<?php
/**
 *
 * Authors: al.scotto
 */

require_once("../session.php");
require_once("../db.php");




if($_SERVER["REQUEST_METHOD"] != "POST" ) {
	echo "Invalid REQUEST_METHOD";
	exit;
}
			
?>
