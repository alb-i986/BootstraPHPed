<?php

//namespace BootstraPHPed\classes\Persistence;


/**
 * Class invariant:
 * If $this->id is defined, then this is an existing element. So, the method 'save' has the meaning of 'edit'.
 * Otherwise, if the client has already called 'set', then this is a new element, and 'save' has the meaning of 'create'.
 * But if it has just been constructed, we can't know.
 *
 * @author Alberto 'alb-i986' Scotto
 */
abstract class Element implements Model {

	/*
	a multi-level map defining the names of the readable and writeable properties of the concrete subclasses
	e.g. 
	$accessible_properties_names = 
		array(
			'ConcreteElement1' => array(
				'readable' => array('r_prop1', 'r_prop2'),
				'writeable' => array('w_prop1', 'w_prop2', 'w_prop3'),
			),
			'ConcreteElement2' => array(
				'readable' => array('r_prop1', 'r_prop2', 'r_prop3'),
				'writeable' => array('w_prop1'),
			),
		);
	*/
	private static $accessible_properties_names = array();

	protected $dao;

	private $is_loading_inhibited;

	public function __construct() {
		$this->dao = DAO::getInstance();
		$this->is_loading_inhibited = false;
	}

	private static function isEmptyPropertiesNames($class) {
		return ! array_key_exists($class, self::$accessible_properties_names);
	}
	private static function setPropertiesNames($class, $names) {
		self::$accessible_properties_names[$class] = $names;
	}
	private static function initPropertiesNames($class, $names) {
		if( self::isEmptyPropertiesNames($class) )
			self::setPropertiesNames($class, $names);
	}
	/**
	 * @param $class a concrete Element's class name
	 * @param $access_mode
	 *           * 'r' for read access
	 *           * 'w' for write access
	 *           * 'rw' for read & write access
	 * @return an array of strings filled with the names of the properties
	 */
	protected static function _getPropertiesNames($access_mode, $class, $names) {
		self::initPropertiesNames($class, $names);
		$names = array();
		// TODO: drop the switch statement for something more clever: parse the string $access_mode!
		// TODO: extend $access_mode with read-only (Readable - Writeable) and write-only (Writeable - Readable)
		switch ($access_mode) {
			case 'r':
			case 'R':
				$names = self::$accessible_properties_names[$class]['readable'];				
				break;
			case 'w':
			case 'W':
				$names = self::$accessible_properties_names[$class]['writeable'];				
				break;
			case 'rw':
			case 'RW':
				// TODO FAIL: this union of sets doesn't work!!!
				// computes the union of the set "readable properties" with the set "writeable properties"
				$names = array_unique( array_merge(self::$accessible_properties_names[$class]['writeable'], self::$accessible_properties_names[$class]['readable']) );
				break;
			
			default:
				throw new InvalidArgumentException('Unknown access mode to properties');				
				break;
		}
		return $names;
	}

	/**
	 * Returns the asked properties in a best effort way: only the readable and accessible
	 * properties are returned.
	 *
	 * @param $properties (optional) Array of strings, where each string is the name of a property
	 *                    and should match one of the values returned by getPropertiesNames.
	 *                    Default: NULL i.e. all the readable properties
	 * @return associative array of properties; each element is a <key, value> pair,
	 *         where 'key' is the name of the property, and 'value' is the actual value of that property.
	 *         The properties that do not exist or that are not accessible for some reasons
	 *         (eg. not readable) are gracefully skipped.
	 *         If $properties is an empty array, then it returns an empty array.
	 *         If $properties is not specified, then all the readable & accessible properties are returned.
	 */
	public function get( $properties = null ) {
		if( is_null($properties) )
			$properties = static::getPropertiesNames('r');
		else if( ! is_array($properties))
			throw new InvalidArgumentException('$properties must be either an array or NULL');	

		$row = array();
		foreach ($properties as $prop) {
			if( 
				property_exists($this, $prop)
				&& in_array( $prop, static::getPropertiesNames('r') ) // property is readable
				&& $this->_getIsAllowed($prop) // allowed to get the property (business rules)
			) {
				$row[$prop] = $this->$prop;
			}
		}
		return $row;
	}
	
	/**
	 * Sets the specified properties of this element, according to the argument
	 * @param $properties associative array of writeable properties (<name, value>)
	 * @return an array with the names of the properties which were skipped and not set
	 * @throws InvalidArgumentException if $properties is not an array
	 */
	public function set( $properties ) {
		if( ! is_array($properties) )
			throw new InvalidArgumentException('$properties must be an array');

		$skipped_properties = array();
		if( ! empty($properties) ) {
			foreach ($properties as $key => $value) {
				if(
					property_exists($this, $key)
					&& in_array( $key, static::getPropertiesNames('w') ) // property is writeable
					&& static::validateStatic($key, $value) // the new value for property is validated
				) {
					$this->$key = $value;
				} else { // couldn't set a property: report its name to the client
					$skipped_properties[] = $key;
				}
			}
		}
		return $skipped_properties;
	}

	/**
	 * Internal set method which skips the checks on the input, assuming it's correct
	 * (e.g. because we have just fetched it from the DB). Useful for the method 'load'.
	 * @param $properties associative array of properties (<name, value>)
	 * @throws InvalidArgumentException if $properties is not an array
	 */
	protected function _set( $properties ) {
		if( ! is_array($properties) )
			throw new InvalidArgumentException('$properties must be an array');
		foreach($properties as $key => $value)
			if( property_exists($this, $key) )
				$this->$key = $value;
	}	

	/**
	 * Validates the specified writeable properties
	 * @param $properties array of writeable properties names. Default: NULL => all writeable properties are validated
	 * @return array of strings with the names of the properties which were not valid (undefined, unwriteable, ..)
	 */
	public function validate( $properties = null ) {
		if( is_null($properties) )
			$properties = static::getPropertiesNames('w');
		if( ! is_array($properties) )
			throw new InvalidArgumentException('$properties must be an array');
		$invalid_props = array();
		foreach ($properties as $property) {
			if(
				property_exists($this, $property)
				&& in_array($property, static::getPropertiesNames('w')) // property is writeable
			) {
				$validated = static::validateStatic($property, $this->$property);
			} else { // the specified property doesn't exist or is not writeable
				$validated = false;
			}
			if( ! $validated )
				$invalid_props[] = $property;
			return $invalid_props;
		}
	}

	/**
	 * This method must be overridden and specialized in every concrete subclass
	 * (i.e. it must be called by the overriding method before any processing)
	 */
	public function load($id) {
		if( empty($id) )
			throw new InvalidArgumentException('Mandatory argument is empty: id');
		if( $this->isLoadInhibited() )
			throw new IllegalObjectStateException('The load for this Model Element is inhibited.');
		if( ! static::validateStatic('id', $id) )
			throw new InvalidArgumentException('Mandatory argument is invalid: id');
	}

	/**
	 * This is a convenience method, and needs to be specialized by every concrete subclass
	 * (i.e. it must be called by the overriding method before any processing).
	 *
	 * @return an array with the names of the properties that are to be saved
	 */
	public function save($properties = null) {
		$properties_to_be_saved = array();
		if( is_null($properties) )
			$properties_to_be_saved = static::getPropertiesNames('w');
		else { // check that $properties are OK
			if( ! is_array($properties) )
				throw new InvalidArgumentException('$properties must be an array');
			foreach ($properties as $property) {
				if(
					property_exists($this, $property) // property exists
					&& in_array($property, static::getPropertiesNames('w')) // property is writeable
				) {
					$properties_to_be_saved[] = $property;
				}
			}
		}
		return $properties_to_be_saved;
	}

	/**
	 * Deletes this user from the DB.
	 * The user to be deleted is the one with ID as the property id of this instance.
	 *
	 * @throws IllegalObjectStateException if $this->id is not defined
	 * @throws ValidationException if $this->id is not valid (either for syntax errors or unknown ID)
	 */
	public function delete() {
		if( empty($this->id) )
			throw new IllegalObjectStateException('Property id is not defined');
		if( ! static::validateStatic('id', $this->id) )
			throw new ValidationException('Property id is not valid: id = '. $this->id);
	}


	/**
	 * HTML representation of this user, as a tabl roe (tr) element
	 * @return an HTML table row (tr) element with this user's readable data
	 */
	public function __toString() {
		$properties = $this->get();
		$html_tr = '<tr>';
		foreach ($properties as $key => $value) {
			$html_tr .= '<td>' . $value . '</td>';
		}
		$html_tr .= '</tr>';
		return $html_tr;
	}

	
	protected function inhibitLoading() {
		$this->is_loading_inhibited = true;
	}

	public function isLoadInhibited() {
		return $this->is_loading_inhibited;
	}

	
/* 
	//load with NAMING CONVENTION so that I can define it in the Element class (experimental!)
	public function load($id) {
		// TODO: with a few more naming conventions, we could move this method to the superclass
		// e.g. $this->dao->getUser($id) --> $this->dao->get__CLASS__($id)
		if( empty($id) )
			throw new InvalidArgumentException('Mandatory argument is empty: id');
		if( ! self::validateStatic('id', $id) )
			throw new InvalidArgumentException('Mandatory argument is invalid: id');

		$get_method = 'get'.__CLASS__; // NAMING CONVENTION (experimental!)
		$user_row = $this->dao->$get_method($id);
		if( empty($user_row) )
			throw new InvalidArgumentException('Invalid argument: the specified id is not associated to any existent users');
		return $this->_set($user_row);
	}
*/

}
