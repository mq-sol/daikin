<?php
define("__ROOT__",dirname(dirname(__FILE__)));
require_once __ROOT__ . "/src/Clients/NAWSApiClient.php";
require_once "Config.php";
//Netatmoの接続情報
$scope = "read_station";
$config = compact("client_id", "client_secret", "username", "password");
$callback = empty($_REQUEST["callback"]) ? "" : $_REQUEST["callback"];
//WSに接続して情報を取得しメッセージ文を作る
$client = new NAWSApiClient($config);
$data = $client->getData();
//print_r($data);
//exit;
$message = "";
$inner = array();
$outer = array();
foreach ($data["devices"] as $device) {
    //場所・気温・湿度・C02
     $message .= sprintf(
        "%s : %s°C, %s%%, %sppm" . PHP_EOL
        , $device["station_name"]
        , $device["dashboard_data"]["Temperature"]
        , $device["dashboard_data"]["Humidity"]
        , $device["dashboard_data"]["CO2"]
    );

    $name = $device["station_name"];
    $temperature = $device["dashboard_data"]["Temperature"];
    $humidity = $device["dashboard_data"]["Humidity"];
    $co2 = $device["dashboard_data"]["CO2"];
    $pressure = $device["dashboard_data"]["Pressure"];
    $current = date('Y-m-d H:i:s', $device["dashboard_data"]["time_utc"]);
    $inner[] = compact('current', 'name', 'temperature', 'humidity','co2','pressure');


    //屋外モジュールなど
    foreach ($device["modules"] as $module) {
        //モジュール名・気温・湿度
        $message .= sprintf(
            "%s : %s°C, %s%% " . PHP_EOL
            , $module["module_name"]
            , $module["dashboard_data"]["Temperature"]
            , $module["dashboard_data"]["Humidity"]
        );
        $current = date('Y-m-d H:i:s', $device["dashboard_data"]["time_utc"]);
        $name = $module["module_name"];
        $temperature = $module["dashboard_data"]["Temperature"];
        $humidity = $module["dashboard_data"]["Humidity"];
        $outer[] = compact('current', 'name','temperature','humidity');
    }
}
$json = json_encode(compact('inner','outer'));
if ($callback){
    printf("%s(%s)",$callback, $json);
}else{
    print($json);
}
?>
