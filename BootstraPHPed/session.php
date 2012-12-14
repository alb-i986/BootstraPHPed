<?php
/*
	Questo script raccoglie le chiamate ai comportamenti inibitori comuni.
	Facendo la require_once di questo script, si ereditano gratis tali comportamenti.
*/

require_once("db.php");
require_once("globals.php");
require_once("session.func.php");

@session_start();

requireIncludeBy($INDEX_PATH);

//if(!requireLogin()) return false;

?>
