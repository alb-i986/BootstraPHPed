<?php
/**
 * Creates a new post.
 *
 * _Output_
 * In caso di fallimento per cause lato client (es: parametri POST non validi), restituisce un elenco formattato in HTML (tag ul e li) con tutti gli errori.
 * In caso di fallimento per cause "interne" (es: problemi di spazio disco), restituisce gli errori in formato testuale 
 *
 * In caso di successo, restituisce l'URL del nuovo ambiente creato, in formato HTML (con tag a).
 *
 * Authors: al.scotto
 */

require_once '../globals.inc.php';


$outfile=LOGS_PATH."script_new.out";


if($_SERVER["REQUEST_METHOD"] != "POST" ) {
	echo "Invalid request method";
	exit;
}

$errori = array();

$richiedente = $_SESSION['user'];

// check campo ambiente
if ( empty($_POST["ambiente"]) ) {
	$errori[] = "Campo non valorizzato: ambiente";
} else {
	$ambiente = $_POST['ambiente'];
	// controllo che nome ambiente sia valido (tra quelli noti)
	$ambienti_validi = db_getTipiAmbienti();
	$nomeamb_ko = true;
	foreach ($ambienti_validi as $amb) {
		if(!strcmp($amb['name'], $ambiente))
			$nomeamb_ko = false;
	}
	if($nomeamb_ko) {
		$errori[] = "Nome ambiente non valido";
	}
}

// check campo enddate
if ( empty($_POST["enddate"]) ) {
	$errori[] = "Campo non valorizzato: data fine retention";
} else if(!preg_match("/^[0-9]{4,4}-[0-1][0-9]-[0-3][0-9]$/", $_POST["enddate"])) {
	$errori[] = "Data di fine retention non valida: deve essere nel formato 'YYYY-MM-DD'";
} else {
date_default_timezone_set('Europe/Rome');
	$until = $_POST["enddate"];
	$until_obj = new DateTime($until);
	$now = new DateTime();
	if($until_obj <= $now) {
		$errori[] = "Data di fine retention non valida: nel passato";
	}
}

if( !empty($errori) ) {
	array_walk($errori, function(&$el, $key){$el = "<li>".$el."</li>";});
	echo "<ul>\n";
	echo implode("", $errori);
	echo "</ul>\n";
} else {
	$now = date("Ymd_Gi");		
	$pos_trattino = strpos($ambiente, '_');	
	$dir = $richiedente."_".$ambiente."_".$now;


	$out = shell_exec($command);

	if( true ) {
		# registro l'attivita' nel DB
		db_newAmbiente($_SESSION['user_id'], $ambiente, $dir, $until);
		$url = "https://". ESTESA_CERT_HOST ."/$dir";
		echo "<a href=\"$url\" target=\"new\">$url</a>";
	}
}