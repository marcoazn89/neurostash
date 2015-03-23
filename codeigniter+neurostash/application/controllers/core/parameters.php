<?php

Class Parameters {
	
	/**
	 * Search for a exact match or anything similar
	 * @var boolean
	 */
	public $strict_search = true;

	/**
	 * Response format: json, xml, etc
	 * @var String
	 */
	public $response_format = 'json';

	/**
	 * Determines if there is a limit on ammount of data to be found
	 * @var int
	 */
	public $limit = null;

	/**
	 * Determines if the search should start from a particular offset
	 * @var int
	 */
	public $offset = 0;

	/**
	 * When false it will return data that belongs the collection being
	 * queried. Otherwise, it will return all the other collections 
	 * it relates to
	 * @var boolean
	 */
	public $complete = false;

	/**
	 * Works only when complete is TRUE. This defines how deep the read
	 * function will read. Depth increments by 1 everytime read goes
	 * further down the children of the children as illustrated below:
	 * 
	 * @example d0		d1		d2 			d3
	 * 			|_actor| 	   | 		   |
	 * 				   |_video_|_rating    |
	 * 						   |_language__|
	 * 						   |_category__|_subcategory
	 * 						   |_director__|_country
	 * @var int
	 */
	public $depth = 1;

	/**
	 * DEPRECATED
	 * This only applies for POST, PUT, and DELETE requests. When false
	 * it returns 1 for success and 0 for fail. When true it returns the
	 * new entity created
	 * @var boolean
	 */
	//public $fullResponse = false;

	public function getParameters() {
		return get_object_vars($this);
	}

	/**
	 * This is for the developers. It will throw the exception and kill the application.
	 * Do not wrap a try catch around this error! Fix it instead.
	 */
	public function __set($attribute, $value) {
		throw new Exception("Can't set unexistant property in class: Parameters", 1);
		
	}

	/**
	 * This is for the developers. It will throw the exception and kill the application.
	 * Do not wrap a try catch around this error! Fix it instead.
	 */
	public function __get($attribute) {
		throw new Exception("Can't get unexistant property in class: Parameters", 1);
		
	}
}
