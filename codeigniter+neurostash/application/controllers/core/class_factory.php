<?php
class Class_Factory {

	private $name;
	private $file_name;

	public function __construct($name)
	{
		$this->name = $name;
		$this->file_name = dirname(dirname(__FILE__)).'/entities/'.strtolower($name).'.php';

		if($this->_valid_class() === FALSE)
		{
			throw new Exception("The file {$this->file_name} does not exist", 1);
		}
	}

	public function get_concrete_class()
	{
		include_once($this->file_name);
		
		$class = $this->name;
		
		if( ! class_exists($class, FALSE))
		{
			throw new Exception("Class {$class} was not found in {$this->file_name}", 1);
		}	
		return new $class;
	}

	private function _valid_class()
	{
		return file_exists($this->file_name) === TRUE ? TRUE : FALSE;
	}
}

/* End of file class_factory.php */
/* Location: ./application/controllers/class_factory.php */
