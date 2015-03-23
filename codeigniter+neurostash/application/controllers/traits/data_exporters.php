<?php
trait Data_Exporters {
	
	

	public function to_Array()
	{
		return get_object_vars($this);//array_diff_assoc(get_object_vars($this), $this->get_relationship_Array());
	}
	
	public function to_json()
	{
		return JSON_encode($this->to_Array());
	}
	
	public function to_XML(){/* Needs Implementation */}
	
	public function valid_attributes_to_Array()
	{
		$valid_attributes;
		
		foreach($this->to_Array() as $attribute => $value)
		{
			if( ! is_null($value))
			{
				if(is_array($value) && count($value) < 1)
				{
					continue;	
				}

				$valid_attributes[$attribute] = $value;
			}
		}

		return empty($valid_attributes) ? null : $valid_attributes;
	}

	public function valid_attributes_to_JSON()
	{
		return JSON_encode($this->valid_attributes_to_Array());
	}

	public function valid_attributes_to_XML(){/* Needs Implementation */}
}

/* End of file data_exporters.php */
/* Location: ./application/controllers/traits/data_exporters.php */