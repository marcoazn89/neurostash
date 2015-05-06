<?php
class Gateway_Factory {

	private $name;
	private $file_name;

	public function __construct($name)
	{
		$this->name = $name;
		$this->file_name = __DIR__.'/'.strtolower($name).'_gateway.php';

		if($this->_valid_class() === FALSE)
		{
			throw new Exception("The file {$this->file_name} does not exist", 1);
		}
	}

	public function get_gateway()
	{
		include_once($this->file_name);

		$gateway = "{$this->name}_Gateway";

		if( ! class_exists($gateway, FALSE))
		{
			throw new Exception("Class {$gateway} was not found in {$this->file_name}", 1);
		}

		return new $gateway;
	}

	public function _valid_class()
	{
		return file_exists($this->file_name) === TRUE ? TRUE : FALSE;
	}
}

/* End of file gateway_factory.php */
/* Location: ./application/controllers/gateways/gateway_factory.php */