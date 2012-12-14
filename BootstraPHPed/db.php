<?php 

require_once("adodb5/adodb.inc.php");
require_once("globals.php");
require_once('log4php/Logger.php');



//Logger::configure('logconfig.xml');
$logger = Logger::getLogger("main");

//$logger = Logger::getLogger("myLogger");


function db_conn() {
	//global $HOSTNAME, $DB_TYPE, $DB_NAME, $DB_USERNAME, $DB_PASSWORD;
	$db_conn = ADONewConnection(DB_TYPE);
	$db_conn->PConnect(HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);
	return $db_conn;
}

require("db.custom.php");


/*********************************************
*               BASIC functions              *
*********************************************/



/********* USER-related functions ***********/



function db_getUser($username) {
	if( empty($username) )
		return false;

	global $logger;
	$db = db_conn();
	$sql = "SELECT * FROM users WHERE id='$username' OR email='$username'";
	$user_row = $db->GetRow($sql);
	if($user_row === FALSE) $logger->error("db_getUser > ADOdb GetRow failed");
	return $user_row;
}

function db_newUser($username, $password) {
	if( empty($username) ) {
		return false;
	}

	global $logger;
	$db = db_conn();
	$sql = "INSERT INTO users (email, password, nickname) VALUES ( '$username', '$password' )";
	$results = $db->Execute($sql);
	if ( !$results || $db->Affected_Rows()!= 1 ) {
		$logger->error($db->ErrorMsg());
		return false;
	}
	return true;
}



/********* ROLE-related functions ***********/

function db_getRole($role) {
	if(empty($role))
		return false;
		
	global $logger;
	$db = db_conn();
	$sql = "SELECT * FROM roles WHERE id='".$role."' OR name='".$role."'";
	$row = $db->GetRow($sql);
	if($row === FALSE) $logger->error("db_getRole > ADOdb GetRow failed. Param role: ".implode(", ",$role) );
	return $row;
}

function db_getRoles() {
	global $logger;
	$db = db_conn();
	$sql = "SELECT id,name FROM roles";
	$stmt = $db->Prepare($sql);
	$rows = $db->GetAll($stmt);
	if($rows === FALSE) $logger->error("db_getRoles > ADOdb GetAll failed.");
	return $rows;
}

/********* SECTION-related functions ***********/


/*
	@param $section: may be an ID or a name of a (super|sub) section
	@return full row of the section IF the section exists
			an empty array if the section does not exist
			false if an error occurs
*/
function db_getSection($section) {
	if(empty($section))
		return false;
	
	global $logger;
	$db = db_conn();
	$sql = "SELECT * FROM sections WHERE id='".$section."' OR name='".$section."'";
	$row = $db->GetRow($sql);
	if($row === FALSE) $logger->error("db_getSection > ADOdb GetRow failed. Param section: ".implode(", ",$section) );
	return $row;
}

/*
	@param $section can be an ID or a name of a subsection
*/
function db_getSubsections($section) {
	$section_row = db_getSection($section);
	if(empty($section_row))
		return false;
	
	global $logger;
	$db = db_conn();
	$sql = "SELECT * FROM sections WHERE super=".$section_row['id']." ORDER BY sort ASC";
	$rows = $db->GetAll($sql);
	if($rows === FALSE) $logger->error("db_getSubsections > ADOdb GetAll failed");
	return $rows;
}

/*
	@param $section can be either an ID or a name
*/
function db_sectionHasSubs($section){
	$section_row = db_getSection($section);
	if(empty($section_row))
		return false;

	global $logger;
	$db = db_conn();
	$sql = "SELECT id FROM sections WHERE super=".$section_row['id'];
	$subsection = $db->GetRow($sql);
	if( $subsection === FALSE ) $logger->error("db_sectionHasSubs > ADOdb GetRow failed");
	return !empty($subsection);
}

/*
	@param $section can be either an ID or a name of a (super|sub) section
	@return true if $section is not a subsection (it may have or not a subsection)
*/
function db_sectionIsSuper($section){
	$section_row = db_getSection($section);
	if(empty($section_row))
		return false;

	global $logger;
	$db = db_conn();
	$sql = "SELECT id FROM sections WHERE super IS NULL AND id=".$section_row['id'];
	$super = $db->GetRow($sql);
	if($super === FALSE) $logger->error("db_sectionIsSuper > ADOdb GetRow failed");
	return !empty($super);
}


function db_getDefaultSubsection($supersection) {
	$super_row = db_getSection($supersection);
	if(empty($super_row))
		return false;
		
	if( !db_sectionHasSubs($supersection) ) {
		return false;
	}
	
	$db = db_conn();
	$sql = "SELECT MIN(sort) FROM sections WHERE super=".$super_row['id'];
	$sort = $db->GetOne($sql);
	if( empty($sort) ) return false;
	$sql = "SELECT id FROM sections WHERE super=".$super_row['id']." AND sort=".$sort;
	return db_getSection($db->GetOne($sql));
}

/*
	@return full row of the default super section, i.e. the super section with the least 'sort' value
		If there are more than one supersections with the least 'sort' value, then it returns the first row it encounters.
*/
function db_getDefaultSection() {
	$db = db_conn();
	$sql = "SELECT MIN(sort) FROM sections WHERE super IS NULL";
	$sort = $db->GetOne($sql);
	if( empty($sort) ) return false;
	$sql = "SELECT id FROM sections WHERE super IS NULL AND sort=".$sort;
	return db_getSection($db->GetOne($sql));
}


/*
	@return array of rows of all the super-sections, sorted by 'sort' column
*/
function db_getSupersections() {
	global $logger;
	$db = db_conn();
	$sql = "SELECT * FROM sections WHERE super IS NULL ORDER BY sort ASC";
	$rows = $db->GetAll($sql);
	if($rows === FALSE) $logger->error("db_getSupersections > ADOdb GetAll failed");
	return $rows;
}


/*  
!!NO PHP CLOSING TAG!!
http://www.php.net/manual/en/language.basic-syntax.phptags.php
If a file is pure PHP code, it is preferable to omit the PHP closing tag at the end of the file. This prevents accidental whitespace or new lines being added after the PHP closing tag, which may cause unwanted effects because PHP will start output buffering when there is no intention from the programmer to send any output at that point in the script.
*/