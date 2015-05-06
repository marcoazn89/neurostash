<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class Legalstatus extends Entity {
	
	public $status;

	public function __construct() {
		$this->relates_to_one('actor');
	}
}

/* End of file product.php */
/* Location: ./application/controllers/product.php */
