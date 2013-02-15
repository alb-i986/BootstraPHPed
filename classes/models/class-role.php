<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed\classes\Persistence;


class Role extends Element {

	const ROLE_GUEST = 1;
	const ROLE_USER = 2;
	const ROLE_TEAMLEADER = 3;
	const ROLE_ADMIN = 4;
	
	private $id,
			$name
	;

	/**
	 * By default, it constructs a guest role.
	 */
	public function __construct() {
		parent::__construct();
		$this->load( ROLE_GUEST );
	}

	public function load( $id ) {
		if( empty($id) )
			throw new InvalidArgumentException('Mandatory argument is empty: id');

		$role_row = $this->dao->getRole( $id );
		if( empty($role_row) )
			throw new InvalidArgumentException('Invalid argument: the specified id is not associated to any existent roles');
		return $this->set( $role_row );
	}

	/**
	 * Sets this user's data according to the argument
	 * @param $row associative array with data to be filled in the properties of this object.
	 *				The names of the properties of $row must match the names of the properties of the object
	 */
	public function set( $row ) {
		if( empty($row) )
			throw new InvalidArgumentException('Mandatory argument is empty: row');

		$properties = get_object_vars( $this );
		foreach ($properties as $prop => $val)
			if( array_key_exists( $prop, $row ) )
				$this->$prop = $row[ $prop ];
		return $this;
	}

	/**
	 * Saves all the properties of this instance in the DB.
	 * @return $this in case all the properties of this instance have been validated;
	 *         otherwise, an array with the properties that have not been validated
	 */
	public function save() {
		$properties = get_object_vars( $this );
		$invalid_properties = array();
		$validated = true;
		foreach ($properties as $prop => $val) {
			if( ! $this->validate($prop) )
				$invalid_properties[] = $prop;
		}
		if( ! empty( $invalid_properties ) )
			return $invalid_properties;

		$this->dao->insertRole(
			$this->id,
			$this->name
		);
		return true;
	}

	public function getId() {
		return $this->id;
	}

	public function setId( $id ) {
		$this->id = $id;
	}

	/**
	 * Precond: this user is authenticated
	 */
	public function getName() {
		return $this->name;
	}

	public function __toString() {
		return "Role #".$this->id.":\nname: ".$this->name."\n";
	}




	/**
	 * Validates the data associated to this role.
	 * Constraints: id > 1
	 *
	*/
	public static function validateStatic( $property, $val ) {
		if ( ! property_exists( __CLASS__ , $property ) )
			throw new InvalidArgumentException('Unknown property of the class ' . __CLASS__ .': ' . $property);

		$ok = false;
		switch( $property ) {
			case 'id':
				$ok = preg_match('/^[0-9]+$/', $val) && $val > 1;
				break;
			case 'name':
				$ok = preg_match("/^[!_\-@&A-Za-z0-9]+$/", $val);
				break;
			default:
				$ok = true; // useful for auto-validating properties which do not need validation
				break;
		}
		return $ok;
	}

}