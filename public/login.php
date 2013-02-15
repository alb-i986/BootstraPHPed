<?php
/**
 * 
 * @author Alberto 'alb-i986' Scotto
 */

require_once './globals.inc.php';


if ( ! isLoggedIn() ) {
	if ( !empty($_POST['email'] ) && !empty($_POST['password']) ) {
		$email_ok = User::validateStatic( 'email', trim( $_POST['email'] ) );
		$password_ok = User::validateStatic( 'password', $_POST['password'] );

		if ( $email_ok && $password_ok ) {
			$email = mysql_real_escape_string( $_POST['email'] );
			$password = mysql_real_escape_string( $_POST['password'] );
			
			$id = $dao->authUser($email, $password);
			if( $id ) {
				session_regenerate_id();
				$u = new User();
				$u->load( $id );
				$u->setAuthenticated();
				sess_setUser($u);
			}
		}
	}
}
// redirects to where you were before
header('Location: ' . $_SERVER['HTTP_REFERER']);
