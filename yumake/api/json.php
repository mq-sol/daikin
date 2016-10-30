<?php
/*
GET 
    lat ... 緯度（デフォルト値は大阪府庁）
    lon ... 経度（デフォルト値は大阪府庁）
    code ... 都道府県コード（デフォルト値は大阪府）
    callback ... なしの場合はJSON、有りはJSONP

RESULT
    出力そのまま  
*/
include_once "Config.php";

//時系列API
$url_time=sprintf("http://api.yumake.jp/1.0/forecastMsm.php?lat=%f&lon=%f&key=%s&format=json",
    empty($_REQUEST["lat"]) ? 34.6863 : $_REQUEST["lat"],
    empty($_REQUEST["lon"]) ? 135.52  : $_REQUEST["lon"],
    $api_key);
$json_time = file_get_contents($url_time);
$timeline = json_decode($json_time,true);
//今日明日API
$url_forecast=sprintf("http://api.yumake.jp/1.1/forecastPref.php?code=%s&key=%s&format=json",
    empty($_REQUEST["code"]) ? 27 : $_REQUEST["code"],
    $api_key);
$json_forecast = file_get_contents($url_forecast);
$forecast = json_decode($json_forecast,true);

$json = json_encode(compact('timeline','forecast'));
//データ取得

if (empty($_REQUEST["callback"])){
    echo $json;
}else{
    printf("%s(%s)", $_REQUEST["callback"], $json);
}

//$result = json_decode($json, true);
//print_r($result);
?>
