<?php
include_once(__DIR__.'.../../../system/core/Model.php');
include_once(dirname(dirname(__FILE__)).'/controllers/core/class_factory.php');
include_once('query_builder.php');

class Table_Data_Gateway extends CI_Model {
	private $table_tracker = array();
	private $row_tracker = array();
	private $queries = array();

	public function __construct() {
		parent::__construct();

		$this->load->database();
	}
	public function update(Entity $entity, Parameters $parameters) {
		$entity->has_create_requirements();
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
					die("Can't update a one-to-many replationship. Please use DELETE protocol.");
					foreach($ent as $e)
					{
						$this->db
							->where("{$entity}_id", (int)$entity->id)
							->update("{$entity}_{$e}", array("{$e}_id"	=>	(int)$e->id));
					}
				}
			}
		}
		if($parameters->complete === FALSE)
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
		return $result;
	}

	public function delete(Entity $entity) {
		try {
			$this->db
			->where('id', $entity->id)
			->delete("{$entity}");
		} catch(Exception $e) {
			die("Unable to delete record");
		}

		return (object)array("success" => 1);
	}

	public function create(Entity $entity, Parameters $parameters) {
		$status = false;
		$entity->has_create_requirements();

		$entity_array = array(
			"{$entity}" =>	get_object_vars($entity),
			"other"		=>	NULL
			);
		$inserted_id = $entity->id;
		//If true, then there are multiple elements being inserted
		$add = is_null($inserted_id) ? false : true;
		$entity_result = array();

		if( ! empty($entity->active_relationship())) {
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
						$entity_array["other"][$entity->get_relationship_name("{$e}")][] = array(
						"{$entity}_id"	=>		&$inserted_id,
						"{$e}_id"		=>		(int)$e->id
						);
					}
				}
			}
			if(is_null($inserted_id)) {
				$status = $this->db->insert("{$entity}", $entity_array["{$entity}"]);
				$inserted_id = $this->db->insert_id();
			}
			if( ! is_null($entity_array["other"]))
			{
				foreach($entity_array["other"] as $table => $array_data)
				{
					$entity_result["success"]["{$entity_name}"] = array();
					$entity_result["failed"]["{$entity_name}"] = array();
					//$entity_result = array();
					foreach($array_data as $data)
					{
						$status = $this->db->insert("{$table}", $data);

						if($status) {
							array_push($entity_result["success"]["{$entity_name}"], $data["{$entity_name}_id"]);
						}
						else {
							array_push($entity_result["failed"]["{$entity_name}"], $data["{$entity_name}_id"]);
						}
					}
				}
			}
		}
		else
		{
			$status = $this->db->insert("{$entity}", $entity);
			$inserted_id = $this->db->insert_id();
		}
		if($add) {
			return array(	"status" => count($entity_result["failed"]) == 0 ? (int)$status : 0,
							"success" => $entity_result["success"],
							"fail" => $entity_result["failed"]
						);
		}
		else {
			if( ! $status) {
				return array("status" => (int)$status);
			}
			else {
				return array("status" => (int)$status, "{$entity}" => array("id" => $inserted_id));
			}
		}
		/*if($parameters->fullResponse) {
			if($add) {
				return array(	"status" => (int)$status,
								"success" => $entity_result["success"],
								"fail" => $entity_result["failed"]
							);
			}
			else {
				$entity->id = $inserted_id;
				//return array("message" => (int)$status, "{$entity}" => get_object_vars($entity));
				return array("status" => (int)$status, "{$entity}" => array("id" => $inserted_id));
			}
		}
		else {
			if($add) {
				return array(	"status" => (int)$status,
								"success" => $entity_result["success"],
								"fail" => $entity_result["failed"]
							);
			}
			else {
				if( ! $status) {
					return array("status" => (int)$status);
				}
				else {
					return array("status" => (int)$status, "{$entity}" => array("id" => $inserted_id));
				}
			}
		}*/
	}
	public function read(Entity $entity, Parameters $parameters) {
		$this->track_table("{$entity}");

		$this->db->from("{$entity}");
		$this->relationship_handler($entity, $parameters);
		//die(json_encode($this->table_tracker));
		//$this->db->get();
		$this->db->order_by("{$entity}.id",'asc');
		$db_results = $this->db->get()->result_array();
		//die(var_dump($db_results));
		//die(var_dump($this->result_extractor($db_results)));
		//return $this->result_extractor($db_results, "{$entity}");
		$result = array();
		$this->recursive_extractor($db_results, "{$entity}", $result);
		//die(var_dump($result));
		if(is_null($entity->id) && ! isset($result[0]) && count($result) > 0) {
			$r = array();
			array_push($r, $result);
			return $r;
		}
		elseif(! is_null($entity->id) && count($result) < 1) {
			return (object)null;
		}

		return $result;
	}
	/**
	 * Extract the results from a Query
	 * @param  Array  $data    An array containing query results
	 * @param  String $entity  A string to identify the current entity that is being extracted
	 * @param  Array  &$result A placeholder to put the results
	 * @return void
	 */
	private function recursive_extractor(Array $data, $entity, &$result) {
		//For the given data set $data, get unique rows by $entity id
		$segments = array_keys(array_unique(array_column($data, "{$entity}_id")));
		//die(var_dump(count($segments)));
		if($this->table_tracker[$entity]['parent'] == '' && count($segments) == 0) {
			return;
		}
		if(count($segments) == 1 && is_null($data[$segments[0]]["{$entity}_id"])) {
			return;
		}
		//If the number of unique rows is equal to one and the entity have no children
		if(count($segments) == 1 && count($this->table_tracker[$entity]['children']) < 1) {
			$original = array_intersect_key($data[$segments[0]], array_flip($this->table_tracker[$entity]['keys']));
			//die(json_encode($this->table_tracker[$entity]));
			if(is_array($result) && $this->table_tracker[$entity]['parent'] != '') {
				array_push($result, array_combine($this->table_tracker[$entity]['realkeys'], $original));
			}
			else {
				$result = array_combine($this->table_tracker[$entity]['realkeys'], $original);
			}
			//$result = array_combine($this->table_tracker[$entity]['realkeys'], $original);
		}//If the number of unique rows is equal to one and the entity have one or more children
		elseif(count($segments) == 1 && count($this->table_tracker[$entity]['children']) >= 1) {
			$original = array_intersect_key($data[$segments[0]], array_flip($this->table_tracker[$entity]['keys']));

			if(is_array($result) && $this->table_tracker[$entity]['parent'] != '') {
				array_push($result, array_combine($this->table_tracker[$entity]['realkeys'], $original));
				$r = &$result[0];
			}
			else {
				$result = array_combine($this->table_tracker[$entity]['realkeys'], $original);
				$r = &$result;
			}

			//figure out here if the children is a one to one or one to many
			//so that $result can be an array or non array
			//based on that do push array or not

			foreach($this->table_tracker[$entity]['children'] as $children => $type) {
				if($type == 'has_one') {
					$r[$children] = null;
				}
				else {
					$r[$children] = array();	
				}

				$this->recursive_extractor($data, $children, $r[$children]);
			}
		}//If the nuber of unique rows is greater than one
		else {
			for($i = 0; $i < count($segments); $i++) {
				$original = array_intersect_key($data[$segments[$i]], array_flip($this->table_tracker[$entity]['keys']));
				if(is_array($result)) {
					array_push($result, array_combine($this->table_tracker[$entity]['realkeys'], $original));
				}
				else {
					$result = array_combine($this->table_tracker[$entity]['realkeys'], $original);
				}
				//array_push($result, array_combine($this->table_tracker[$entity]['realkeys'], $original));
				if(count($this->table_tracker[$entity]['children']) >= 1) {
					foreach($this->table_tracker[$entity]['children'] as $children => $type) {
						$subset = $this->data_by_range($data, $segments[$i], $segments[count($segments)-1] === $segments[$i] ? count($data)-1 : $segments[$i+1] - 1);
						//$result[count($result)-1][$children] = array();
						if($type == 'has_one') {
							$result[count($result)-1][$children] = null;
						}
						else {
							$result[count($result)-1][$children] = array();
						}
						$this->recursive_extractor($subset, $children, $result[count($result)-1][$children]);
					}
				}
			}
		}
	}
	private function data_by_range(Array $data, $start, $end) {
		$new = array();
		for($i = $start; $i <= $end; $i++) {
			array_push($new, $data[$i]);
		}
		return $new;
	}
	//@deprecated
	private function result_extractor(Array $db_result, $root) {
		$result = array();
		$pointers = array();
		$temp = array();
		$x = 0;
		$segments = array_keys(array_unique(array_column($db_result, "{$root}_id")));
		foreach($db_result as $row) {
			$x++;
			foreach($this->table_tracker as $entity => $details) {
				if($entity == $root) {
					if( ! isset($temp[$entity]["id"])) {
						$original = array_intersect_key($row, array_flip($details['keys']));
						$temp[$entity] = array_combine($details['realkeys'], $original);
						$pointers[$entity] = &$temp[$entity];
					}
				}
				else {
					if(isset($pointers[$entity])) {
						if(isset($pointers[$entity][0])) {
							if(! isset($pointers[$details['parent']][0])) {
								if(array_search($row["{$entity}_id"], array_column($pointers[$entity], "id")) == false) {
									$original = array_intersect_key($row, array_flip($details['keys']));
									array_push($pointers[$entity], array_combine($details['realkeys'], $original));
									$pointers[$entity] = &$pointers[$entity];
								}
							}
							else {
								if(array_search($row["{$entity}_id"], array_column($pointers[$details['parent']][$x-1][$entity], "id")) == false) {
									$original = array_intersect_key($row, array_flip($details['keys']));
									array_push($pointers[$details['parent']][$x-1][$entity], array_combine($details['realkeys'], $original));
									$pointers[$details['parent']][$x-1][$entity] = &$pointers[$details['parent']][$x-1][$entity];
								}
							}
						}
						else {
							if($pointers[$entity]["id"] != $row["{$entity}_id"] && ! isset($pointers[$details['parent']][0])) {
								$prev = $pointers[$entity];
								$pointers[$entity] = array();	
								array_push($pointers[$entity], $prev);
								$original = array_intersect_key($row, array_flip($details['keys']));
								array_push($pointers[$entity], array_combine($details['realkeys'], $original));
								$pointers[$entity] = &$pointers[$entity];
							}
							elseif($pointers[$entity]["id"] != $row["{$entity}_id"] && isset($pointers[$details['parent']][0])) {
								$original = array_intersect_key($row, array_flip($details['keys']));
								$pointers[$details['parent']][$x-1] = &$pointers[$entity];
							}
						}
					}
					else {
						$original = array_intersect_key($row, array_flip($details['keys']));
						$pointers[$details['parent']][$entity] = array_combine($details['realkeys'], $original);
						$pointers[$entity] = &$pointers[$details['parent']][$entity];
					}
				}
			}
			if(in_array($x, $segments) || $x == count($db_result)) {
				if(count($segments) > 1) {
					array_push($result, $temp[$root]);
					$pointers = array();
					$temp = array();
				}
				else {
					$result = $temp[$root];
					$pointers = array();
					$temp = array();
				}
			}
		}
		return $result;
	}
	private function getKeys(Entity $entity) {
		$this->table_tracker["{$entity}"]['keys'] = array();
		foreach($entity->persisted_attributes() as $attribute) {
			$this->table_tracker["{$entity}"]['keys'][] = "{$entity}_{$attribute}";
			$this->table_tracker["{$entity}"]['realkeys'][] = "{$attribute}";
		}
	}
	private function relationship_handler(Entity $entity, Parameters $parameters) {
		$this->queries["{$entity}"]['db'] = clone $this->db;
		$depth = $this->get_depth("{$entity}");
		//Generate select satement for the given entity
		$select_str = Query_Builder::select_string($entity);
		$this->db->select($select_str);
		//$this->table_tracker["{$entity}"]['keys'] = "{$select_str}";
		$this->getKeys($entity);
		//Check if there are active attributes for the given entity
		if($entity->has_active_attributes()) {
			//If the id is set, don't bother checking other attributes
			if(is_numeric($entity->id)) {
				$this->db->where("{$entity}.id", (int)$entity->id);
			}
			else {
				//Use where or like depending on strict_search
				$this->generate_conditions($entity, $parameters);
			}
		}
		//Check if the given entity has relationships
		if($entity->has_relationships()) {
			//If complete search is disabled, the id is not set and  it has active relationships
			if( ! $parameters->complete &&  ! is_numeric($entity->id) && $entity->has_active_relationships()) {

				foreach($entity->active_relationship() as $entity_name) {
					$type_of_relationship = $entity->type_of_relationship($entity_name);
					$ent = $entity->get_entity($entity_name, $type_of_relationship)[0];

					$this->generate_conditions($ent, $parameters);
					$this->generate_joins($type_of_relationship, $entity, $ent);
				}
			}
			elseif($parameters->complete && $depth + 1 <= $parameters->depth) {
				//is returning has_one, etc
				foreach($entity->relationship() as $entity_name => $type_of_relationship) {

					$ent = $entity->get_entity($entity_name, $type_of_relationship);
					if($type_of_relationship === 'has_many' && ! empty($ent)) {
						$ent = $ent[0];
					}
					if(empty($ent)) {
						$class = new Class_Factory($entity_name);
						$ent = $class->get_concrete_class();
					}
					if($this->track_table("{$ent}", $depth+1, $entity)) {
						$this->generate_joins($type_of_relationship, $entity, $ent);
						//recursive step
						$this->relationship_handler($ent, $parameters, $depth);
					}
				}
			}
		}
	}

	private function relationship_handlerr(Entity $entity, Parameters $parameters) {
		$depth = $this->get_depth("{$entity}");
		//Generate select satement for the given entity
		$select_str = Query_Builder::select_string($entity);
		$this->db->select($select_str);
		//$this->table_tracker["{$entity}"]['keys'] = "{$select_str}";
		$this->getKeys($entity);
		//Check if there are active attributes for the given entity
		if($entity->has_active_attributes()) {
			//If the id is set, don't bother checking other attributes
			if(is_numeric($entity->id)) {
				$this->db->where("{$entity}.id", (int)$entity->id);
			}
			else {
				//Use where or like depending on strict_search
				$this->generate_conditions($entity, $parameters);
			}
		}
		//Check if the given entity has relationships
		if($entity->has_relationships()) {
			//If complete search is disabled, the id is not set and  it has active relationships
			if( ! $parameters->complete &&  ! is_numeric($entity->id) && $entity->has_active_relationships()) {

				foreach($entity->active_relationship() as $entity_name) {
					$type_of_relationship = $entity->type_of_relationship($entity_name);
					$ent = $entity->get_entity($entity_name, $type_of_relationship)[0];

					$this->generate_conditions($ent, $parameters);
					$this->generate_joins($type_of_relationship, $entity, $ent);
				}
			}
			elseif($parameters->complete && $depth + 1 <= $parameters->depth) {
				//is returning has_one, etc
				foreach($entity->relationship() as $entity_name => $type_of_relationship) {

					$ent = $entity->get_entity($entity_name, $type_of_relationship);
					if($type_of_relationship === 'has_many' && ! empty($ent)) {
						$ent = $ent[0];
					}
					if(empty($ent)) {
						$class = new Class_Factory($entity_name);
						$ent = $class->get_concrete_class();
					}
					if($this->track_table("{$ent}", $depth+1, $entity)) {
						$this->generate_joins($type_of_relationship, $entity, $ent);
						//recursive step
						$this->relationship_handler($ent, $parameters, $depth);
					}
				}
			}
		}
	}
	private function generate_joins($relationship, $entity1, $entity2) {
		switch($relationship) {
			case 'has_one':
				$this->db->join($entity2, "{$entity1}.{$entity2}_id={$entity2}.id", 'left');
				break;
			case 'has_many':
				$tb = $entity1->get_relationship_name("{$entity2}");
				$this->db->join($tb, "{$entity1}.id={$tb}.{$entity1}_id", 'left');
				$this->db->join($entity2, "{$tb}.{$entity2}_id={$entity2}.id", 'left');
				break;
			case 'has_dependants':
				$this->db->join($entity2, "{$entity1}.id={$entity2}.{$entity1}_id", 'left');
				break;
		}
	}

	private function generate_conditions($entity, $parameters) {
		if($parameters->strict_search) {
			$this->db->where(Query_Builder::where_array($entity));
		}
		else {
			$this->db->like(Query_Builder::like_array($entity));
		}
	}

	private function track_table($table, $depth = 0, Entity $parent = null) {
		if(array_key_exists($table, $this->table_tracker)) {
			return false;
		}
		else {
			$this->table_tracker[$table]['depth'] = $depth;
			$this->table_tracker[$table]['parent'] = "{$parent}";
			$this->table_tracker["{$table}"]['children'] = array();

			if( ! is_null($parent)) {
				//$this->table_tracker["{$parent}"]['children'][$table] = null;
				$this->table_tracker["{$parent}"]['children'][$table] = $parent->type_of_relationship($table);
			}
			return true;
		}
	}

	private function get_depth($table) {
		return $this->table_tracker[$table]['depth'];
	}

	private function get_parent($table) {
		return $this->table_tracker[$table]['parent'];
	}
	/*public function read(Entity $entity, Parameters $parameters) {
		$complete = $parameters->complete;
		$limit = $parameters->limit;
		$offset = $parameters->offset;
		$strictSearch = $parameters->strict_search;
		$blindSearch = false;
		$blindSearch = false;
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
							//$sql["other"][$ent]->select(Query_Builder::select_string($class));
							$sql["other"][$ent]->select('*');
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
			if( ! is_null($limit)) {
				$sql['main']->limit($limit, $offset);
			}			
			
			if($complete === FALSE)
			{
				if($entity->has_active_attributes())
				{
					if($strictSearch) {
						$sql["main"]->where(Query_Builder::where_array($entity));	
					}
					else {
						$sql["main"]->like(Query_Builder::like_array($entity));	
					}
					
					
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
			else
			{
				$blindSearch = true;
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
					}
				}				
			}
		}
		$db_result = $sql["main"]->get()->result_array();
		$result = array();
		$key = 0;
		foreach($db_result as $ent)
		{
			$total = count($db_result);
			
			foreach($ent as $attribute => $value)
			{
				$att_val = explode('_', $attribute);
				$entity_name = $att_val[0];
				$entity_attribute = $att_val[1];
				if($entity_name !== "{$entity}")
				{
					if($total > 1) {
						$result[$key]["{$entity_name}"]["{$entity_attribute}"] = $value;
						unset($result[$key]["{$attribute}"]);
					}
					else {
						
						$result["{$entity_name}"]["{$entity_attribute}"] = $value;
						unset($result["{$attribute}"]);	
					}
						
				}
				else
				{	
					if($total > 1) {
						$result[$key]["{$entity_attribute}"] = $value;
						unset($result[$key]["{$attribute}"]);
						
						
			
						if($blindSearch === TRUE && $entity_attribute === "id")
						{
							//$sql["main"]->where("{$entity}.id", (int)$entity->id);
							if($entity->has_relationships() === TRUE)
							{
								foreach($entity->relationship() as $ent => $rel)
								{
									$class = new Class_Factory($ent);
									$class = $class->get_concrete_class();
									if($rel === 'has_one')
									{
										
									}
									else
									{
										$sql["other"][$ent] = clone $this->db;
										//$sql["other"][$ent]->select(Query_Builder::select_string($class));
										$sql["other"][$ent]->select('*');
										$sql["other"][$ent]->from("{$entity}_{$ent}");
										$sql["other"][$ent]
											->join($ent, 
												"{$entity}_{$ent}.{$ent}_id={$ent}.id");
										$sql["other"][$ent]->where("{$entity}_{$ent}.{$entity}_id", (int)$result[$key]["id"]);
										//die(var_dump($sql['other']));
										$res = $sql["other"][$ent]->get()->result_array();
										$x = 0;
										foreach($res as $data => $entity_value)	{
											if(count($res) > 1) {
												$result[$key][$ent][$x] = $entity_value;
												unset($result[$key][$ent][$x]["{$ent}_id"]);
												unset($result[$key][$ent][$x]["{$entity}_id"]);
												$x++;	
											}
											else {
												$result[$key][$ent] = $entity_value;
												unset($result[$key][$ent]["{$ent}_id"]);
												unset($result[$key][$ent]["{$entity}_id"]);
											}
										}
									}
								}
							}
						}
					}
					else {
						$result["{$entity_attribute}"] = $value;
						unset($result["{$attribute}"]);
					}
				}
			}
			$key++;
		}
		$key = 0;
		if( ! is_null($sql["other"]) && ! $blindSearch)	{
			foreach($sql["other"] as $entity_name => $query) {
				$res = $query->get()->result_array();
				$x = 0;
				foreach($res as $data => $entity_value)	{
					if(count($res) > 1) {
						$result[$entity_name][$x] = $entity_value;
						unset($result[$entity_name][$x]["{$entity_name}_id"]);
						unset($result[$entity_name][$x]["{$entity}_id"]);
						$x++;	
					}
					else {
						$result[$entity_name] = $entity_value;
						unset($result[$entity_name]["{$entity_name}_id"]);
						unset($result[$entity_name]["{$entity}_id"]);
					}
				}
			}
		}
		
		if(empty($result)) {
			//return array("message" => "No data was found");
			return array();
		}
		return count($result) > 1 ? $result : $result[0];
	}*/
}
