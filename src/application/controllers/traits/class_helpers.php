<?php
trait Class_Helpers {
	
	public function get($attribute)
	{
		if( ! property_exists($this, $attribute))
		{
			throw new AttributeMismatchException($attribute);
		}
		
		return $this->$attribute;
	}

	public function set($attribute, $value)
	{
		if( ! property_exists($this, $attribute))
		{
			if(get_parent_class($this) === FALSE)
			{
				throw new AttributeMismatchException($attribute);
			}
		}
		
		$this->$attribute = $value;
	}

	public function set_force($attribute, $value)
	{
		$this->$attribute = $value;
	}

	public function get_all_attributes()
	{
		return array_keys(get_class_vars(__CLASS__));
	}

	public function get_class_attributes()
	{
		return array_diff($this->get_all_attributes(), $this->get_relationship_attributes());
	}
}

/* End of file class_helpers.php */
/* Location: ./application/controllers/traits/class_helpers.php */