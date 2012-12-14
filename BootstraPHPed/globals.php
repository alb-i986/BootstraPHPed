<?php
/*
// parametri file, path
$ROOT = "D:\www";
$BASE = "/BootstraPHPed/";
$HOME = "http://" . $_SERVER['HTTP_HOST'] . $BASE;
$INDEX_PATH = $BASE . "index.php"; // da cofrontare con $_SERVER['PHP_SELF']
$MODELS_PATH = $BASE . "models/";
$SCRIPTS_PATH = $ROOT . "scripts/";
$LOGS_PATH = $ROOT . "logs/";


// parametri DB
$HOSTNAME = "localhost";
$DB_TYPE = "mysql";
$DB_NAME = "bootstraphped";
$DB_USERNAME = "root";
$DB_PASSWORD = "passw0rd";


// vari & eventuali
*/	




// parametri file, path
define("ROOT", "D:\www");
define("BASE", "/BootstraPHPed/");
define("HOME", "http://" . $_SERVER['HTTP_HOST'] . BASE);
define("INDEX_PATH", BASE . "index.php"); // da cofrontare con $_SERVER['PHP_SELF']
define("MODELS_PATH", BASE . "models/");
define("SCRIPTS_PATH", ROOT . "scripts/");
define("LOGS_PATH", ROOT . "logs/");


// parametri DB
define("HOSTNAME", "localhost");
define("DB_TYPE", "mysql");
define("DB_NAME", "bootstraphped");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "passw0rd");


// vari & eventuali
	

?>
