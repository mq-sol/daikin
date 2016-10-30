<?php
include_once "Config.php";

class postgresql {
    var $host = DBHOST;
    var $user = DBUSER;
    var $pass = DBPASS;
    var $dbname = DBNAME;
    var $dbconn = null;

    var $dblog = null;

    var $dbError;
    var $lastSQL;

    function __construct() {
        $this->connect();
    }

    function __destruct() {
        $this->disconnect();
    }

    function connect() {//接続
        //$this->dblog .= "connect...";
        $this->dbError = null;
        try {
            $host = $this->host;
            $db = $this->dbname;
            $user = $this->user;
            $pass = $this->pass;
            //接続定義呼び出し
            $this->dbconn = pg_connect("host=".$host." dbname=".$db." user=".$user." password=".$pass);
            if (!$this->dbconn) {
                $this->dblog .= "connect error...";
                error_mes("No Connect!");
                exit;
            }

            $bs = pg_connection_busy($this->dbconn);
            if ($bs) {//接続がビジー
                $this->dblog .= "connect busy...";
                error_mes("busy");
            }
            return $this->dbconn;
        }
        catch(string $e) {
            error_mes($e);
        }
    }

    function disconnect() {
        $this->dblog .= "disconnect...";
        pg_close();
        $this->dbconn = null;
    }

    function Query($sql) {
//        try {
            $this->dblog .= "query...$sql";
            //コネクト
            if (!$this->dbconn) {
                $this->dblog .= "reconn...";
                $this->connect();
            }
            $this->lastSQL = $sql;
            $this->dbError = NULL;
            $array = array();
            $result = pg_query($this->dbconn, $sql);
            if ($result) {//ヒット
                $cnt2 = 0;
                for ($cnt = 0; $row = pg_fetch_assoc($result); $cnt++) {
                    //$this->dblog .= "loop...$cnt";
                    while ($str = each($row)) {
                        $encoding = mb_detect_encoding($str['value'], "UTF-8,EUC-JP");
                        if ($encoding != "UTF-8") {//UTF-8以外なら
                            //コード変換
                            $array[$cnt2][$str['key']] = mb_convert_encoding($str['value'], "UTF-8", $encoding);
                        } else {
                            $array[$cnt2][$str['key']] = $str['value'];
                        }
                    }
                    $cnt2++;
                }
            } else {
                $this->error_mes($e);
            }
            //切断
            return $array;

//        }
//        catch(Exception $e) {
//            $this->error_mes($e);
//            return false;
//        }
    }

    function getQuery($sql, $args, $strip = TRUE) {
        if ($strip)
            array_walk_recursive($args, 'sanitize');
        try {
            $orig = $sql;
            $sql = vsprintf($sql, $args);
            $sql = str_replace("”", "\"", $sql);
            return $sql;
        }
        catch(Exception $e) {
            $this->error_mes($e);
            return false;
        }
    }

    function getRecordSet($sql, $args, $strip = TRUE) {
        return $this->Query($this->getQuery($sql, $args, $strip));
    }

    function update($table, $sqlval, $where = "", $arrValIn = array()) {
        $arrCol = array();
        $arrVal = array();
        $find = false;

        if ($where && count($arrValIn)) {
            array_walk_recursive($arrValIn, 'sanitize');

            $where = vsprintf($where, $arrValIn);
        }

        foreach ($sqlval as $key=>$val) {
            if ($val == "NULL") {
                $arrCol[] = $key."=%s";
                $arrVal[] = "NULL";
            } else if (preg_match("/^#(.*)#$/", $val)) {
                $val = str_replace("#", "", $val);
                $arrCol[] = $key."=%s";
                $arrVal[] = $val;
            } else {
                $arrCol[] = $key."='%s'";
                $arrVal[] = $val;
            }
            $find = true;
        }

        if ( empty($arrCol)) {
            return false;
        }

        // 文末の","を削除
        $strcol = implode(', ', $arrCol);
        $where = (strlen($where) <= 0) ? "" : "WHERE $where";
        $sqlup = "UPDATE $table SET $strcol $where";
        // UPDATE文の実行
        $sql = $this->getQuery($sqlup, $arrVal);

        if (!$this->dbconn) {
            $this->connect();
        }
        try {
            pg_query($this->dbconn, "BEGIN WORK");
            $mes = null;
            $result = pg_query($this->dbconn, $sql);
            if ($result) {//アップデート成功
                pg_query($this->dbconn, "COMMIT");
                $mes = true;
            } else {
                pg_query($this->dbconn, "ROLLBACK");
                $mes = false;
            }
            return $mes;
        }
        catch(string $e) {
            pg_query($this->dbconn, "ROLLBACK");
            echo 'ERROR_MES=>'.$e.'<br>';
            $this->error_mes($e);
        }
    }

    function insert($table, $sqlval) {
        $strcol = '';
        $strval = '';
        $find = false;
        if (count($sqlval) <= 0)
            return false;
        foreach ($sqlval as $key=>$val) {
            $strcol .= $key.',';
            if (strcasecmp("Now()", $val) === 0) {
                $strval .= 'Now(),';
            } else if (preg_match("/^#(.*)#$/", $val)) {
                $val = str_replace("#", "", $val);
                $strval .= $val.",";
            } else if (strpos($val, '~') === 0) {
                $strval .= preg_replace("/^~/", "", $val);
            } else {
                $strval .= "'%s',";
                $arrval[] = str_replace("\x0B", "", $val);
            }
            $find = true;
        }
        if (!$find) {
            return false;
        }
        // 文末の","を削除
        $strcol = preg_replace("/,$/", "", $strcol);
        $strval = preg_replace("/,$/", "", $strval);
        $sqlin = "INSERT INTO $table(".$strcol.") VALUES (".$strval.")";
        $sql = $this->getQuery($sqlin, $arrval);

        if (!$this->dbconn) {
            $this->connect();
        }
        try {
            pg_query($this->dbconn, "BEGIN WORK");
            $mes = null;
            $result = pg_query($this->dbconn, $sql);
            if ($result) {//インサート成功
                pg_query($this->dbconn, "COMMIT");
                $mes = true;
            } else {
                pg_query($this->dbconn, "ROLLBACK");
                $mes = false;
            }
            return $mes;
        }
        catch(string $e) {
            pg_query($this->dbconn, "ROLLBACK");
            $this->error_mes($e);
        }
    }

    function error_mes($e) {
        $this->dbError = array("error"=>$e, "dberror"=>pg_last_error());
    }
}
function sanitize(&$val, $key) {
    $val = str_replace("\x0B", "", $val);
    $val = mysql_real_escape_string($val);
    $val = str_replace("”", "\"", $val);
}

$db = new postgresql();
?>
