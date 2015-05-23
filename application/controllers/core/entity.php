<?php
/**
 * Entity
 * 
 * @package 	Content-Server
 * @author 		Marco A. Chang <mchang@mvsusa.com>
 * @copyright 	[description]
 * @license 	[url] [description]
 * @link        (target, link)
 * @since 		1.2
 * @version 	1.2
 */

/**
 * 	Entity interface is used for:
 * 		- Type hinting: Only objects that implement Entity interface
 * 						can be passed to methods that require Entity
 * 						data type as a parameter.
 *
 * 		- Pre-defined	Used to identity system's entity properties.
 * 		  Constants:	To come soon...
 * 						
 * 		- Custom 		Constants can be used to define common attributes
 * 		  Constants:	that all domain objects implementing Entity
 * 						interface share.
 * 						
 */
include_once(dirname(__DIR__).'/traits/class_helpers.php');
include_once(dirname(__DIR__).'/traits/data_exporters.php');
include_once(dirname(__DIR__).'/traits/relationships.php');

abstract class Entity implements IteratorAggregate {

	use Data_Exporters;
	use Class_Helpers;
	use Relationships;

	public $id;
	private $visible_attributes;
	private $persisted_attributes;
	private $filled_values = false;

	//CRUD requirements
	public function has_read_requirements() {
		return false;
	}

	public function has_create_requirements() {
		return false;
	}

	public function has_update_requirements() {
		return false;
	}

	public function has_delete_requirements() {
		return false;
	}

	//Validation requirements
	public function has_validation_requirements() {
		return false;
	}

	//Field Requirements
	public function has_field_requirements() {
		return false;
	}

	public function active_attributes($boolean) {
		$this->filled_values = $boolean;
	}

	public function has_active_attributes()	{
		return $this->filled_values;
	}

	protected function persist_attributes(Array $attributes) {
		$this->persisted_attributes = $attributes;
	}

	public function persisted_attributes() {
		if(is_null($this->persisted_attributes)) {
			$attributes = get_class_vars($this);
			unset($attributes['has_one']);
			unset($attributes['has_many']);
			unset($attributes['visible_attributes']);
			unset($attributes['persisted_attributes']);
			unset($attributes['filled_values']);
			unset($attributes['relationships']);
			unset($attributes['object_graph']);
			unset($attributes['relationship_array']);
			unset($attributes['active_relationships']);
			unset($attributes['relationshipNames']);

			return array_keys($attributes);
		}
		
		return $this->persisted_attributes;
	}

	protected function set_visible_attributes(Array $attributes) {
		$this->visible_attributes = $attributes;
	}

	public function getIterator() {
        return new ArrayIterator(
        	is_null($this->visible_attributes) ? $this : $this->visible_attributes
        	);
    }

    public function visible_attributes() {
    	return $this->visible_attributes;
    }

	public function __toString() {
		return strtolower(get_class($this));
	}
}
