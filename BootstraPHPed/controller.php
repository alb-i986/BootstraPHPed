<?php
/*
Postcond:
 - section $page exists,
 - user is allowed to access $page

*/

require_once("session.php");
require_once("db.php");


	$page = "";
	if(empty($_GET['page'])) {
		$default_row = db_getDefaultSection();
		$page = $default_row['name'];
	} else {
		$p = trim($_GET['page']);
		// security check
		if(preg_match("/^[A-Za-z0-9_\-]*$/", $p) && strlen($p)<30)
			$page = $p;
	}
	
	
	// first, check that $page identifies an existing section
	$section_row = db_getSection($page);
	if( empty($section_row) )
		showErr(404);	
	else if( requireAccessTo($page) ) {
		if ( db_sectionHasSubs($page) )
			$included = @include("section.php");
		else
			$included = @include("section_nosubs.php");
		if(!$included)
			showErr(-1);
	}
?>

<script>
  $(document).ready(function() {
		$("#navbar-<?= $page ?>").addClass('active');
  });
</script>