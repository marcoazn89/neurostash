<?php
class Query_Builder {

	public static function select_string(Entity $entity)
	{
		$select_vals = array();

		foreach($entity->persisted_attributes() as $attribute)
		{
			$select_vals[] = "{$entity}.{$attribute} as {$entity}_{$attribute}";
		}

		return implode(', ', $select_vals);
	}

	public static function where_array(Entity $entity)
	{
		$where_array = array();

		foreach($entity as $attribute => $value)
		{
			if( ! is_null($value))
			{
				$where_array["{$entity}.{$attribute}"] = $value;
			}
		}

		return $where_array;
	}

	public static function like_array(Entity $entity)
	{
		$like_array = array();

		foreach($entity as $attribute => $value)
		{
			if( ! is_null($value) && $attribute != "id")
			{
				$like_array["{$entity}.{$attribute}"] = $value;
			}
		}
		
		return $like_array;
	}
}
/* End of file query_builder.php */
/* Location: ./application/controllers/gateways/query_builder.php */