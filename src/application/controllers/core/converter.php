<?php
class Converter {

	private $data;

	public function __construct($data) {
		$this->data = $data;
	}

	public function to_object()
	{
		//not yet supported
	}

	public function to_serialized()
	{
		//not yet supported
	}

	public function to_array()
	{
		return $this->data;
	}

	public function to_json() {
		return json_encode($this->data);
	}

	public function to_xml()
	{
		//not yet supported
	}
}	
/* End of file converter.php */
/* Location: ./application/*models|controllers/converter.php/ */
