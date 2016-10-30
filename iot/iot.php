<?php
include_once "postgresql.php";

$json_string = file_get_contents('php://input');
$obj = json_decode($json_string, true);
error_log(print_r($obj,true),3,"/var/tmp/daikin.log");
if (empty($obj)){
    echo json_encode(array("success" => false,"msg"=>"nodata", "created" => date('Y-m-d H:i:s')));
    exit;
}
error_log(print_r($obj["iot_id"],true),3,"/var/tmp/daikin.log");
$id = empty($obj["iot_id"]) ? 0 : $obj["iot_id"];
error_log(print_r($id,true),3,"/var/tmp/daikin.log");
$value = empty($obj["value"]) ? array() : $obj["value"];

//DBに登録
$sql = sprintf("insert into logs(iot_id, value) values (%d, '%s')",
    $id, json_encode($value));

$ret = $db->Query($sql);

$success = is_null($db->dbError);
$created = date('Y-m-d H:i:s');
$msg = $db->dbError;
echo json_encode(compact("success", "created", "msg"));

?>
