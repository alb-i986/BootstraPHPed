<?php 

function db_newAmbiente($requestor_id, $amb, $path, $data_end) {
	if(empty($requestor_id) || empty($amb) || empty($path) || empty($data_end))
		return false;

	$db = db_conn();
	$sql = "INSERT INTO istanze (name, requestor, path, date_start, date_end) VALUES ( '" . $amb ."', '". $requestor_id ."', '". $path ."', '". date("Y-m-d") ."', '". $data_end ."')";
	$results = $db->Execute($sql);
	if ( !$results || $db->Affected_Rows()!= 1 ) {
		error_log($db->ErrorMsg());
		return false;
	}
	return true;
}


function db_getTipiAmbienti() {
	$db = db_conn();
	$sql = "SELECT name FROM ambienti";
	$stmt = $db->Prepare($sql);
	return $db->GetAll($stmt);
}

function db_getAmbienti($requestor_id) {
	if(empty($requestor_id))
		return false;
	$db = db_conn();
	$sql = "SELECT id, name, path, date_start, date_end, deletable FROM istanze WHERE deleted=0 AND requestor=". $requestor_id;
	return $db->GetAll($sql);
}

function db_getAmbientiCancellabili() {
	$db = db_conn();
	$sql = "SELECT id, path FROM istanze WHERE deletable=1 AND deleted=0";
	return $db->GetAll($sql);
}

/**
	Returns gli ambienti creati dai team mates di $user_id
*/
function db_getAmbientiTeam($user_id) {
	if(empty($user_id))
		return false;
	$db = db_conn();
	
	if(!isset($_SESSION['user_team'])) {
		return false;
	}
	$team = $_SESSION['user_team'];	
			
	$sql = "SELECT a.id, a.name, a.path, a.date_start, a.date_end, a.deletable, u.nickname
			FROM users u JOIN istanze a on a.requestor = u.id
			WHERE a.deleted=0 AND u.id IN (SELECT id FROM users WHERE team='". $team ."' AND id<>". $user_id .")";			
	return $db->GetAll($sql);
}


function db_flagAmbientiDaCancellare($requestor_id, $ambienti) {
	if(empty($requestor_id) || empty($ambienti))
		return false;
	$db = db_conn();
	$sql = "UPDATE istanze SET deletable=1 WHERE deletable=0 AND requestor='$requestor_id' AND ";
	$i = 0;
	foreach($ambienti as $amb) {
		if($i == 0)
			$sql .= " ( id=". $amb;
		else
			$sql .= " OR id=". $amb;
		$i++;
	}
	$sql .= " ) ";
	$res = $db->Execute($sql);
	if ( count($ambienti) == $db->Affected_Rows() )
		return true;
	else
		return false;
}

/*
 * @param $rows un array di righe rappresentanti istanze di ambienti, dove ogni riga e' un array di colonne
*/
function db_delAmbienti($rows) {
	global $logger, $LOGS_PATH, $SCRIPTS_PATH;

	$outfile = $LOGS_PATH."script_del.out";
	$db = db_conn();
	
	// STEP 1: phisically remove envs
	$ids_ok = array();	
	foreach($rows as $amb) {
		$out = shell_exec($SCRIPTS_PATH."script_del.sh ".$amb['path']);
		if( !isset($out) ) {
			$logger->error("phisical removal of env ". $amb['path'] ." failed.");
		} else {
			file_put_contents($outfile, $out, FILE_APPEND);
			$ids_ok[] = $amb['id'];
		}
	}
	
	// STEP 2: delete rows in DB locale di SelfService
	$sql = "DELETE FROM istanze WHERE id=?";
	$stmt = $db->Prepare($sql);
	
	$ok = true;
	foreach($ids_ok as $id) {
		$res = $db->Execute($stmt, array($id));
		if(!$res) {
			$ok = false;
			$logger->error("Local DB removal of env #$id failed.");
		}
	}
	
	if ( count($rows) == count($ids_ok) && $ok)
		return true;
		
	return false;
}