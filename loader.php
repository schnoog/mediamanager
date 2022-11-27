<?php  

$data = array();
include_once("vendor/autoload.php");
include_once("functions.php");
include_once("config.php");
include_once("secrets.php");


DB::$user = $data["user"];
DB::$password = $data["pw"];
DB::$dbName = $data["db"];
DB::$host = $data["host"];
DB::$port = $data["port"];