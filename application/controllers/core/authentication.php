<?php
include_once('crud_controller.php');
include_once(dirname(dirname(__FILE__)).'/mappers/data_mapper.php');
include_once('parameters.php');
include_once('crud_service.php');

trait Authentication {

	public $entity = 'users';
	public $username_field = 'email';
	public $password_field = 'password';
	public $mapper;
	private $hash_algorithm = 'sha512';

	//public function Authentication($entity, $username_field = null, $password_field = null)
	public function setAuth($entity, $username_field = 'username', $password_field = 'password')//Array $args)
	{
		$this->username_field = $username_field;
		$this->password_field = $password_field;
		$this->entity = $entity;
	}

	public function create_hash(Array $data)
	{
		return hash($this->hash_algorithm, implode('', $data));
	}

	public function random_hash()
	{
		return hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
	}

	public function authenticate($username, $password) {
		/*$this->entity->{$this->username_field} = $username;
		$this->entity->active_attributes(true);

		//die($this->entity->email);
		//$this->entity->password_field = $password;

		$mapper = new Data_Mapper($this->entity);

		$this->entity_data = $mapper->read(false,1,0);*/
		$service = new CRUD_Service();
		$parameters = new Parameters();

		$parameters->response_format = 'array';

		$values = array(
			$this->username_field => $username
			);

		$this->entity_data = $service->read($this->entity, $values, $parameters);

		if(empty($this->entity_data)) {
			//wrong username
			//die("wrong username");
			return false;
		}

		//$user = $this->entity_data[0];
		$user = $this->entity_data[0];

		$password = $this->create_hash(array($password, $user['salt']));

		if($password !== $user[$this->password_field]) {
			//wrong password
			//die("wrong password");
			return false;
		}

		$this->load->library('session');
		$this->session->sess_table_name = 'sessions';
		$session_str = $this->create_hash(array(
			$_SERVER['REMOTE_ADDR'],
			$_SERVER['HTTP_USER_AGENT'],	
			$password
			));
		$this->token = $this->makeToken(array($_SERVER['REMOTE_ADDR'],$_SERVER['HTTP_USER_AGENT'],$password,time()));

		$session_arr = array(
			'username'	=>	$username,
			'entity'	=>	"{$this->entity}",
			'usr_field'	=>	"{$this->username_field}",
			'psw_field'	=>	"{$this->password_field}",
			'string'	=>	$session_str,
			'token'		=>  $this->token);

		$this->session->set_userdata('logged_in', $session_arr);
		
		return true;
	}

	public function makeToken(array $data) {
		return sha1(implode('', $data));
	} 

	public function killSession() {
		$this->session->sess_destroy();

		return true;
	}

	public function isInSession() {
		$this->load->library('session');
		
		$session_arr = $this->session->userdata('logged_in');

		if($session_arr === false) {
			//not logged in
			return false;
		}
		
		$service = new CRUD_Service();
		$parameters = new Parameters();
		
		$parameters->response_format = 'array';

		$values = array(
			$session_arr['usr_field'] => $session_arr['username']
			);

		$this->entity_data = $service->read($session_arr['entity'], $values, $parameters);

		if(empty($this->entity_data)) {
			//highjacked session???
			return false;
		}
		
		$user = $this->entity_data;

		$session_str = $this->create_hash(array(
			$this->session->userdata('ip_address'),
			$this->session->userdata('user_agent'),	
			$user[$session_arr['psw_field']]
			));

		if($session_str !== $session_arr['string']) {
			//user switched browser/device or highjacked session
			return false;
		}

		return true;
	}

	//deprecated for now
	private function checkbrute($user_id) {
    	// Get timestamp of current time 
	    $now = time();
	 
	    // All login attempts are counted from the past 2 hours. 
	    $valid_attempts = $now - (2 * 60 * 60);
	 
	    $result = 	$this->db->from('attempts')
	    			->where('user_id', $user_id)
	    			->where('time', $valid_attempts)
	    			->count_all_results();

        // If there have been more than 5 failed logins 
        if($result > 5)
        {
            return true;
        }
        else
        {
            return false;
        }	    
	}

	//public function get_auth_data
}
