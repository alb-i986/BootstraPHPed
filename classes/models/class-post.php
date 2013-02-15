<?php
/**
 *
 * @author Alberto 'alb-i986' Scotto
 */

//namespace BootstraPHPed\classes\Persistence;


class Post extends Element {

	private 
			$id,
			$title,
			$content,
			$author,
			$published_on
	;

	/**
	 * By default, it constructs a non-authenticated user, aka 'guest' (guests have role 1).
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Loads this user's data from DB
	 * @param $id the ID of the user to be loaded
	 */
	public function load( $id ) {
		if( empty($id) )
			throw new InvalidArgumentException('Mandatory argument is empty: id');

		$post_row = $this->dao->getPost( $id );
		if( empty($post_row) )
			throw new InvalidArgumentException('Invalid argument: the specified id is not associated to any existent posts');
		return $this->set( $post_row );
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

		$this->dao->insertPost(
			$this->title,
			$this->content,
			$this->author
		);
		return true;
	}


	/**
	 * Precond: this user is authenticated
	 */
	public function getId() {
		if( $this->role == self::ROLE_GUEST )
			throw new BadMethodCallException( 'Requested property is not defined: currently, user is a guest' );
		return $this->id;
	}

	/**
	 * Precond: this user is authenticated
	 */
	public function getTitle() {
		if( $this->role == self::ROLE_GUEST )
			throw new BadMethodCallException( 'Requested property is not defined: currently, user is a guest' );
			
		return $this->nickname;
	}


	public function __toString() {
		return "User #".$this->id.":\nemail: ".$this->email."\nnickname: ".$this->nickname."\nrole: ".$this->role."\nteam: ".$this->team."\n";
	}




	/**
	 * Validates the data associated to this user.
	 *
	*/
	public static function validateStatic( $property, $val ) {
		if ( ! property_exists( __CLASS__ , $property ) )
			throw new InvalidArgumentException('Unknown property of the class ' . __CLASS__ .': ' . $property);

		$ok = false;
		switch( $property ) {
			case 'id':
				$ok = preg_match('/^[0-9]+$/', $val);
				break;
			case 'title':
				$ok = preg_match("/^[!_'àèìòù\-@&A-Za-z0-9]+$/", $val);
				break;
			case 'content':
				$ok = preg_match("/^[!_'àèìòù\-@&A-Za-z0-9]+$/", $val);
				break;
			case 'author':
				$ok = preg_match("/^[!_'àèìòù\-@&A-Za-z0-9]+$/", $val);
				break;
			case 'published_on':
				$ok = preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $val);
				break;
			default:
				$ok = true; // useful for auto-validating properties which do not need validation
				break;
		}
		return $ok;
	}

}