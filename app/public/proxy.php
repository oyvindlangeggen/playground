<?php

$requestType = $_SERVER['REQUEST_METHOD'];
$endpoint =  $_GET['method'];
$salonId = $_GET['salonId'];
$url = "https://booking.raise.no/api/v2/";
$payload = trim(file_get_contents("php://input"));
$isPost = $requestType == "POST";
// $isGet = $requestType == "GET";
$querystring = stripQueryParams($_SERVER['QUERY_STRING']);

$token = findAPIKey($salonId);
$response = forward($url, $endpoint, $payload, $token, $isPost, $querystring);
echo $response; 

function stripQueryParams($querystring) {
    if ($querystring = "") {
        return;
    }
    parse_str($querystring, $ar);
    unset($ar["method"]);
    unset($ar["salonId"]);
    return http_build_query($ar);
}

function findAPIKey($salonId) {
    $json = json_decode(file_get_contents('../salons.json'));
    $token = "";
    
    foreach($json->Salons as $item) {
        if($item->Id == $salonId) {
            return $item->APIKey;
        }
    }
}

function forward($url, $endpoint, $payload, $token, $isPost, $params) {
    $authorization = "Authorization: Bearer " . $token;
    $redirect_url = $url . $endpoint;
    
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Content-Type: application/json' , $authorization )
    );

    if ($isPost) {
        $options[CURLOPT_POST] = true;
    }

    if ($payload != "") {
        $options[CURLOPT_POSTFIELDS] = $payload;
        $options[CURLOPT_POST] = true;
    }

    $redirect_url = $redirect_url . "?" . $params;

    $ch = curl_init($redirect_url);
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);

    if (!isset($response)) {
        return null;
    }
    return $response;
}

?>