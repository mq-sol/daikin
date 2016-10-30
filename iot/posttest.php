<?php
header("Content-Type: text/plain");
//$url = "http://api.daikin.ishikari-dc.net/equipments/70/";
$url = "https://api-06.daikin.ishikari-dc.net/equipments/70/";
$user = "daikin";
$pass = "pichonkun";
$body = array(
    "id" => 70,
    "status" => array(
        "id" => 70,
        "power" => 1,
        "operation_mode" => 4,
        "set_temperature" => 28,
        "fan_speed" => 1,
        "fan_direction" => 7
    )
);
$json = json_encode($body);
$ch = curl_init();
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_USERPWD, $user . ":" . $pass);
curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
$result=curl_exec($ch);
print_r($result);
curl_close($ch);
