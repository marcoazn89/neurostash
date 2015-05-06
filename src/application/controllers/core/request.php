<?php
include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/system/core/Input.php');
/**
 * This class is used to grab all requests and
 * return an array such as the following:
 *
 * ['request']		=	'post'
 * ['data']			=	array('username' => 'someuser')
 *
 */

class Request extends CI_Input {

	private $request_array = array(
		'type'		=>	NULL,
		'data'			=>	array());

	private $input;

	public function __construct($format)
	{
		/**
		 * When a Request concrete class is created, it automatically
		 * grabs the current request and makes the request array
		 */
		parent::__construct();

		$format = strtolower($format);

		$this->request_array['type'] = strtolower($this->server('REQUEST_METHOD'));
		
		switch($format) {
			case 'form':
				$this->getFormData();
				break;
			case 'json':
				$this->getJsonData();
				break;
			default:
				die("Unsupported Format {$format}");
		}
	}

	private function getFormData() {
		switch($this->request_array['type'])
		{
			case 'get':
				$this->request_array['data'] = $this->get(NULL, TRUE);
				break;
			case 'post':
				$this->request_array['data'] = $this->post(NULL, TRUE);
				break;
			default:
				die('Request type not supported');
		}
	}

	private function getJsonData() {
		$this->input = file_get_contents('php://input');
		
		switch($this->request_array['type'])
		{
			case 'get':
				$this->request_array['data'] = $this->specialGet();
				break;
			case 'post':
				$this->request_array['data'] = json_decode($this->input, true);
				break;
			case 'put':
			//BUG:JSON_DECODE makes invalid json null and results into update always true
				$this->request_array['data'] = json_decode($this->input, true);
				break;
			case 'delete':
				$this->request_array['data'] = json_decode($this->input, true);
				break;
			default:
				die('Request type not supported');
		}
	}

	private function specialGet() {
		$url_len = strlen($_SERVER['QUERY_STRING']);
		$json_len = strlen(json_decode($this->input, true));

		if($url_len <= 1 && $json_len > 1) {
			return json_decode($this->input, true);
		}
		elseif($url_len > 1 && $json_len > 1) {
			die("Please make sure your url does not contain any query");
		}
		else {
			return $this->get(NULL, TRUE);
		}
	}

	private function clean(Array $array) {
		foreach($array as $key => $value) {
			$array["{$key}"] = urldecode($value);
		}
	}

	public function __destruct() {
		return true;
	}

	public function get_request_type() {
		return $this->request_array['type'];
	}

	public function get_request_data() {
		return $this->is_empty() ? array() : $this->request_array['data'];
	}

	public function unsetData($data) {		
		unset($this->request_array['data'][$data]);
	}

	//Deprecated
	public function unset_input($data) {
		switch($this->request_array['type'])
		{
			case 'get':
				unset($_GET[$data]);
				break;
			case 'post':
				unset($_POST[$data]);
				break;
			case 'put':
				unset($_put[$data]);
				break;
			case 'delete':
				unset($_delete[$data]);
				break;
		}
	}

	public function is_empty() {
		return 	$this->request_array['data'] !== false
				&& count($this->request_array['data']) > 0 ?
				false : true;
	}
}
