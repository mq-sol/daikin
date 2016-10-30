<?php
include_once "postgresql.php";

$id = empty($_REQUEST["id"]) ? 0 : $_REQUEST["id"];

$sql = "update logs set done_flg = 1 where id = $id";
$rs = $db->query($sql);

echo "done: $id";
?>
