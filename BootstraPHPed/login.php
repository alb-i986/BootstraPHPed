<?php

require_once("adodb5/adodb.inc.php");
require_once("globals.php");
require_once("db.php");
require_once("session.func.php");


if (!isLoggedIn()) {
	if(!empty($_POST['email']) && !empty($_POST['password'])) {
		$email_ok = true;
		//preg_match("/^[A-Za-z0-9_\-\.]+@([A-Za-z0-9\-]+\.)+[A-Za-z0-9]{2,3}$/", $_POST['email']) && strlen($_POST['email']) < 255;
		if($email_ok) {
			$email = mysql_real_escape_string($_POST['email']);
			$password = mysql_real_escape_string($_POST['password']);
			
			$row = auth_db($email, $password);
			if(!empty($row)) {
				session_regenerate_id();
				$_SESSION['user_id'] = $row['id'];
				$_SESSION['user'] = $row['nickname'];
				$_SESSION['user_role'] = $row['role'];
				$_SESSION['user_team'] = $row['team'];
			}
		}
	}
}
// redirects to where you were before
header("Location: ".$_SERVER['HTTP_REFERER']);




function auth_db($username, $password) {
	$db = db_conn();	
	$sql = "SELECT id,nickname,role,team FROM users WHERE email=? AND password = MD5(?)";
	$stmt = $db->Prepare($sql); // against SQL injection
	$row = $db->GetRow($stmt, array($username, $password));
	if (empty($row)) {
		return false;
	} else  {
		return $row;
	}
}
