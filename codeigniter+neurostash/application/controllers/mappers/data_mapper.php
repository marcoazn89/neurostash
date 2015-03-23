<?php
include(dirname(dirname(dirname(__FILE__))).'/models/table_data_gateway.php');
include_once(dirname(dirname(__FILE__)).'/core/parameters.php');

class Data_Mapper {
	
	private $gateway;
	private $mapped_obj;

	public function __construct(Entity $obj = null)
	{
		//parent::__construct();
		//$this->load->database();
		//$this->gateway = $this->load->model('table_data_gateway');
		//$gateway_factory = new Gateway_Factory(get_class($obj));
		$this->gateway = new Table_Data_Gateway(); //$gateway_factory->get_gateway();
		$this->mapped_obj = $obj;
	}

	//will be deprecated
	public function map(Entity $obj) {
		$this->mapped_obj = $obj;
	}

	public function read(Parameters $parameters = null) {
		return 	is_null($parameters) ? 
				$this->gateway->read($this->mapped_obj, new Parameters()) :
				$this->gateway->read($this->mapped_obj, $parameters);
	}

	public function create(Parameters $parameters = null) {
		return 	is_null($parameters) ? 
				$this->gateway->create($this->mapped_obj, new Parameters()) :
				$this->gateway->create($this->mapped_obj, $parameters);
	}

	public function update(Parameters $parameters = null) {
		return 	is_null($parameters) ? 
				$this->gateway->update($this->mapped_obj, new Parameters()) :
				$this->gateway->update($this->mapped_obj, $parameters);
	}

	public function delete() {
		return $this->gateway->delete($this->mapped_obj);
	}
}

/* End of file data_mapper.php */
/* Location: ./application/controllers/mappers/data_mapper.php */
