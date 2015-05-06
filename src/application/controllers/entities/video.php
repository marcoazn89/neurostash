<?php
include_once(dirname(dirname(__FILE__)).'/exceptions/attributemissmatchexception.php');
include_once(dirname(dirname(__FILE__)).'/core/entity.php');
include_once('product.php');

class Video extends Product {

	public $duration;

	public function __construct()
	{
		//$this->persist_attributes();
		$this->relates_to_one('category, director, rating');
		$this->relates_to_many('actor, genre, language');
	}
}
/* End of file video.php */
/* Location: ./application/controllers/video.php */
