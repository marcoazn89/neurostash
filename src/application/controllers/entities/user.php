<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');

class User extends Entity {

	public $name;
	public $lastname;
	public $email;
	public $password;
	public $dni;
	public $age;
	public $sex;
	public $salt;

	//@override
	public function has_create_requirements() {
		$this->set_salt();
		$this->set_password($this->password, $this->salt);

		return true;
	}

	//Setters
	private function set_password($password, $salt) {
		$this->password = hash('sha512', $password.$salt);
	}

	private function set_salt()
	{
		$this->salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
	}
}
