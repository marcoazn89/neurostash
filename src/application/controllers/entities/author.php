<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class Author extends Entity {
	
	public $id;
	public $firstname;
	public $lastname;
	public $sex;
	public $birthday;
	public $country;
}

/* End of file author.php */
/* Location: ./application/controllers/author.php */
