<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class Director extends Entity {
	
	public $id;
	public $firstname;
	public $lastname;
	public $sex;
	public $birthday;
	public $country;
}

/* End of file director.php */
/* Location: ./application/controllers/product.php */
