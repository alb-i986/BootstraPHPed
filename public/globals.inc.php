<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */


#namespace BootstraPHPed;
#use BootstraPHPed\classes\Persistence;

/*
spl_autoload_register( function( $class ) {
	$class_filename = 'models/class-' . strtolower($class) . '.php';
	echo '<p>loading class '.$class_filename;
    require_once $class_filename;
});
*/

require_once './config.inc.php';

// sets the defaults for the constants that have not been defined in config.inc.php
if( !defined('DEBUG_MODE') )
	define( 'DEBUG_MODE', false );
if( !defined('HOSTNAME') )
	define( 'HOSTNAME', $_SERVER['HTTP_HOST'] );
if( !defined('URL_BASE') )
	define( 'URL_BASE', '/' );
if( !defined('SYSTEM_NAME') )
	define( 'SYSTEM_NAME', 'BootstraPHPed Web Site' );
if( !defined('WEBMASTER_NAME') )
	define( 'WEBMASTER_NAME', 'webmaster' );
if( !defined('WEBMASTER_EMAIL') )
	define( 'WEBMASTER_EMAIL', 'webmaster@'.HOSTNAME );
if( !defined('TZ') )
	define( 'TZ', 'Europe/Rome' );

// define some paths
define( 'ROOT_ABSPATH', realpath(__DIR__ . '/..') );	// absolute path of the root (not exposed by the web server)
define( 'PUBLIC_ROOT_ABSPATH', __DIR__ );			// absolute path of the public dir containing PHP scripts exposed by the web server
define( 'CLASS_PATH', ROOT_ABSPATH . '/classes/');
define( 'FORM_HANDLERS_RELPATH', './form_handlers/');
define( 'VIEWS_PATH', ROOT_ABSPATH.'/views/');
define( 'LOGS_PATH', ROOT_ABSPATH . '/logs/');

define( 'URL_HOME', 'http://'. $_SERVER['HTTP_HOST'] . URL_BASE);	// URL of the home page (useful for redirects)

// Now, require all the core PHP scripts

// 3rd party libraries
require_once ROOT_ABSPATH.'/lib/adodb5/adodb.inc.php';
require_once ROOT_ABSPATH.'/lib/log4php/Logger.php';
require_once ROOT_ABSPATH.'/lib/phpass-0.3/PasswordHash.php';

// CLASSES imports

// EXCEPTIONS
require_once CLASS_PATH.'class-exception-adodb.php';
require_once CLASS_PATH.'class-exception-illegal-object-state.php';
require_once CLASS_PATH.'class-exception-validation.php';

require_once CLASS_PATH.'models/class-dao_core.php';
require_once CLASS_PATH.'models/class-dao.php';
// MODELS
require_once CLASS_PATH.'models/class-model.php';
	// ELEMENTS
	require_once CLASS_PATH.'models/class-element.php';
		//require_once CLASS_PATH.'models/class-role.php';
		require_once CLASS_PATH.'models/class-user.php';
		//require_once CLASS_PATH.'models/class-post.php';
	// COLLECTIONS
	//require_once CLASS_PATH.'models/class-collection.php';
	//	require_once CLASS_PATH.'models/class-users.php';

require_once './session.inc.php';


$dao = DAO::getInstance();

// default exception handler
set_exception_handler( function($ex) {
	if( DEBUG_MODE == true )
		$msg = nl2br("Uncaught exception:\n" . $ex->getMessage() . "\n". $ex->getTraceAsString() . "\n");
	else
		$msg = nl2br("Uncaught exception:\n" . $ex->getMessage() . "\n");
	showErr(0, $msg);
});

date_default_timezone_set(TZ);