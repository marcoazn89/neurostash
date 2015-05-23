<?php
include_once('request.php');
include_once('mappers/data_mapper.php');
include_once('class_factory.php');
include_once('crud_service.php');
include_once('parameters.php');

/**
 * DataHandler
 * Class used to handle all the data manipulation between
 * the client and the database.
 * It looks at config/data.php and figures how to fulfill
 * that data.
 */
trait DataHandler {
	public $uri;
	public $inputType;
	public $requirements;
	private $mapperp;
	private $requestData;
	private $service;

	public function handleData($uri) {
		//$this->load->library('session');
		//			die(var_dump($this->session->userdata('entity')));
		//die("handling Data");
		include(dirname(__DIR__).'/config/data.json');
		$this->requirements = $data;
		$this->uri = $uri;

		$request = new Request('form');
		$inputType = $request->get_request_type();
		$this->requestData = $request->get_request_data();

		$this->service = new CRUD_Service();
	}

	public function hasRequirements() {
		return isset($this->requirements[$this->uri]);
	}

	public function getClientRequirements() {
		return $this->requirements[$this->uri];
	}

	public function scanPost() {
		return 	isset($this->getClientRequirements()['post']) ? 
				$this->getClientRequirements()['post']	:
				false;
	}

	public function scanGet() {
		return 	isset($this->getClientRequirements()['get'])	?
				$this->getClientRequirements()['get']	:
				false;
	}

	public function postData() {
		$result = array();
		$data = $this->scanPost();

		if( ! $data) {
			return false;
		}

		foreach($data as $entity => $requirements) {
			//die(var_dump($requirements));
			if( ! isset($requirements['source']) ||
				(isset($this->requestData['source']) && $requirements['source'] == $this->requestData['source'])) {
			
				//set entity values from parameters sent
				$entityValues = $this->prepareFields($requirements);

				$entityValues = $this->fillFirst($requirements, $entityValues);
				
				$result[$entity] = $this->service->create($entity, $entityValues, new Parameters());
			}
		}

		return $result;
	}

	private function prepareFields($array) {
		$entityValues = array();

		if( ! isset($array['fields'])) {
			throw new Exception("Fields were not set in config/data.php", 1);
		}

		$fields = $array['fields'];

		if($array['mapping']) {
			//mapping was set to true
		}
		else {
			foreach($fields as $field) {
				if( ! isset($this->requestData[$field])) {
					throw new Exception("Field from request cannot be found in model 
						$entity defined in config/data.php", 1);
				}

				$entityValues["{$field}"] = $this->requestData[$field];
			}
		}

		return $entityValues;
	}

	private function fillFirst($array, $values) {

		if( ! isset($array['fillFirst'])) {
			return false;
		}
		
		$data = $array['fillFirst'];

		foreach($data as $dt => $params) {
			$entity_field = explode('_', $dt);
			$entity = $entity_field[0];
			$field = $entity_field[1];
			$entity_field = $dt;

			$entity_vals = $this->setFillData($params);

			$parameters = new Parameters();

			$parameters->response_format = 'array';

			$result = $this->service->read($entity, $entity_vals, $parameters);

			$values["{$entity_field}"] = $result["{$field}"];
		}

		return $values;
	}

	private function setFillData($data) {
		$values = array();

		foreach($data as $field => $value) {
			switch($value) {
				case 'session':
					$this->load->library('session');
					//die(var_dump($this->session->userdata('username')));
					$values["{$field}"] = $this->session->userdata('logged_in')['username'];
					break;
				case 'uri':
				//uri is passed from view_controller
					//$entity->{"$field"} = $uri;
					break;
				default:
				//use parameter from request object
					$values["{$field}"] = $this->requestData[$value];
			}
		}

		return $values;
	}



	public function getData() {
		$data = $this->scanGet();

		if( ! $data) {
			return false;
		}

		foreach($data as $entity => $requirements) {
			if(	! isset($requirements['source']) ||
				$requirements['source'] == $this->requestData['source']) {
				//not using mapping variable yet
				$class_factory = new Class_Factory($entity);
				$entity = $class_factory->get_concrete_class();

				
			}
		}
	}

	public function searchBy($array, Entity $entity) {
		
	}

	public function work() {
		$this->postData();
		//$this->getData();
	}
}
