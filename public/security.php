<?php
/**
 * 
 * @author Alberto 'alb-i986' Scotto
 */

/*
	Questo script raccoglie le chiamate ai comportamenti inibitori comuni.
	Facendo la require (non require_once!) di questo script, si "ereditano" gratis tali comportamenti.
	
	N.B: require instead of require_once since we want to execute the security functions in every page this script is included
*/

require_once './globals.inc.php';


// prevents the direct access the scripts that include this file
// e.g. the user who connects to http://hostname/includee.php will be redirected to the home page
requireIncludeByIndex();



/**
 * Blocca l'esecuzione *diretta* dello script PHP corrente.
 * L'esecuzione è ammessa solo se lo script corrente è stato incluso nello script index.php.
 * Se così non è, redirige l'utente alla homepage
 */
function requireIncludeByIndex() {
	// sfruttando la var globale 'INCLUDED_IN_INDEX' definita in index.php, 
	// posso dedurre se sono stato incluso in index.php o meno
	if( ! defined('INCLUDED_IN_INDEX') )
		header('Location: '. URL_HOME);
}


/**
 * Blocca l'esecuzione *diretta* dello script PHP corrente.
 *
 * L'esecuzione è ammessa solo se lo script corrente è stato incluso nello script $includer.
 * Se così non è, redirige l'utente alla homepage
 * Questo filtro, di default, non opera sugli script di tipo Models che si trovano nel path $MODELS_PATH.
 *
 * @param includer deve essere della forma "/path/file.php" (affinchè possa essere facilmente confrontato con $_SERVER['PHP_SELF'])
 */
function requireIncludeBy( $includer ) {
	if( empty($includer) )
		return false;

	// sfruttando la var $_SERVER['PHP_SELF'], blocco l'accesso diretto a TUTTI gli script TRANNE...
	if ( !(strpos($_SERVER['PHP_SELF'], MODELS_PATH) === 0) && strcmp($_SERVER['PHP_SELF'], URL_BASE . 'index.php'))
		header('Location: '. URL_HOME);
	return true;
}