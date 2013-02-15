<?php

//namespace BootstraPHPed\classes\Persistence;


/**
 * A Model represents a row in the DB (only potentially, if it has just been constructed).
 * A Model can be:
 *  * fetched from the DB with load;
 *  * made persistent in the DB with save;
 *  * read with get;
 *  * checked for validation with validate;
 * A Model can be loaded only once, but it can be set as many times as you like.
 *
 * Here are some common uses for a Model:
 *  * read a row from the DB:		$m->load($id); $row = $m->get();
 *  * edit a row in the DB:			$m->load($id); $m->set($row); $m->save();
 *  * create a new row in the DB:	$m = new ConcreteModel(); $m->set($row); $m->save();
 *  * delete a row in the DB:		$m->load($id); $m->delete();
 * 
 * @author Alberto 'alb-i986' Scotto
 */
interface Model {
	
	/**
	 * @param $access_mode:
	 *           * 'r', for read access
	 *           * 'w', for write access
	 *           * 'rw' for read & write access
	 * @return an array of strings filled with the names of the accessable properties
	 */
	public static function getPropertiesNames($access_mode);

	/**
	 * @param $properties the names of the readable properties you are interested in
	 *                    (the allowed values are given by getPropertiesNames)
	 * @return the values of the properties you asked for
	 */
	public function get($properties);

	public function set($properties);

	/**
	 * Loads this model's data from the DB
	 * @param $id the ID of the row to be loaded
	 */
	public function load($id);


	public function save();

	public function delete();

	public function validate($properties);
	
	public static function validateStatic($property, $value);

	/**
	 * HTML+Bootstrap representation of this Model
	 */
	public function __toString();

}