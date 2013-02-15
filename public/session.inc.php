<?php
/**
 * 
 * @author Alberto 'alb-i986' Scotto
 */

// This file must contain only functions. No code outside of functions shall be executed.

#use BootstraPHPed\classes\Persistence;



@session_start(); // gli script che vogliono usare le funzioni di sessione, devono avere la sessione inizializzata!

/**
 * Initializes the $_SESSION variable with a new instance of User, ONLY IF it wasn't already initialized.
 *
 * @return the (unserialized) instance of the current user who is using the system;
 *         false if $_SESSION['BootstraPHPed']['user_obj'] is not empty.
 */
function sess_initUser() {
	$u = null;
	if( !isset( $_SESSION['BootstraPHPed'] ) && !isset( $_SESSION['BootstraPHPed']['user_obj'] ) )
		sess_setUser( $u = new User() );
	return ( empty($u) ? false : $u );
}

/**
 * Side effect: initializes the user instance in $_SESSION if it wasn't initialized.
 * @return the instance of the current user who is using the system
 * @throws Exception if the User instance saved in $_SESSION is not serializable
 */
function sess_getUser() {
	if( !isset( $_SESSION['BootstraPHPed'] ) && !isset( $_SESSION['BootstraPHPed']['user_obj'] ) ) {
		$u = sess_initUser();
	}
	else {
		$u = unserialize( $_SESSION['BootstraPHPed']['user_obj'] );
		if ( $u === false )
			throw new Exception('Unserialization of user instance in $_SESSION failed');
	}
	return $u;
}

/**
 * Serializes the user passed as argument, and saves it in $_SESSION.
 * @return serialized $user
 */
function sess_setUser(User $user) {
	if( empty($user) )
		throw new InvalidArgumentException('Mandatory argument missing: user');

	return $_SESSION['BootstraPHPed']['user_obj'] = serialize( $user );
}

/**
 * Side effect: initializes the user instance in $_SESSION if it wasn't initialized.
 * @return true if the user accessing the system is authenticated
 */
function isLoggedIn() {
	try {
		$u = sess_getUser();
	} catch(Exception $ex) {
		return false;
	}
	return $u->isAuthenticated();
}

function requireLogin() {
	return requireRole('user');
}

/*
	@param $section: may be an ID or a name of a (super|sub)section
*/
function requireAccessTo( $section ) {
	if( empty($section) )
		throw new InvalidArgumentException('Mandatory argument is empty: $section.');
	global $dao;
	$section_row = $dao->getSection($section);
	if( empty($section_row) )
		throw new InvalidArgumentException('Invalid argument: the specified section does not exist in the DB.');
	
	$u = sess_getUser();
	if( ! $u->hasAccessTo( $section_row['id'] ) ) {
		$prop = $u->get( array('role') );
		showErr( $prop['role'] == User::ROLE_GUEST ? 401 : 403 );
		return false;
	}
	return true;
}




/*
	Precond: $role > 0
	Se l'utente loggato non ha il ruolo desiderato, stampa un div con un msg di errore.
	@param role può essere sia in formato numerico (colonna id della tabella roles) che in formato testuale (colonna name)
	@return true SSE l'utente loggato ha il ruolo desiderato o uno superiore
*/
function requireRole( $role ) {
	if( empty($role) )
		throw new InvalidArgumentException('Mandatory argument is empty: $role');
	global $dao;
	$role_row = $dao->getRole($role);
	if( empty($role_row) )
		throw new InvalidArgumentException('Invalid argument: the specified role does not exist in the DB.');
		
	$role_id = $role_row['id'];
	$u = sess_getUser();
	if( ! $u->hasRole( $role_id ) ) {
		$prop = $u->get( array('role') );
		showErr( ( $prop['role'] == User::ROLE_GUEST ? 401 : 403) ); // $role == User::ROLE_GUEST => authentication required
		return false;
	}
	return true;
}



function showErr( $err_num, $msg = '' ) {
	switch( $err_num ):
		case 401:
		?>
			<form method="post" action="login.php" class="form-signin">
				<h2 class="form-signin-heading">Log in</h2>
				<input name="email" type="text" class="input-block-level" placeholder="Email address">
				<input name="password" type="password" class="input-block-level" placeholder="Password">
				<button class="btn btn-large btn-primary" type="submit">Sign in</button>
			</form>
		<?php
			break;
		case 403:
		?>
			<div class="alert">
				<h1>Privilegi insufficienti</h1>
				<p>Questa sezione è riservata. Per richiedere l'accesso, contatta <a href="mailto:<?= WEBMASTER_EMAIL ?>?subject=SelfService%20Portal%20-%20Richiesta%20accesso%20area%20riservata"><?= WEBMASTER_NAME ?></a>.
				</p>
				<p>Nel testo dell'email scrivici la pagina a cui vorresti accedere, per esempio facendo un copia/incolla dell'URL in cui ti trovi ora.
				</p>
				<p>Grazie della collaborazione.</p>
			</div>
		<?php
			break;
		case 404:
		?>
			<div class="alert alert-error">
				<h1>404 error</h1>
				<h3>Page not found</h3>
			</div>
		<?php
			break;
		case 0:
		?>
			<div class="alert alert-error">
				<h1>Unhandled exception</h1>
				<h3>Something horrible has just happened. Please contact <a href="mailto:<?= WEBMASTER_EMAIL ?>?subject=SelfService%20Portal"><?= WEBMASTER_NAME ?></a></h3>
				<code><?= $msg ?></code>
			</div>
		<?php
			break;
		case -1:
		?>
			<div class="alert alert-error">
				<h1>Fatal error</h1>
				<h3>Something horrible has just happened. Please contact <a href="mailto:<?= WEBMASTER_EMAIL ?>?subject=SelfService%20Portal"><?= WEBMASTER_NAME ?></a></h3>
			</div>
		<?php
			break;
		default:
		?>
			<div class="alert alert-error">
				<h1>Unknown error</h1>
			</div>
		<?php
			break;
	endswitch;
	return true;
}