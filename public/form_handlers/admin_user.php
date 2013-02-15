<?php
/**
 * Sort of a servlet that handles POST requests submitted in a form by a View.
 * Goal: CRUD users
 *
 * _Output_
 * Whether it is data or error messages, its output is plain (XHTML) text.
 * It is planned to implement outputting in JSON format.
 *
 * In case of failure due to the input data from the client (e.g.: invalid input),
 * it returns an unordered list (tag ul) with error messages that explain the errors to the client.
 * In case of failure due to internal causes (e.g.: no connection to the DB), 
 * it returns the errors in plain test.
 *
 * @author Alberto 'alb-i986' Scotto
 */

require_once '../globals.inc.php';


if($_SERVER['REQUEST_METHOD'] != 'POST' ) {
	echo 'Invalid request method';
	exit;
}

$user = sess_getUser();
if( ! $user->hasRole(User::ROLE_ADMIN) ) {
	echo 'Your privileges are not sufficient to perform the requested action';
	exit;
}

$err_msgs = array();

// check user input
if ( empty($_GET['action']) ) {
	$err_msgs[] = 'Empty field: action';
} else {
	$u = new User();
	$action = $_GET['action'];
	// action must be 'edit' OR 'delete' OR 'add'
	if( ! strcmp($action, 'edit') ) {
		$user_row = array(
			'id' => $_POST['user_id'],
			'email' => $_POST['email'],
			'team' => $_POST['team'],
			'nickname' => $_POST['nickname'],
			'role' => $_POST['role'],
		);
		$fields_with_errs = $u->set( $user_row );
		$u->save();
		$err_msgs = array_merge($err_msgs, $fields_with_errs);
	} else if( ! strcmp($action, 'delete') ) {
		$u->load( $_POST['user_id'] );
		try {
			$u->delete();
		} catch(Exception $e) {
			$err_msgs = array_merge( $err_msgs, $e->getMessage() );
		}		
	} else if( ! strcmp($action, 'add') ) {
		$user_row = array(
			'email' => $_POST['email'],
			'password' => $_POST['password'],
			'team' => $_POST['team'],
			'nickname' => $_POST['nickname'],
			'role' => $_POST['role'],
		);
		$fields_with_errs = $u->set( $user_row );
		$u->save();
		$err_msgs = array_merge($err_msgs, $fields_with_errs);
	} else {
		$err_msgs[] = 'Unknown action: ' . $action;		
	}
}

// user input is KO => output an HTML unordered list with the error messages
if( !empty($err_msgs) ) {
	array_walk($err_msgs, function(&$el, $key){ $el = '<li>' . $el . '</li>'; });
	echo "<ul>\n";
	echo implode('', $err_msgs);
	echo "</ul>\n";
} else {
	echo 'OK';
}