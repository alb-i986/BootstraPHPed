<?php 
/**
 * 
 * @author Alberto 'alb-i986' Scotto
 */

require_once './globals.inc.php';

@session_start();
session_unset();
session_destroy();

header('Location: ' . URL_HOME);
