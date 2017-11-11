<?php
//------------------------------------------------------------------------------
// 程式: db.inc.php
// 用途: 資料庫常用基本設定自定函數
// 作者: 陳瑞明
// 說明: 資料庫存取設定常用自定函數，如主機、使用者、密碼、資料庫路徑設定、
//       選擇要存取的資料庫、存取資料表所有記綠、符合查詢條件的資料內容、
//       將『'』轉換為『\'』、
//------------------------------------------------------------------------------

//------------------------------------------------------------------------------
// class name: SQL
// 功用:快速建立一個SQL查詢的類別
// 說明: 若要使用這個 class, 預設的 mysql 的 link handler 必須取名叫 $link
// 用法:
// 範例:
//------------------------------------------------------------------------------
namespace LFH;
use \PDO;
class SQL {
    var $str;
    var $result;
    var $id;
    var $num_rows;
    var $affected_rows;
    var $link;
    var $row;
    var $connection;

    function __construct($str = "") {
        global $mysql_server, $mysql_dbname, $mysql_account, $mysql_password;
        $this->str = $str;
        $this->connection = new PDO('mysql:host='. $mysql_server. ';dbname='. $mysql_dbname. ';charset=utf8', $mysql_account, $mysql_password); //找好點的寫法
    }

    function fetch_object() {
        return($this->row = $this->result->fetch(PDO::FETCH_OBJ));
    }

    function query() {
        $this->result = $this->connection->prepare($this->str);
        $this->result->execute();
        $this->id = $this->connection->lastInsertId();
        $this->num_rows = $this->result->rowCount();
        $this->affected_rows = $this->result->rowCount();
    }

//------------------------------------------------------------------------------        
// function name: GetField
// 功用:取得資料表中符合查詢條件的記錄的函數
// 說明:取得資料表中符合查詢條件的某一欄位記錄
// 用法:GetField(資料表名稱,欄位名稱,比對欄位名稱,輸入的比較值,連結之主機、使用者、密碼)
// 範例:GetField($table_name, $field_name, $refer_field, $refer_value, $link)
//------------------------------------------------------------------------------

    function GetField($table_name, $field_name, $refer_field, $refer_value, $link = "" ) {
        $this->str = "select $field_name from $table_name where $refer_field='$refer_value' limit 0, 1";
        $this->result = $this->connection->query($this->str);
        $answer = $this->result->fetch(PDO::FETCH_ASSOC);
        return $answer[$field_name];
    }//end GetField

    function reset() {
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }
}
//------------------------------------------------------------------------------

