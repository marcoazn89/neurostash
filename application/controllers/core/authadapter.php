<?php
include_once('authentication.php');
include_once('class_factory');

class AuthAdapter {
	
	private $auth;

	public function __construct($auth_class) {
		$class_factory = new Class_Factory($auth_class);
		$this->auth = $class_factory->get_concrete_class();
	}

	public function authenticate(Array $data) {
		return $this->auth->authenticate($data);
	}
}