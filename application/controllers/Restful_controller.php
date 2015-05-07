<?php
include_once('core/request.php');
include_once('core/parameters.php');
include_once('core/crud_service.php');
include_once('core/authentication.php');

class Restful_controller extends CI_Controller {

	use Authentication;

	private $param1;
	private $param2;
	private $param3;
	private $request;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Used to handle all incoming requests
	 * @param  String $entity
	 * @param  mixed $param1
	 * @param  mixed $param2
	 * @param  mixed $param3
	 * @return [type]         [description]
	 *
	 * Examples:
	 * GET:
	 * 		-	All:			/video
	 * 		-	By id:			/video/41
	 * 		-	By attribute:	/video/actor_name/Jennifer
	 * 		-	By JSON data:	/video
	 * 							{limit: 5, groupBy: length}
	 *
	 * POST:
	 * 		-	An entity:		/video
	 * 							{name: "Jolt", actor_id: [{1, 14, 3}]}
	 * 		-	A many-to-many:	/video/1/actor/5	(Add element to collection)
	 * 		-	More than one:	/video/1/actor
	 * 			many-to-many	{actor_id: [4, 16, 10, 12]}
	 * 	PUT:
	 * 		-	Attribute:		/video/23/name/Something
	 * 		-	Attributes:		/video/23
	 * 							{name: "A new name", length: 100}
	 * 	DELETE:
	 * 		-	Entity:			/video/2
	 * 		-	Entities:		/video
	 * 							{id: {1,2,3}}
	 * 		-	A many-to-many:	/video/2/actor/1
	 * 		-	More than one:	/video/2/actor
	 * 			many-to-many	{actor: [4, 16, 10, 12]}
	 */
	public function handler($entity, $param1 = null, $param2 = null, $param3 = null) {
		//$this->oauth();
		unset($_GET['access_token']);
		unset($_POST['access_token']);
		$this->param1 = $param1;
		$this->param2 = $param2;
		$this->param3 = $param3;

		$this->request = new Request('json');
		$parameters = new Parameters();
		$service = new CRUD_Service();
		$respose = null;

		if( ! $this->request->is_empty()) {
			$requestParams = array_intersect(
								array_keys($this->request->get_request_data()),
								array_keys($parameters->getParameters())
								);

			foreach($requestParams as $params) {
				$parameters->{$params} = $this->cast($this->request->get_request_data()[$params]);
				$this->request->unsetData($params);
			}
		}

		switch ($this->request->get_request_type()) {
			case 'get':
				$entityValues = $this->parseGetURI();
				$response = $service->read($entity, $entityValues, $parameters);
				break;
			case 'post':
				$entityValues = $this->parsePostURI();
				$response = $service->create($entity, $entityValues, $parameters);
				break;
			case 'put':
				$entityValues = $this->parsePutURI();
				$response = $service->update($entity, $entityValues, $parameters);
				break;
		}

		header("Content-Type: application/json");
		echo $response;
	}

	private function cast($value) {
		switch(strtolower($value)) {
			case 'true':
				return true;
				break;
			case 'false':
				return false;
				break;
			case 'null':
				return null;
				break;
			default:
				return (int)$value;
		}
	}

	private function parseGetURI() {

		$entityValues = $this->request->get_request_data();

		if( ! is_null($this->param1) && is_null($this->param2)) {
			$entityValues['id'] = (int)$this->param1;
		}
		elseif( ! is_null($this->param1) && ! is_null($this->param2)) {
			$entityValues[$this->param1] = urldecode($this->param2);
		}
		elseif(isset($entityValues['id'])) {
			$entityValues['id'] = (int)$entityValues['id'];
		}

		$this->request->__destruct();

		return $entityValues;
	}

	private function parsePostURI() {
		
		$entityValues = $this->request->get_request_data();
		
		if( ! is_null($this->param1) && ! is_null($this->param2) && ! is_null($this->param3)) {
			$entityValues['id'] = (int)$this->param1;
			$entityValues[strtolower($this->param2).'_id'] = (int)$this->param3;
		}
		elseif( ! is_null($this->param1) && ! is_null($this->param2)) {
			$entityValues['id'] = (int)$this->param1;
			$entityValues[strtolower($this->param2).'_id'] = 
			isset($entityValues[strtolower($this->param2).'_id']) ?
			$entityValues[strtolower($this->param2).'_id'] :
			die("Expecting ids for {$this->param2}, instead ids for a different entity were sent");
		}
		elseif(is_null($this->param1) && is_null($this->param2) && is_null($this->param3)) {
			//do nothing
		}
		else {
			die("Parameters were passed incorrectly. Please revise API.");
		}

		$this->request->__destruct();

		return $entityValues;
	}

	private function parsePutURI() {
		
		$entityValues = $this->request->get_request_data();
		
		if( ! is_null($this->param1) && ! is_null($this->param2) && ! is_null($this->param3)) {
			$entityValues['id'] = (int)$this->param1;
			$entityValues[strtolower($this->param2)] = $this->param3;
		}
		elseif( ! is_null($this->param1)) {
			$entityValues['id'] = (int)$this->param1;
		}
		else {
			die("Parameters were passed incorrectly. Please revise API.");
		}

		$this->request->__destruct();

		return $entityValues;
	}

	public function token() {
		$toBeAuth = array('user' => array('email', 'password'));

		$auth = false;

		$request = new Request('json');

		if($request->get_request_type() === 'post') {

			$data = $request->get_request_data();

			//if there is no request to login we might need to skip this loop
			if( ! empty($data)) {
				foreach($toBeAuth as $entity => $fields) {
					$this->setAuth($entity, $fields[0], $fields[1]);
					if($this->authenticate($data[$fields[0]],$data[$fields[1]])) {
						$auth = true;
						break;
					}
				}

				$request->unset_input($fields[0]);
				$request->unset_input($fields[1]);
				$request->__destruct();
			}
		}

		if($auth) {
			header("Content-Type: application/json");
			echo json_encode((object)array('access_token' => $this->token));
		}
		else {
			header("Content-Type: application/json");
			echo json_encode((object)array('error' => 'wrong credentials'));;
		}
	}

	public function oauth() {
		$this->load->library('session');
		$data = $this->session->userdata('logged_in');
		
		if( ! isset($_REQUEST['access_token'])) {
			header("Content-Type: application/json");
			echo json_encode((object)array('error' => 'access token missing in request'));
			die;
		}
		else {
			if($_REQUEST['access_token'] === $data['token']) {
				return;
			}
			else {
				header("Content-Type: application/json");
				echo json_encode((object)array('error' => 'invalid token'));
				die;
			}			
		}
	}

	public function profile() {
		$this->load->library('session');
		$data = $this->session->userdata('logged_in');
		
		if(is_null($data)) {
			header("Content-Type: application/json");
			echo json_encode((object)array('error' => 'No profile in session, please login'));
			die;
		}

		$service = new CRUD_Service();
		$parameters = new Parameters();
		$parameters->response_format = 'array';

		$values = array(
			$data['usr_field'] => $data['username']
			);

		$entity_data = $service->read($data['entity'], $values, $parameters);
		
		header("Content-Type: application/json");
		echo json_encode($entity_data[0]);
	}
}