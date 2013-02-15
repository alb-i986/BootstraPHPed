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



$sub_row = $dao->getDefaultSubsection($page);
$sub = $sub_row['name'];
if( ! empty( $_GET['sub'] ) ) {
	$s = trim($_GET['sub']);
	if(preg_match("/^[A-Za-z0-9_\-]*$/", $s) && strlen($s)<30)
		$sub = $s;
}

?>
	
		<div class="row-fluid">
			<div class="span2" id="sidemenu">
				<!-- SIDE MENU -->
				
				<ul class="nav nav-pills nav-stacked affix">
					<li class="nav-header"><img src="img/logo.jpg" alt="logo"></li>
					<li class="divider"></li>
					<li class="nav-header"><i class="icon-chevron-down"></i> <?php $s = $dao->getSection($page); echo $s['title']; ?></li>
					
<?php
$subs = $dao->getSubsections($page);
	foreach($subs as $s) {
?>
					<li id="navlist-<?= $s['name'] ?>">
						<a href="index.php?page=<?= $page ?>&sub=<?= $s['name'] ?>">
						<i class="icon-chevron-right"></i> <?= $s['title'] ?>
						</a>
					</li>
<?php
	}
?>
				</ul>
			</div>
			<div class="span10" id="content">
				<!-- BEGIN include subsection in #content -->
				
<?php

	// first, check that $page identifies an existing section
	$sub_row = $dao->getSection($sub);
	if( empty($sub_row) )
		showErr(404);	
	
	$subsection_filename = VIEWS_PATH . $page."_".$sub . ".php";
	if( requireAccessTo($sub) )
		if ( ! @include $subsection_filename )
			showErr(404);
?>

				<!-- END include subsection in #content -->
			</div>
		</div>


<script>
  $(document).ready(function() {
		$("#navlist-<?= $sub ?>").addClass('active');
  });
</script>
