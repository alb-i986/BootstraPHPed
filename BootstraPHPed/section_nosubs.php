<?php


/*
Precond: section $page exists, user is allowed to access $page
*/


require_once("session.php");

?>

		<div class="row-fluid">
			<div class="span10 offset1" id="content">

<?php

	$section_filename = $page . ".php";
	if (!@include($section_filename) )
		showErr(404);

?>

			</div>
		</div>
		
