<?php
/**
 * Precond:
 * * section $page exists;
 * * user is allowed to access $page
 *
 * @author Alberto 'alb-i986' Scotto
 */

require_once './globals.inc.php';
require_once PUBLIC_ROOT_ABSPATH.'/security.php';

?>

		<div class="row-fluid">
			<div class="span10 offset1" id="content">

<?php

	$section_filename = VIEWS_PATH . $page . '.php';
	if ( ! @include $section_filename )
		showErr(404);

?>

			</div>
		</div>
		
