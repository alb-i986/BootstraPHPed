<?php

/*
	Precond: section $page exists, user is allowed to access $page
*/

require_once("session.php");
require_once("db.php");


$sub_row = db_getDefaultSubsection($page);
$sub = $sub_row['name'];
if(!empty($_GET['sub'])) {
	$s = trim($_GET['sub']);
	if(preg_match("/^[A-Za-z0-9_\-]*$/", $s) && strlen($s)<30)
		$sub = $s;
}

?>
	
		<div class="row-fluid">
			<div class="span2" id="sidemenu">
				<!-- SIDE MENU -->
				
				<ul class="nav nav-pills nav-stacked affix">
					<li class="nav-header"><img src="img/fpt_logo.jpg"></li>
					<li class="divider"></li>
					<li class="nav-header"><i class="icon-chevron-down"></i> <?php $s = db_getSection($page); echo $s['title']; ?></li>
					
<?php
$subs = db_getSubsections($page);
	foreach($subs as $s) {
?>
					<li id="navlist-<?= $s['name'] ?>">
						<a href="?page=<?= $page ?>&sub=<?= $s['name'] ?>">
						<i class="icon-chevron-right"></i> <?= $s['title'] ?>
						</a>
					</li>
<?php
	}
?>
				</ul>
			</div>
			<div class="span10" id="content">
				<!-- BEGIN include subsection in #content) -->
				
<?php

	// first, check that $page identifies an existing section
	$sub_row = db_getSection($sub);
	if( empty($sub_row) )
		showErr(404);	
	
	$subsection_filename = $page."_".$sub . ".php";
	if( requireAccessTo($sub) )
		if (!include($subsection_filename) )
			showErr(404);
?>

				<!-- END include subsection in #content) -->
			</div>
		</div>


<script>
  $(document).ready(function() {
		$("#navlist-<?= $sub ?>").addClass('active');
  });
</script>
