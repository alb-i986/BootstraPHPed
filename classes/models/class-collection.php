<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed\classes\Persistence;


abstract class Collection implements Model {
	
	protected
		$dao,
		$collection, // list of Elements
		$filters
	;

	public function __construct() {
		$this->dao = DAO::getInstance();
		$collection = array();
		$filters = array();
	}


	/**
	 * Returns the specified properties of each element in this collection.
	 * @param $properties (the allowed values are given by getPropertiesNames)
	 * @return array of rows; each row is an associative array of strings with the values of the properties you asked for
	 */
	public function get( $properties = null ) {
		if( is_null($properties) )
			$properties = static::getPropertiesNames('r');
		else if( ! is_array($properties) )
			throw new InvalidArgumentException('$properties must be either NULL or an array');

		$rows = array();
		foreach ($collection as $element) {
			$rows[] = $element->get($properties);
		}
		return $rows;
	}

	/**
	 * Sets the specified properties to each element in this collection.
	 * @param $properties associative array of writeable properties (<name, value>)
	 * @return an array with the names of the properties which were skipped and not set
	 * @throws InvalidArgumentException if $properties is not an array
	 */
	public function set( $properties ) {
		if( ! is_array($properties) )
			throw new InvalidArgumentException('$properties must be an array');

		$skipped_properties = array();
		foreach ($collection as $element) {
			$skipped_properties[] = $element->set($properties):
		}
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
	 * Validates the specified writeable properties for each element in this collection
	 *
	 * @param $properties array of writeable properties names.
	 *        Default: NULL => all writeable properties are validated
	 * @return array of arrays: each sub-array contains the names of the invalid properties
	 *         for an element in the collection
	 */
	public function validate( $properties = null ) {
		if( is_null($properties) )
			$properties = static::getPropertiesNames('w');
		if( ! is_array($properties) )
			throw new InvalidArgumentException('$properties must be an array');

		$invalid_props = array();
		foreach ($collection as $element) {
			$invalid_props[] = $element->validate($properties):
		}
			
		return $invalid_props;
	}
	
	/**
	 * Before loading the users from the DB with load(), you can apply a filter so that
	 * only a subset of the users saved in the DB are fetched.
	 * You are strongly encouraged to apply a filter unless you need to get all of the rows of a table.
	 *
	 * @param $property the property upon which the filter is to be applied
	 * @param $value the values that are to be filtered
	 * @param $exclude true if the specified value is to be filtered _out_; default: false.
	 */
	public function filter( $property, $value, $exclude=false ) {
		if( empty($property) || ! isset($value) )
			throw new InvalidArgumentException('One or more mandatory arguments are missing');
		if ( ! isPropertyFilterable($property) )
			throw new InvalidArgumentException('Property is not filterable');						
		// TODO check: each element of $ids must be an integer

		$this->filters[$property]['value'] = $value;
		if( $exclude )
			$this->filters[$property]['exclude'] = true;
		else
			$this->filters[$property]['exclude'] = false;
	}


	/**
	 * Loads from the DB the data associated to the user whose ID is $id.
	 * Remembers that the element has been loaded: next loads will be fobidden.
	 * @param $ids array with the IDs of the users to be loaded
	 * @throws InvalidArgumentException if $id does not correspond to an existing user
	 */
	public function load($ids = null) {
		if( ! is_null($ids) )
			throw new InvalidArgumentException('The argument is not used');

		if( ! is_array($ids) )
			
		foreach ($ids as $id) {
			$u = new User();
			$u->load($id);
			$collection[] = $u;
		}

	}


	/**
	 * @return an array of strings filled with the names of the properties
	 *         you can filter on this collection
	 */
	public function getFilterableProperties() {
		return array_keys($filters);
	}
	/**
	 * @return an array of strings filled with the names of the properties
	 *         you can filter on this collection
	 */
	public function isPropertyFilterable($property) {
		return array_key_exists($property, $this->filters);
	}


	/**
	 * @return HTML+Bootstrap table rappresenting this collection
	 */
	public function __toString() {
		// generate the table header
		$html_table_header = '<thead>';
		$html_table_header .= '<tr>';
		$properties_names = $element->getPropertiesNames('ro');
		foreach ($properties_names as $prop) {
			$html_table_header .= '<th>' . $prop . '</th>';
		}
		$html_table_header .= '</tr>';
		$html_table_header = '</thead>';

		// generate the table body
		$html_table_body = '<tbody>';
		foreach ($this->collection as $element) {			
			$html_table_body .= $element;
		}
		$html_table_body = '</tbody>';

		// finally, generate the table
		$html_table = '<table class="table table-striped table-hover table-condensed">';
		$html_table .= $html_table_header . $html_table_body;
		$html_table = '</table>';
	}

}