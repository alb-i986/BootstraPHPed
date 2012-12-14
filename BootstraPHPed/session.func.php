<?php

@session_start(); // gli script che vogliono usare le funzioni di sessione, devono avere la sessione inizializzata!

require_once("globals.php");
require_once("db.php");


/*
	Blocca l'esecuzione *diretta* dello script PHP corrente.
	L'esecuzione è ammessa solo se lo script corrente è stato incluso nello script $includer.
	Se così non è, redirige l'utente in $HOME
	
	Questo filtro, di default, non opera sugli script di tipo Models che si trovano nel path $MODELS_PATH.
	
	@param includer deve essere della forma "/path/file.php" (affinchè possa essere facilmente confrontato con $_SERVER['PHP_SELF'])
*/
function requireIncludeBy($includer) {
	if(empty($includer))
		return false;
		
	//global $HOME, $MODELS_PATH, $INDEX_PATH;

	// sfruttando la var $_SERVER['PHP_SELF'], blocco l'accesso diretto a TUTTI gli script TRANNE...
	if ( !(strpos($_SERVER['PHP_SELF'], MODELS_PATH) === 0) && strcmp($_SERVER['PHP_SELF'], INDEX_PATH))
		header("Location: ". HOME);
	return true;
}


function isLoggedIn() {
	return isset($_SESSION['user']) && !empty($_SESSION['user']);
}

function requireLogin() {
	return requireRole('user');
}


/*
	@param $section may be either an ID or a name of a section
*/
function hasAccessTo($section) {
	$section_row = db_getSection($section);
	if( empty($section_row) )
		return false;
		
	return $_SESSION['user_role'] >= $section_row['min_role'];
}

/*
	@param $section: may be an ID or a name of a (super|sub)section
*/
function requireAccessTo($section) {
	$section_row = db_getSection($section);
	if( empty($section_row) )
		return false;
		
	if( !hasAccessTo($section_row['id']) ) {
		showErr( ( $_SESSION['user_role']==0 ? 401 : 403) );
		return false;
	}
	return true;
}


/*
	Precond: $role > 0
	@param role può essere sia in formato numerico (colonna id della tabella roles) che in formato testuale (colonna name)
*/
function hasRole($role) {
	$role_row = db_getRole($role);
	if( empty($role_row) )
		return false;

	return $_SESSION['user_role'] >= $role_row['id'];
}

/*
	Precond: $role > 0
	Se l'utente loggato non ha il ruolo desiderato, stampa un div con un msg di errore.
	@param role può essere sia in formato numerico (colonna id della tabella roles) che in formato testuale (colonna name)
	@return true SSE l'utente loggato ha il ruolo desiderato o uno superiore
*/
function requireRole($role) {
	$role_row = db_getRole($role);
	if( empty($role_row) )
		return false;
		
	$id = $role_row['id'];
	if(!hasRole($id)) {
		showErr( ( $id <= 1 ? 401 : 403) ); // role<=1 => authentication required
		return false;
	}
	return true;
}



function showErr($err_num) {
	switch($err_num) {
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
				<p>Questa sezione è riservata. Per richiedere l'accesso, contatta <a href="mailto:ito.estesa.passenger@reply.it?subject=SelfService%20Portal%20-%20Richiesta%20accesso%20area%20riservata">ito.estesa.passenger</a>.
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
		case -1:
		?>
			<div class="alert alert-error">
				<h1>Fatal error</h1>
				<h3>Something horrible happened. Please contact <a href="mailto:ito.estesa.passenger@reply.it?subject=SelfService%20Portal">ito.estesa.passenger</a></h3>
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
	}
	return true;
}