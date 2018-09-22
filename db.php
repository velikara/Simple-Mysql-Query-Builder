<?php
namespace vendor;

use \PDO;

class db extends PDO{

    private static $dbname ="";
    private static $user ="";
    private static $password ="";
    private static $host="";

    private static  $query ="";
    private static  $selectType = false;
    public static   $table="";
    public static   $tablePrefix="";
    public static   $joinPrefix="";
    public static   $val=array();

    private static  $_istance = null;

    public function __construct() {
        $dbsetting=array();
        include "config.php";
        self::$dbname =$dbsetting["db"];
        self::$user =$dbsetting["user"];
        self::$password =$dbsetting["pass"];
        self::$host=$dbsetting["host"];

        parent::__construct("mysql:dbname=".self::$dbname.";host=".self::$host, self::$user, self::$password);
//        $this->query("SET SESSION time_zone = '+01:00'");
        $this->query("SET NAMES UTF8");
        $this->query("SET CHARACTER SET utf8");
        $this->query("SET COLLATION_CONNECTION = 'utf8_general_ci'");
    }

    public static final function pdo() {
        if (is_null(self::$_istance)) {
            self::$_istance = new db;
        }
        return self::$_istance;
    }
    public static function table($table) {
        $table = trim($table);
        self::$table = $table;
        self::$val = array();
        return self::pdo();
    }

    public static function whereparam($array=array()){
        $query=null;
        $curentArr= $array;
        $strbindArr = array();
        foreach ($array as $key => $value) {
            $strbind=str_replace(".","_",$key);
            $query .=$key."=:$strbind AND ";
            $strbindArr[$strbind]=$value;
        }
        $query = rtrim($query, " AND");
        self::$query .= $query;
        self::$val=array_merge(self::$val, $strbindArr);
        return self::pdo();
    }
    public static function onparam($array=array()){
        $query=null;
        $curentArr= $array;
        $strbindArr = array();
        foreach ($array as $key => $value) {
            $strbind=str_replace(".","_",$key);
            $query .=$key."=:$strbind AND ";
            $strbindArr[$strbind]=$value;
        }
        $query = rtrim($query, " AND");
        self::$query .= $query;
        self::$val=array_merge(self::$val, $strbindArr);
        return self::pdo();
    }
    public static function where($strORarray,$bind=array()){
        self::$query .=" WHERE@ ";
        if(is_array($strORarray)) {
            return self::whereparam($strORarray);
        } else {
            self::$query .=$strORarray;
            return self::bind($bind);
        }
        return self::pdo();
    }
    public static function lang(){
        if (!strstr(self::$query,"WHERE@")) {
            self::$query.=" WHERE@ ";
        } else {
            self::$query.= " AND ";
        }
        return self::whereparam(array("lang"=>session::get("lang")));
    }
    public static function select($values=""){
        if($values=="")
            self::$query="SELECT * FROM ".self::$table;
        else
            self::$query="SELECT ".$values." FROM ".self::$table;
        return self::pdo();
    }
    public static function selectOne($values=""){
        if($values=="")
            self::$query="SELECT * FROM ".self::$table;
        else
            self::$query="SELECT ".$values." FROM ".self::$table;

        self::$selectType = true;
        return self::pdo();
    }
    // INSERT INTO table_name (column1,column2,column3,...) VALUES (value1,value2,value3,...);
    public static function insert($array=array()){
        foreach ($array as $key => $value) {
            if($value=="" || $value==null) {
                unset($array[$key]);
            }
        }
        $fieldKeys = implode(",", array_keys($array));
        $fieldValues = ":" . implode(", :", array_keys($array));
        self::$query = "INSERT INTO ".self::$table."($fieldKeys) VALUES($fieldValues)";
        self::$val = $array;
        return self::pdo();
    }
    public static function update($array){
        $updateKeys = null;
        foreach ($array as $key => $value) {
            if(is_int($key)) {
                $updateKeys .=$value.",";
                unset($array[$key]);
            } else {
                $updateKeys .= "$key=:$key,";
            }
        }
        $updateKeys = rtrim($updateKeys, ",");
        self::$query = "UPDATE ".self::$table." SET $updateKeys";
        self::$val=$array;
        return self::pdo();
    }
    public static function replace($array){
        $updateKeys = null;
        foreach ($array as $key => $value) {
            $updateKeys .= "$key=$value,";
        }
        $updateKeys = rtrim($updateKeys, ",");
        self::$query = "UPDATE ".self::$table." SET $updateKeys ";
        return self::pdo();
    }
    public function delete(){
        self::$query ="DELETE FROM ".self::$table;
        return self::pdo();
    }
    public static function bind($array){
        if(!empty($array)) {
            self::$val = $array;
        }
            return self::pdo();

    }
    public static function left_join($string=""){
        self::$query .=" LEFT JOIN ".$string;
        return self::pdo();
    }
    public static function right_join($string=""){
        self::$query .=" RIGHT JOIN ".$string;
        return self::pdo();
    }
    public static function left_outer_join($string=""){
        self::$query .=" LEFT OUTER JOIN ".$string;
        return self::pdo();
    }
    public static function left_inner_join($string=""){
        self::$query .=" LEFT INNER JOIN ".$string;
        return self::pdo();
    }
    public static function right_outer_join($string=""){
        self::$query .=" RIGHT OUTER JOIN ".$string;
        return self::pdo();
    }
    public static function right_inner_join($string=""){
        self::$query .=" RIGHT INNER JOIN ".$string;
        return self::pdo();
    }
    public static function join($string=""){
        if (strstr($string,"JOIN")) {
            self::$query .=" ".$string;
        } else {
            self::$query .=" LEFT OUTER JOIN ".$string;
        }
        return self::pdo();
    }
    public static function on($strORarray,$bind=array()){
        self::$query .=" ON ";
        if(is_array($strORarray)) {
            return self::onparam($strORarray);
        } else {
            self::$query .=$strORarray;
            return self::bind($bind);
        }
    }
    //GROUP BY ülke
    public static function groupby($cols=""){
        self::$query .=" GROUP BY ".$cols;
        return self::pdo();
    }
    public static function orderby($cols="",$type="ASC"){
        if(strstr(strtoupper($type),"ASC") || strstr(strtoupper($type),"DESC"))
            self::$query .=" ORDER BY ".$cols." ".$type;
        else
            self::$query .=" ORDER BY ".$cols." ASC";
        return self::pdo();
    }
    public static function limit($limit="",$ofset=""){
        if(is_numeric($ofset))
            self::$query .=" LIMIT ".$limit.",".$ofset;
        else
            self::$query .=" LIMIT ".$limit;
        return self::pdo();
    }

    public function end($type=""){
        self::$query = str_replace("WHERE@","WHERE",self::$query);
        $sth = $this->prepare(self::$query);
        if(!empty(self::$val)) {
            foreach (self::$val as $key => $value) {
                $sth->bindValue($key, $value);
            }
        }
            try{
            $result =$sth->execute();
            }catch(PDOException $e){
                die($e->getMessage());
            }

        if (strstr(self::$query,"SELECT")) {
            $result = $sth->fetchall(PDO::FETCH_ASSOC);
            if (self::$selectType) $result = $result[0];
            self::$selectType = false;
        }
        if (strtolower($type) == "json") {
            return json_encode($result);
        } else if (strtolower($type) == "std") {
            return (object)$result;
        } else if (strtolower($type) == "debug") {
            echo "SQL : " . self::$query;
            echo "<pre>PARAMS:<br>";
            print_r(self::$val);
            echo "</pre>";
            echo "<pre>RESULT:<br>";
            print_r($result);
            echo "</pre>";
            die;
        } else {
            self::$val=array();
            return $result;
        }

    }
}