<?php

//namespace BootstraPHPed\classes\Persistence;


/**
 * 
 * @author Alberto 'alb-i986' Scotto
 */
class User extends Element {

	const ROLE_GUEST		= 1;
	const ROLE_USER			= 2;
	const ROLE_TEAMLEADER	= 3;
	const ROLE_ADMIN		= 4;
	
	protected
		$authenticated = false,
		$id,
		$email,
		$password,
		$nickname,
		$role,
		$team
	;

	/**
	 * By default, it constructs a non-authenticated user, aka 'guest' (guests have role = 1).
	 */
	public function __construct() {
		parent::__construct();
		$this->authenticated = false;
		$this->role = self::ROLE_GUEST;
	}

	/**
	 * @param $access_mode:
	 *           * 'r', for read access
	 *           * 'w', for write access
	 *           * 'rw' for read & write access
	 * @return an array of strings filled with the names of the accessible properties
	 */
	public static function getPropertiesNames($access_mode) {
		$this_model_prop_names = array(
			'readable' => array(
				'id',
				'email',
				'password',
				'nickname',
				'role',
				'team',
			),
			'writeable' => array(
				'email',
				'password',
				'nickname',
				'role',
				'team',
			),
		);
		return parent::_getPropertiesNames($access_mode, __CLASS__, $this_model_prop_names);
	}

	/**
	 * Sets the specified properties of this element, according to the argument
	 * @param $properties associative array of writeable properties (<name, value>)
	 * @return an array with the names of the properties which were skipped and not set
	 * @throws InvalidArgumentException if $properties is not an array
	 */
	public function set( $properties ) {
		$skipped_properties = parent::set($properties);
		// and now the business logic..

		// setting a password is a particular case: it needs to be hashed
		// before it can be assigned to this user's 'password' property
		if(
			array_key_exists('password', $properties)
			&& ! array_key_exists('password', $skipped_properties)
		) {
			$t_hasher = new PasswordHash(8, FALSE);
			$this->password = $t_hasher->HashPassword( $properties['password'] );
		}

		return $skipped_properties;
	}

	/**
	 * Loads from the DB the data associated to the user whose ID is $id.
	 * Remembers that the element has been loaded: next loads will be fobidden.
	 * @param $id the ID of the user to be loaded
	 * @throws InvalidArgumentException if $id does not correspond to an existing user
	 */
	public function load($id) {
		parent::load($id);
		// TODO: with a few more naming conventions, we could move this method to the superclass
		// e.g. $this->dao->getUser($id) --> $this->dao->{'get'.__CLASS__}($id)

		$user_row = $this->dao->getUser($id);
		if( empty($user_row) )
			throw new InvalidArgumentException('Invalid argument: the specified id is not associated to any existent users');
		$this->_set($user_row);
		$this->inhibitLoading();
	}


	/**
	 * Saves the specified properties in the DB.
	 * It is interpreted as an edit if this user's 'id' is defined.
	 * Otherwise, it is interpreted as a create.
	 * Precond.: the data has already been validated (typically by the method set or load)
	 *
	 * @return an array with the names of the properties that was not possible to save
	 *         because invalid.
	 * @throws ADOdbException if the SQL query failed (e.g. because of a foreign key issue)
	 */
	public function save($properties = null) {
		$properties_to_be_saved = parent::save($properties);
		if( is_null($properties) )
			$properties = static::getPropertiesNames('w');
		$skipped_properties = array_diff($properties, $properties_to_be_saved);
		// now in $properties_to_be_saved we have valid properties names
		$props = $this->get($properties_to_be_saved);
		if( ! empty($this->id) ) // it's an edit/UPDATE
			$res = $this->dao->updateUser($props + array('id' => $this->id) );
		else // it's an add/INSERT
			$this->dao->insertUser($props);
		return $skipped_properties;
	}

	/**
	 * Deletes this user from the DB.
	 * The user to be deleted is the one with ID as the property id of this instance.
	 *
	 * @throws IllegalObjectStateException if $this->id is not defined
	 * @throws ValidationException if $this->id is not valid (either for syntax errors or unknown ID)
	 */
	public function delete() {
		parent::delete();
		$user_row = $this->dao->getUser( $this->id );
		// execute the delete only if this user is in the DB
		if( ! empty( $user_row ) )
			$this->dao->delUser( $this->id );
		return true;
	}

	/**
	 * Defines some business rules that prevent access to some readable properties
	 * under certain conditions (which depend on the state of the object).
	 * These rules are to be applied in the parent's method 'get'.
	 * In this case, if this user is not authenticated, then only the access to 'role' is permitted.
	 * @param $property the name of a (readable) property
	 * @return true if $property is accessible
	 */
	protected function _getIsAllowed($property) {
		$allowed = false;
		switch ($property) {
			// all the _inaccessible_ properties go here
			case 'email':		// jumps to case 'team'
			case 'id':			// jumps to case 'team'
			case 'nickname':	// jumps to case 'team'
			case 'password':	// jumps to case 'team'
			case 'team':
				if( $this->role == self::ROLE_GUEST )
					$allowed = false;
				else
					$allowed = true;
				break;

			// all the accessible properties go here
			case 'role':
				$allowed = true;
				break;
			default:
				throw new InvalidArgumentException('The access to the specified property is prevented (undefined?)');				
				break;
		}
		return $allowed;
	}



	/**
	 * Validates the data associated to this user.
	*/
	public static function validateStatic( $property, $value ) {
		if( empty( $property ) )
			throw new InvalidArgumentException('Mandatory argument is empty: property');
		if( empty( $value ) )
			throw new InvalidArgumentException('Mandatory argument is empty: value');
		if ( ! property_exists( __CLASS__ , $property ) )
			throw new InvalidArgumentException('Unknown property of the class ' . __CLASS__ .': ' . $property);

		$ok = false;
		switch( $property ) {
			case 'id':
				$ok = preg_match('/^[0-9]+$/', $value);
				break;
			case 'email':
				$ok = preg_match("/^[-!#$%&'*+\/0-9=?A-Z^_a-z{|}~](\.?[-!#$%&'*+\/0-9=?A-Z^_a-z{|}~])*@[a-zA-Z](-?[a-zA-Z0-9])*(\.[a-zA-Z](-?[a-zA-Z0-9])*)+$/", $value);
				break;
			case 'nickname':
				$ok = preg_match("/^[!_'àèìòù\-@&A-Za-z0-9]+$/", $value);
				break;
			case 'role':
				$ok = preg_match("/^[_\-A-Za-z0-9]+$/", $value);
				break;
			case 'team':
				$ok = preg_match("/^[_'àèìòù\-@&A-Za-z0-9]+$/", $value);
				break;
			case 'password':
				$ok = true;
				break;
			default:
				$ok = true; // useful for auto-validating properties which do not need validation
				break;
		}
		return $ok;
	}


	/**
	 * Precond: $role > 0
	 * @param $role può essere sia in formato numerico (colonna id della tabella roles) che in formato testuale (colonna name)
	 */
	public function hasRole( $role ) {
		if( empty($role) )
			throw new InvalidArgumentException('Mandatory argument is empty: role');
		$role_row = $this->dao->getRole( $role );
		if( empty($role_row) )
			throw new InvalidArgumentException('Invalid argument: the specified role does not exist in the DB.');

		return $this->role >= $role_row['id'];
	}

	/**
	 * @param $section may be either an ID or a name of a section
	 */
	public function hasAccessTo( $section ) {
		if( empty($section) )
			throw new InvalidArgumentException('Mandatory argument is empty: section');
		$section_row = $this->dao->getSection( $section );
		if( empty($section_row) )
			throw new InvalidArgumentException('Invalid argument: the specified section does not exist in the DB.');

		return $this->role >= $section_row['min_role'];
	}

	public function isAuthenticated() {
		return $this->authenticated;
	}

	/**
	 * @param $bool if 'false' then flags this user as not authenticated. Default: true
	 */
	public function setAuthenticated( $bool = true ) {
		$this->authenticated = $bool;
	}
}