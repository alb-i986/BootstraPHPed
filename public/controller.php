<?php
/**
 * Postcond:
 * * section $page exists;
 * * user is allowed to access $page.
 *
 * @author Alberto 'alb-i986' Scotto
 */
/**
 */

require_once './globals.inc.php';
require_once PUBLIC_ROOT_ABSPATH.'/security.php';


$page = '';
if(empty($_GET['page'])) {
	$default_row = $dao->getDefaultSection();
	$page = $default_row['name'];
} else {
	$p = trim($_GET['page']);
	// security check
	if(preg_match("/^[A-Za-z0-9_\-]*$/", $p) && strlen($p)<30)
		$page = $p;
}

// first, check that $page identifies an existing section
$section_row = $dao->getSection($page);
if( empty($section_row) )
	showErr( 404 );
else if( requireAccessTo($page) ) {
	if ( $dao->sectionHasSubs($page) )
		$included = @include './section.php';
	else
		$included = @include './section_nosubs.php';
	if( ! $included )
		showErr( -1 );
}
?>

<script>
  $(document).ready(function() {
		$('#navbar-<?= $page ?>').addClass('active');
  });
</script>