<?php
class AttributeMismatchException extends InvalidArgumentException {
	
	public function __construct($attribute)
	{
		$this->message = "Unkown attribute: '$attribute'.";
	}
}

/* End of file attributemissmatchexception.php */
/* Location: ./application/controllers/exceptions/attributemissmatchexception.php */