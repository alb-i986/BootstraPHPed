<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed\classes\Persistence;


class Users extends Collection {


	public function __construct() {
		parent::__construct();
		$filters = array(
			'role' => array(),
			'team' => array(),
		);
	}

	public static function getPropertiesNames($access_mode) {
		return User::getPropertiesNames($access_mode);
	}

	/**
	 * Loads from the DB the data associated to the user whose ID is $id.
	 * Remembers that the element has been loaded: next loads will be fobidden.
	 * @param $id the ID of the user to be loaded
	 * @throws InvalidArgumentException if $id does not correspond to an existing user
	 */
	public function load($ids) {
		parent::load($ids);

		$user_row = $this->dao->getUser($id);
		if( empty($user_row) )
			throw new InvalidArgumentException('Invalid argument: the specified id is not associated to any existent users');
		$this->_set($user_row);
		$this->inhibitLoading();
	}

	public function load() {
		$rows = $dao->getUsers( $this->filters );
		foreach ($rows as $row) {
			$u = new User();
			$u->set($row);
			$collection[] = $u;			
		}
	}

	public function set( $rows ) {
		$i = 0;
		foreach ($collection as $el) {
			$u = new User();
			$el->set( $rows[$i] );
			$i++;
		}
	}
	
	public function save() {
		foreach ($collection as $el) {
			$el->save();
		}
		return true;
	}

	public function delete() {
		foreach ($collection as $el) {
			$el->delete();
		}
		return true;
	}


	
	/**
	 * Validates the data associated to this user.
	 *
	*/
	public static function validateStatic( $property, $value ) {
		return true;
	}

}