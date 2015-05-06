<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class Department extends Entity{

	use Class_Helpers;
	use Data_Exporters;
	use Relationships;

	public $name;

	public function __construct() {
		$this->add_dependants('category');
	}
}

/* End of file department.php */
/* Location: ./application/controllers/department.php */
