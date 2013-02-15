<?php
/**
 * Implements Data Access Object desgin pattern.
 * Precond. for every function that _modifies_ the DB: the input is validated.
 *
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed\classes\Persistence;


abstract class DAO_Core {

	// SINGLETON
	protected static $dao = null;

	private function __construct() {}

	public static function getInstance() {
		if ( empty(self::$dao) ) {
			self::$dao = new DAO();
			self::$dao->db = ADONewConnection(DB_TYPE);
			
			//Logger::configure('logconfig.xml');
			self::$dao->logger = Logger::getLogger("main");
			//$this->logger = Logger::getLogger("myLogger");
		}
		self::$dao->connect();
		return self::$dao;
	}


	protected 	$db,
				$logger
	;

	/**
	 * After unserializing, restores the connection to DB
	 */
    public function __wakeup() {
		$this->connect();
    }

	private function connect() {
		$this->db->PConnect(HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);
		#$this->db->debug = true;
		return $this->db;
	}



/********* USER-related CRUD methods ***********/

	/**
	 * @return false if the credentials are not correct;
	 *			otherwise, the id of the user
	 */
	public function authUser($username, $password) {
		if( empty($username) || empty($password) )
			throw new InvalidArgumentException('Mandatory argument is empty: username | password');

		$sql = 'SELECT id,password FROM users WHERE email=?';
		$stmt = $this->db->Prepare($sql); // kind of protection against SQL injection
		
		$record_set = $this->db->Execute( $stmt, array($username) );
		if( $record_set->RecordCount() != 1 )
			return false;
		$stored_hash = $record_set->Fields('password');
		$hasher = new PasswordHash(8, FALSE);
		if( ! $hasher->CheckPassword($password, $stored_hash) )
			return false;
		return $record_set->Fields('id');

	}


	public function getUser($user) {
		if( empty($user) )
			throw new InvalidArgumentException('Mandatory argument is empty: user');

		$sql = "SELECT * FROM users WHERE id='$user' OR email='$user'";
		$user_row = $this->db->GetRow($sql);
		if($user_row === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetRow failed. SQL: '.$sql );
			throw new ADOdbException('GetRow failed');
		}
		return $user_row;
	}

	public function insertUser($row) {
		if( empty($row) )
			throw new InvalidArgumentException('Mandatory argument is empty.');

		$sql_header = 'INSERT INTO users ( ';
		$sql_body = ' VALUES ( ';
		$i = 0;
		foreach ($row as $key => $value) {
			$sql_header .= $key;
			$sql_body .= '\'' . $value . '\'';
			if( $i < count($row)-1 ) {
				$sql_header .=  ', ';
				$sql_body .= ', ';
			}
			$i++;
		}
		$sql_header .= ' )';
		$sql_body .= ' )';
		$sql = $sql_header . $sql_body;

		$res = $this->db->Execute($sql);
		if ( !$res || $this->db->Affected_Rows() != 1 )
			throw new ADOdbException('INSERT failed. Err: ' . $this->db->ErrorMsg());
		return true;
	}

	public function updateUser($row) {
		if( empty($row) )
			throw new InvalidArgumentException('Mandatory argument is empty.');
		if( ! is_array($row) )
			throw new InvalidArgumentException('Mandatory argument is not an array.');
		if( ! array_key_exists('id', $row) || empty($row['id']) )
			throw new InvalidArgumentException('The ID of the user to be edited is missing.');

		$sql = 'UPDATE users SET ';
		$i = 0;
		foreach ($row as $key => $value) {
			$sql .= $key . ' = \'' . $value . '\'';
			if( $i < count($row)-1 )
				$sql .=  ', ';
			$i++;
		}
		$sql .= ' WHERE id = ' . $row['id'];
		$res = $this->db->Execute($sql);
		if ( !$res || $this->db->Affected_Rows() != 1 )
			throw new ADOdbException('UPDATE failed. Err: ' . $this->db->ErrorMsg());
		return true;
	}

	public function delUser( $id ) {
		if( empty($id) )
			throw new InvalidArgumentException('Mandatory argument is empty: id.');

		$sql = "DELETE FROM users WHERE id = $id";
		$results = $this->db->Execute($sql);
		if ( !$results || $this->db->Affected_Rows()!= 1 )
			throw new ADOdbException( 'DELETE failed. Err: ' . $this->db->ErrorMsg() );
		return true;
	}

	/**
	 * 
	 */
	public function getUsers($filters) {
		// first we build the query string
		$sql = 'SELECT * FROM users ';
		$no_filters = true;
		foreach ($filters as $property => $filter) {
			if( empty( $filter['ids'] ) )
				$no_filters = false;
		}
		// if there are filters to be applied, then build the WHERE clause
		if( ! $no_filters ) {
			$sql .= ' WHERE ';
			foreach ($filters as $property => $filter) {
				$op = ( $filter['exclude'] ? ' <> ' : ' = ');
				for($i=0; $i < count( $filter['ids'] ); $i++ ) {
					$sql .= $property . $op . $id;
					if( $i < count( $filter['ids'] ) - 1 )
						$sql .= ' AND ';	
				}
			}			
		}

		$rows = $this->db->GetAll($sql);
		if($rows === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetAll failed. SQL: '.$sql );
			throw new ADOdbException('GetAll failed');
		}
		return $rows;
	}



/********* ROLE-related CRUD methods ***********/

	public function getRole($role) {
		if( empty($role) )
			throw new InvalidArgumentException('Mandatory argument is empty: role');

		$sql = "SELECT * FROM roles WHERE id='$role' OR name='$role'";
		$row = $this->db->GetRow($sql);
		if($row === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetRow failed. SQL: '.$sql );
			throw new ADOdbException('GetRow failed');
		}
		return $row;
	}

	public function getRoles() {
		$sql = "SELECT * FROM roles";
		$rows = $this->db->GetAll($sql);
		if($rows === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetAll failed. SQL: '.$sql );
			throw new ADOdbException('GetAll failed');
		}
		return $rows;
	}

	public function getRolesIDs() {
		$sql = 'SELECT id FROM users';
		$ids = $this->db->GetCol($sql);
		if($ids === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetAll failed. SQL: '.$sql );
			throw new ADOdbException('GetAll failed');
		}
		return $ids;
	}

	/**
	 * 
	 */
	public function insertRole( $name, $id = null ) {
		if( empty( $id ) || empty($name) )
			throw new InvalidArgumentException('Mandatory argument is empty.');

		$role_row = $this->getRole($id);
		if( $id == $role_row['id'] && $name != $role_row['name'] ) {
			// TODO
		}


		$sql = "INSERT INTO roles (id, name) VALUES ( $id, '$name' )";
		$results = $this->db->Execute($sql);
		if ( !$results || $this->db->Affected_Rows()!= 1 ) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb Execute failed. Err: '.$this->db->ErrorMsg().' SQL: '.$sql );
			throw new ADOdbException('Insert failed.');
		}
		return true;
	}

	/********* SECTION-related CRUD methods ***********/


	/**
	 * @param $section may be an ID or a name of a (super|sub) section
	 * @return full row of the section IF the section exists;
	 *         an empty array if the section does not exist
	 * @throws InvalidArgumentException if $section is empty
	 *         ADOdbException if ADOdb's query fails
	 */
	public function getSection( $section ) {
		if( empty($section) )
			throw new InvalidArgumentException('Mandatory argument is empty: section');
		
		$sql = "SELECT * FROM sections WHERE id='$section' OR name='$section'";
		$row = $this->db->GetRow($sql);
		if($row === FALSE)  {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetRow failed. SQL: '.$sql );
			throw new ADOdbException('GetRow failed');
		}
		return $row;
	}

	/**
	 * @param $section may be an ID or a name of a subsection
	 */
	public function getSubsections($section) {
		if( empty($section) )
			throw new InvalidArgumentException('Mandatory argument is empty: $section.');
		$section_row = $this->getSection($section);
		if( empty($section_row) )
			return false;

		$sql = "SELECT * FROM sections WHERE super=".$section_row['id']." ORDER BY sort ASC";
		$rows = $this->db->GetAll($sql);
		if($rows === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetAll failed. SQL: '.$sql );
			throw new ADOdbException('GetAll failed');
		}
		return $rows;
	}

	/**
	 * @param $section can be either an ID or a name
	 */
	public function sectionHasSubs( $section ) {
		if( empty($section) )
			throw new InvalidArgumentException('Mandatory argument is empty: $section.');
		$section_row = $this->getSection($section);
		if(empty($section_row))
			return false;

		$sql = "SELECT id FROM sections WHERE super=".$section_row['id'];
		$subsection = $this->db->GetRow($sql);
		if($subsection === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetRow failed. SQL: '.$sql );
			throw new ADOdbException('GetRow failed');
		}
		return !empty($subsection);
	}

	/**
	 * @param $section can be either an ID or a name of a (super|sub) section
	 * @return true if $section is not a subsection (it may have or not a subsection)
	 */
	public function sectionIsSuper($section){
		if( empty($section) )
			throw new InvalidArgumentException('Mandatory argument is empty: $section.');
		$section_row = $this->getSection($section);
		if(empty($section_row))
			return false;

		$sql = "SELECT id FROM sections WHERE super IS NULL AND id=".$section_row['id'];
		$super = $this->db->GetRow($sql);
		if($super === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetRow failed. SQL: '.$sql );
			throw new ADOdbException('GetRow failed');
		}
		return !empty($super);
	}


	public function getDefaultSubsection($supersection) {
		if( empty($supersection) )
			throw new InvalidArgumentException('Mandatory argument is empty: $supersection.');
		$super_row = $this->getSection($supersection);
		if(empty($super_row))
			throw new InvalidArgumentException('Invalid argument: the specified supersection does not have a default subsection.');
			
		if( ! $this->sectionHasSubs($supersection) ) {
			return false;
		}

		$sql = "SELECT MIN(sort) FROM sections WHERE super=".$super_row['id'];
		$sort = $this->db->GetOne($sql);
		if( empty($sort) ) return false;
		$sql = "SELECT id FROM sections WHERE super=".$super_row['id']." AND sort=".$sort;
		return $this->getSection($this->db->GetOne($sql));
	}

	/*
		@return full row of the default super section, i.e. the super section with the least 'sort' value
			If there are more than one supersections with the least 'sort' value, then it returns the first row it encounters.
	*/
	/**
	 * @return full row of the default super section, i.e. the super section with the least 'sort' value;
	 *         the first row it encounters, if there are more than one supersections with the least 'sort' value
	 */
	public function getDefaultSection() {
		$sql = "SELECT MIN(sort) FROM sections WHERE super IS NULL";
		$sort = $this->db->GetOne($sql);
		if( empty($sort) ) return false;
		$sql = "SELECT id FROM sections WHERE super IS NULL AND sort=".$sort;
		return $this->getSection( $this->db->GetOne($sql) );
	}

	/**
	 * @return array of rows of all the super-sections, sorted by 'sort' column
	 */
	public function getSupersections() {
		$sql = "SELECT * FROM sections WHERE super IS NULL ORDER BY sort ASC";
		$rows = $this->db->GetAll($sql);
		if($rows === FALSE) {
			$this->logger->error( __CLASS__ . '' . __METHOD__ . ':'. __LINE__ .' ADOdb GetAll failed. SQL: '.$sql );
			throw new ADOdbException('GetAll failed');
		}
		return $rows;
	}

}
