<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class Product extends Entity {

	public $name;
	public $date;
	public $picture;
	public $reference;
	public $description;
	public $quantity;
	public $price;

	public function __construct() {
		$this->relates_to_one('department');
	}
}

/* End of file product.php */
/* Location: ./application/controllers/product.php */
