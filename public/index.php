<?php
/**
 * This is the one and only entry point to the system.
 * It generates the XHTML skeleton of the web app (e.g. html, head, body, and main div tags),
 * and then, thanks to a smart use of includes, it delegates to controller.php the generation
 * of the HTML with the contents.
 * 
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed;
//use BootstraPHPed\classes\Persistence;

// This constant is useful for implementing a constraint: the user must see nothing but this page in his URL bar.
// It makes easy for checking in other PHP files that they are being included in this file
const INCLUDED_IN_INDEX = true;

require_once './globals.inc.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="BootstraPHPed is a general purpose extendable PHP application built on top of Bootstrap (and jQuery). It can be used as a base for creating a cool Bootstrapped dynamic web site.">
<meta name="author" content="Alberto Scotto">

<title><?= SYSTEM_NAME ?></title>

<link rel="stylesheet" href="css/bootstrap.min.css" media="screen">

<style type="text/css">
  body {
	padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
	padding-bottom: 40px;
	background: #FFFFFF url(img/img05.jpg) repeat;
	background-color: #f5f5f5
	color: #5B5B5B;
  }
  .white-caret
  {
    border-top-color: #CCCCCC !important;
    border-bottom-color: #CCCCCC !important;
  }
  
/* BEGIN cut&paste from http://twitter.github.com/bootstrap/examples/signin.html */
  .form-signin {
	max-width: 300px;
	padding: 19px 29px 29px;
	margin: 0 auto 20px;
	background-color: #fff;
	border: 1px solid #e5e5e5;
	-webkit-border-radius: 5px;
	   -moz-border-radius: 5px;
			border-radius: 5px;
	-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
	   -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
			box-shadow: 0 1px 2px rgba(0,0,0,.05);
  }
  .form-signin .form-signin-heading,
  .form-signin .checkbox {
	margin-bottom: 10px;
  }
  .form-signin input[type="text"],
  .form-signin input[type="password"] {
	font-size: 16px;
	height: auto;
	margin-bottom: 15px;
	padding: 7px 9px;
  }
/* END cut&paste */


/* BEGIN cut&paste from http://twitter.github.com/bootstrap/examples/sticky-footer.html */

      /* Sticky footer styles
      -------------------------------------------------- */

      html,
      body {
        height: 100%;
        /* The html and body elements cannot have any padding or margin. */
      }

      /* Wrapper for page content to push down footer */
      #wrap {
        min-height: 100%;
        height: auto !important;
        height: 100%;
        /* Negative indent footer by it's height */
        margin: 0 auto -60px;
      }

      /* Set the fixed height of the footer here */
      #push,
      #footer {
        height: 60px;
      }
      #footer {
        background-color: #f5f5f5;
      }

      /* Lastly, apply responsive CSS fixes as necessary */
      @media (max-width: 767px) {
        #footer {
          margin-left: -20px;
          margin-right: -20px;
          padding-left: 20px;
          padding-right: 20px;
        }
      }
/* END cut&paste */



</style>

<link rel="stylesheet" href="css/bootstrap-responsive.min.css">
<link rel="stylesheet" href="css/smoothness/jquery-ui-1.9.1.custom.min.css" />

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->


<script src="js/jquery-1.8.2.min.js"></script>
<script src="js/jquery-ui-1.9.1.custom.min.js"></script>
<script src="js/jquery.blockUI.js"></script>

</head>

<body>
	<!-- top fixed navbar -->
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="index.php"><?= SYSTEM_NAME ?></a>
		  
          <div class="nav-collapse collapse">
            <ul class="nav pull-right">	
<?php
	if( isLoggedIn() ) {
    $props = sess_getUser()->get(array('team', 'nickname'));
?>
				<li class="divider-vertical"></li>
				<li><a><i class="icon-user icon-white"></i> <?= $props['team'] ?> &gt; <?= $props['nickname'] ?></a></li>
				<li class="divider-vertical"></li>
				<li><a href="logout.php" class="navbar-link">LOGOUT <i class="icon-off icon-white"></i></a></li>
<?php
	}
?>
            </ul>
            <ul class="nav">
              <li class="divider-vertical"></li>
			  
<?php
	$supersections = $dao->getSupersections();
	foreach( $supersections as $s ) {
		if( sess_getUser()->hasAccessTo( $s['id'] ) ) {
?>
					<li id="navbar-<?= $s['name'] ?>">
						<a href="index.php?page=<?= $s['name'] ?>"><?= $s['title'] ?></a>
					</li>
<?php
		}
	}
?>
			  
              <li class="divider-vertical"></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

	<div id="wrap">
		<div class="container-fluid" id="container">
<?php
	echo "<!-- BEGIN include section -->\n";
	
	if( ! @include './controller.php' )
		showErr(-1);
	
	echo "<!-- END include section -->";
?>

		</div>
      	<div id="push"></div>
    </div>


	<div id="footer">
		<div class="container-fluid">
			<footer>
			<p class="muted"><a href="https://github.com/alb-i986/BootstraPHPed" target="_new">BootstraPHPed</a> lets you easily bootstrap your own cool PHP application equipped with <a href="http://twitter.github.com/bootstrap/" target="_new">Bootstrap</a> (and jQuery).</p>
			<p class="muted"><a href="https://github.com/alb-i986/BootstraPHPed" target="_new">BootstraPHPed</a> is a project by <a href="http://about.com/alb_i986">Alberto Scotto</a></p>
			<p class="muted"><a href="https://github.com/alb-i986/BootstraPHPed" target="_new"><img src="img/logo.jpg" alt="Logo FPT Passenger"></a></p>
			</footer>
		</div>
	</div>



		<script src="js/bootstrap.min.js"></script>
	</body>
</html>