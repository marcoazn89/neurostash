<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');
class Category extends Entity {

	public $name;

	public function __construct()
	{
		$this->relates_to_one('department');
	}
}

/* End of file category.php */
/* Location: ./application/controllers/category.php */
