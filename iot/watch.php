<?php
include_once "postgresql.php";

$iot_id = empty($_REQUEST["iot_id"]) ? 0 : $_REQUEST["iot_id"];

$sql = "select l.id, i.name, i.username, i.userpass, i.url, i.method, l.value from
    iots as i inner join logs as l on (i.id = l.iot_id)
    where iot_id = $iot_id and done_flg = 0";

$rs = $db->query($sql);
print json_encode($rs);
?>
