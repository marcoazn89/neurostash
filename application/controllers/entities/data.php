<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/entity.php');

class Data extends Entity {
	
	public $date;
	public $pas;
	public $pad;
	public $pulse;

	public function __construct()
	{
		$this->relates_to_one('user');
	}
}

/* End of file user.php */
/* Location: ./application/controllers/product.php */
