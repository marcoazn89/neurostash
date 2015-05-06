<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class Actor extends Entity {
	
	public $id;
	public $firstName;
	public $lastname;
	public $sex;
	public $birthday;
	public $country;

	public function __construct() {
		$this->relates_to_many('video');
		$this->name_relationship('video', 'video_actor');
		$this->add_dependants('legalstatus');
	}
}

/* End of file product.php */
/* Location: ./application/controllers/product.php */
