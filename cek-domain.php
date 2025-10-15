<?php
date_default_timezone_set('Asia/Jakarta');

// Ambil dari secrets
$TOKEN = getenv('TELEGRAM_TOKEN');
$MAP_JSON = getenv('DOMAIN_CHAT_MAP');
$DOMAIN_CHAT_MAP = json_decode($MAP_JSON, true);

$endPointCurlPing = '/api-cek-server.php';

// Ambil daftar domain dari key JSON
$arr_domain = array_keys($DOMAIN_CHAT_MAP);

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
        CURLOPT_HTTPHEADER => [
            'Accept: application/json, text/plain, */*',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (compatible; DomainMonitorBot/1.0; +https://github.com/celunk/monitor-domain)',
        ],
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

function kirimPesanTelegram($token, $chatid, $pesan)
{
    if (!$chatid) return;
    $url = "https://api.telegram.org/bot{$token}/sendMessage?chat_id={$chatid}&text=" . urlencode($pesan);
    @file_get_contents($url);
}

$log = "=== Log Cek Domain (" . date('Y-m-d H:i:s') . ") ===\n";

foreach ($arr_domain as $domain) {
    $chatid = $DOMAIN_CHAT_MAP[$domain] ?? null;
    $cek = curlPingWebsite($domain . $endPointCurlPing);

    if ($cek === false) {
        $pesan = "$domain Down (Tidak Dapat Diakses)\n\nCek dari GitHub Actions";
        kirimPesanTelegram($TOKEN, $chatid, $pesan);
        $log .= "[DOWN] $domain (chat $chatid)\n";
    } else {
        $log .= "[UP]   $domain (chat $chatid)\n";
    }
}

file_put_contents(__DIR__ . '/log.txt', $log . "\n", FILE_APPEND);
echo $log;
?>