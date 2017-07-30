<?php
require_once 'fns.php';
require_once 'vendor/autoload.php';



class Xmly
{
    const APP_KEY = 'b617866c20482d133d5de66fceb37da3';
    const APP_SECRET = 'b617866c20482d133d5de66fceb37da3';
    
    
}


$app_key = 'b617866c20482d133d5de66fceb37da3';
$app_secret = '4d8e605fa7ed546c4bcb33dee1381179';
$ServerAuthStaticKey = 'de5kio2f';
$client_os_type = '4';
$microtime = microtime();
$microtime = explode(' ', $microtime);
$timestamp = $microtime[1] . substr($microtime[0], 2, 3);
$nonce = $timestamp . mt_rand(1000, 9999);

$queryArr = [
    'app_key' => $app_key,
    'client_os_type' => $client_os_type,
    'nonce' => $nonce,
    'timestamp' => $timestamp,
    //'category_id' => 0,
    //'sort' => 'asc',
    //'album_id' => 4194376,
    //'page' => 1,
    //'count' => 20,
    'rank_type' => 1
];

ksort($queryArr);
$sigArr = [];
foreach ($queryArr as $key => $value) {
    $sigArr[] = $key . '=' . $value;
}
$sigStrTmp = implode('&', $sigArr);
$sigStrTmp = base64_encode($sigStrTmp);
$signKey = $app_secret . $ServerAuthStaticKey;
$sigStrTmpBin = hash_hmac('sha1', $sigStrTmp, $signKey, true);
$sig = md5($sigStrTmpBin);
$queryArr['sig'] = $sig;
$requestUrl = 'http://api.ximalaya.com/openapi-gateway-app/categories/list?';
//$requestUrl = 'http://api.ximalaya.com/openapi-gateway-app/albums/get_all?';
//$requestUrl = 'http://api.ximalaya.com/openapi-gateway-app/albums/browse?';
//$requestUrl = 'http://api.ximalaya.com/openapi-gateway-app/open_pay/all_paid_albums?';
$requestUrl = 'http://api.ximalaya.com/openapi-gateway-app/ranks/index_list?';
// $curl = new \wii\curl\Curl();


$curl = new HTTP_Request2();

$getStr = http_build_query($queryArr);
$requestUrl .= $getStr;
// $_defaultOptions = [
//     CURLOPT_RETURNTRANSFER => true
// ];
// $curl = curl_init($requestUrl);
// curl_setopt_array($curl, $_defaultOptions);
// $response = curl_exec($curl);
// curl_close($curl);
// echo $response;
// exit();


$request = new HTTP_Request2($requestUrl, HTTP_Request2::METHOD_GET);
try {
    $response = $request->send();
    if (200 == $response->getStatus()) {
        $content = $response->getBody();
        ee(json_decode($content));
    } else {
        echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
            $response->getReasonPhrase();
    }
} catch (HTTP_Request2_Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

$request = new HttpRequest();
