<?php  

$data = array();
include_once("vendor/autoload.php");
include_once("functions.php");
include_once("photo_inc.php");

include_once("indexer_inc.php");
include_once("sync_inc.php");
include_once("config.php");
include_once("secrets.php");
include_once("retire_me.php");


DB::$user = $data["user"];
DB::$password = $data["pw"];
DB::$dbName = $data["db"];
DB::$host = $data["host"];
DB::$port = $data["port"];
DB::$encoding = 'utf8mb4';
