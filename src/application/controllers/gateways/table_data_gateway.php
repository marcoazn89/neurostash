<?php
include_once(__DIR__.'..\..\..\..\system\core\Model.php');
include_once(__DIR__.'..\..\class_factory.php');
include_once('query_builder.php');

class Table_Data_Gateway extends CI_Model {
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->load->database();
	}

	public function update(Entity $entity, $complete)
	{
		$entity_array = array(
			"{$entity}" =>	get_object_vars($entity),
			"other"		=>	NULL
			);

		if(empty($entity->active_relationship()) === FALSE)
		{
			foreach($entity->active_relationship() as $entity_name)
			{
				$type_of_relationship = $entity->type_of_relationship($entity_name);
				$ent = $entity->get_entity($entity_name, $type_of_relationship);

				if($type_of_relationship == 'has_one')
				{
					$entity_array["{$entity}"]["{$ent}_id"] = (int)$ent->id;
				}
				else
				{
					foreach($ent as $e)
					{
						$this->db
							->where("{$entity}_id", (int)$entity->id)
							->update("{$entity}_{$e}", array("{$e}_id"	=>	(int)$e->id));
					}
				}
			}
		}

		if($complete === FALSE)
		{
			foreach($entity as $attribute => $value)
			{
				if(is_null($value))
				{
					unset($entity_array["{$entity}"]["{$attribute}"]);
				}
			}

		}

		$result = $this->db
			->where('id', $entity->id)
			->update("{$entity}", $entity_array["{$entity}"]);

		return true;
	}
	
	public function delete(Entity $entity)
	{
		$this->db
			->where('id', $entity->id)
			->delete("{$entity}"); 
	}

	public function create(Entity $entity)
	{
		$entity_array = array(
			"{$entity}" =>	get_object_vars($entity),
			"other"		=>	NULL
			);

		$inserted_id = NULL;

		if( ! empty($entity->active_relationship()))
		{
			foreach($entity->active_relationship() as $entity_name)
			{
				$type_of_relationship = $entity->type_of_relationship($entity_name);
				$ent = $entity->get_entity($entity_name, $type_of_relationship);

				if($type_of_relationship == 'has_one')
				{
					$entity_array["{$entity}"]["{$ent}_id"] = (int)$ent->id;
				}
				else
				{
					foreach($ent as $e)
					{
						$entity_array["other"]["{$entity}_{$e}"][] = array(
						"{$entity}_id"	=>		&$inserted_id,
						"{$e}_id"		=>		(int)$e->id
						);
					}
				}
			}

			$this->db->insert("{$entity}", $entity_array["{$entity}"]);
			
			$inserted_id = $this->db->insert_id();

			if( ! is_null($entity_array["other"]))
			{
				foreach($entity_array["other"] as $table => $array_data)
				{
					foreach($array_data as $data)
					{
						$this->db->insert("{$table}", $data);
					}
				}
			}
		}
		else
		{
			$inserted_id = $this->db->insert("{$entity}", $entity);
		}

		return $inserted_id;
	}

	public function read(Entity $entity, $complete, $limit, $offset)
	{
		$sql =  array(
			"main" 	=> NULL,
			"other"	=> NULL
			);

		$sql["main"] = clone $this->db;
		$sql["main"]->select(Query_Builder::select_string($entity));
		$sql["main"]->from("{$entity}");	

		if(is_numeric($entity->id))
		{
			$sql["main"]->where("{$entity}.id", (int)$entity->id);
			
			if($complete === TRUE)
			{	
				if($entity->has_relationships() === TRUE)
				{
					foreach($entity->relationship() as $ent => $rel)
					{
						$class = new Class_Factory($ent);
						$class = $class->get_concrete_class();

						if($rel === 'has_one')
						{
							$sql["main"]->select(Query_Builder::select_string($class));
							$sql["main"]
								->join($ent,
									"{$entity}.{$ent}_id={$ent}.id");
						}
						else
						{
							$sql["other"][$ent] = clone $this->db;
							$sql["other"][$ent]->select(Query_Builder::select_string($class));
							$sql["other"][$ent]->from("{$entity}_{$ent}");
							$sql["other"][$ent]
								->join($ent, 
									"{$entity}_{$ent}.{$ent}_id={$ent}.id");
							$sql["other"][$ent]->where("{$entity}_{$ent}.{$entity}_id", (int)$entity->id);
						}
					}
				}
			}
		}
		else
		{
			$sql['main']->limit($limit, $offset);
			
			//should only be false. This is temporary
			//just to avoid the empty else statement
			//and a query that can break it like this
			//read/video?genre_name=sci&complete=true
			if($complete === FALSE || $complete === TRUE)
			{
				if($entity->has_active_attributes())
				{
					$sql["main"]->like(Query_Builder::like_array($entity));
					
					if($entity->has_active_relationships())
					{
						foreach($entity->active_relationship() as $entity_name)
						{
							$type_of_relationship = $entity->type_of_relationship($entity_name);
							$ent = $entity->get_entity($entity_name, $type_of_relationship);
							$ent = is_array($ent) ? $ent[0] : $ent;

							if(is_numeric($ent->id))
							{
								$sql["main"]->where("{$ent}.id", (int)$ent->id);	
							}
							else
							{
								$sql["main"]->like(Query_Builder::like_array($ent));
							}

							if($type_of_relationship == 'has_one')
							{
								$sql["main"]->join($ent,"{$entity}.{$ent}_id={$ent}.id");
							}
							else
							{
								$sql["main"]->join("{$entity}_{$ent}",
										"{$entity}_{$ent}.{$entity}_id={$entity}.id");
								$sql["main"]->join($ent,"{$entity}_{$ent}.{$ent}_id={$ent}.id");
							}
						}
					}
				}
			}
			//else
			//{
				//Currently unsupported
				//Search for complete results where id is null
			//}
		}

		$result = $sql["main"]->get()->result_array();

		$key = 0;

		foreach($result as $ent)
		{
			foreach($ent as $attribute => $value)
			{
				$att_val = explode('_', $attribute);

				$entity_name = $att_val[0];
				$entity_attribute = $att_val[1];

				if($entity_name !== "{$entity}")
				{
					$result[$key]["{$entity_name}"]["{$entity_attribute}"] = $value;
					unset($result[$key]["{$attribute}"]);
				}
				else
				{
					$result[$key]["{$entity_attribute}"] = $value;
					unset($result[$key]["{$attribute}"]);	
				}
			}

			$key++;
		}

		$key = 0;

		if( ! is_null($sql["other"]))
		{
			foreach($sql["other"] as $entity_name => $query)
			{
				$res = $query->get()->result_array();
				
				foreach($res as $ent)
				{
					foreach($ent as $attribute => $value)
					{
						$att_val = explode('_', $attribute);
						$entity_name = $att_val[0];
						$entity_attribute = $att_val[1];

						//$att = explode('_', $attribute)[1];

						$res[$key]["{$entity_attribute}"] = $value;
						unset($res[$key]["{$attribute}"]);	
						
					}

					$key++;
				}

				$key = 0;

				$result[0]["{$entity_name}"] = $res;
			}
		}

		return $result;
	}
}