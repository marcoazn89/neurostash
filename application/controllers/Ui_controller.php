<?php
include_once('authentication.php');
include_once('request.php');
include_once('dataHandler.php');

class Ui_controller extends CI_Controller {

	use Authentication;
	use DataHandler;

	private $request;
	private $data;
	private $toBeAuth;

	public function __construct() {
		parent::__construct();
	}

	private function isAuthReq($view) {
		
		include(dirname(dirname(__FILE__)).'/config/authentication.php');

		if(is_null($auth[$view])) {
			return false;
		}

		$this->toBeAuth = $auth[$view];
		
		return true;
	}

	public function loadPage($page, $uri = null) {
		$content['content'] = null;

		if( ! $this->canView($page)) {
			return $this->load->view('login');
		}

		//$pageHandler = new DataHandler($page);

		/*if( ! is_null($uri)) {
			//use param to fetch data
		}*/

		$this->handleData($page);

		if($this->hasRequirements()) {
			//die("has requirements");
			$this->work();
			//get response data
		}

		return $this->load->view($page, $content);

		//work

		//do stuff
		//return $this->load->view($page, $content);
	}

	private function canView($view) {

		//Check if authentication is required
		if($this->isAuthReq($view)) {
			//Check if there is a session for the client
			if( ! $this->isInSession()) {
				//Check if the client can login
				if( ! $this->login()) {
					return false;
				}
			}
		}

		return true;
	}

	private function login() {

		$auth = false;

		$request = new Request('form');

		if($request->get_request_type() === 'post') {
			$data = $request->get_request_data();
			//if there is no request to login we might need to skip this loop
			if( ! empty($data)) {
				foreach($this->toBeAuth as $entity => $fields) {
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

		return $auth;
	}

	public function info($entity)
	{
		$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();

		$data['data'] = $obj;

		$this->load->view('info', $data);
	}

}

/* End of file view_controller.php */
/* Location: ./application/controllers/view_controller.php */
