<?php
date_default_timezone_set('Asia/Jakarta');

$TOKEN = getenv('TELEGRAM_TOKEN');
$endPointCurlPing = '/api-cek-server.php';

$arr_domain = [
    ["https://kurir.baraya-paket.com", "-826370958"],
    ["https://baraya-paket.com", "-826370958"],
    ["https://hrd.id", "-716749503"],
    ["https://logistic.stsa.co.id", "-972091450"],
    ["https://sorascreen.id", "-716749503"],
];

function curlPingWebsite($host)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $host,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 60,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err || !$response) {
        return false;
    }

    $hasil = json_decode($response, true);
    return isset($hasil['data']);
}

$log = "=== Log Cek Domain (" . date('Y-m-d H:i:s') . ") ===\n";

foreach ($arr_domain as $row) {
    $domain = $row[0];
    $chatid = $row[1];

    $cek = curlPingWebsite($domain . $endPointCurlPing);

    if ($cek == false) {
        $pesan = $domain . " Down (Tidak Dapat Diakses)\n\nCek dari https://kecoakganteng.my.id";
        $url = "https://api.telegram.org/bot" . $TOKEN . "/sendMessage?chat_id=" . $chatid . "&text=" . urlencode($pesan);
        file_get_contents($url);
        $log .= "[DOWN] $domain\n";
    } else {
        $log .= "[UP]   $domain\n";
    }
}

file_put_contents(__DIR__ . '/log.txt', $log . "\n", FILE_APPEND);
echo $log;
?>