<?php
trait Relationships {

	private $relationships = FALSE;
	/*private $has_many = FALSE;
	private $has_one = FALSE;*/

	private $object_graph = array(
		'has_one' 	=> array(),
		'has_many' 	=> array(),
		'dependats' 	=> array()
		);

	private $relationship_array = array();

	private $active_relationships = array();

	private $relationshipNames = array();

	public function valid_relationship($entity)
	{
		return array_key_exists($entity, $this->relationship_array);
	}

	public function type_of_relationship($entity)
	{
		return $this->relationship_array[$entity];
	}

	public function get_entity($entity_name, $relationship) {
		return 	$this->object_graph[$relationship][(string)$entity_name];
	}

	public function relationship()
	{
		return $this->relationship_array;
	}

	public function active_relationship()
	{
		return $this->active_relationships;
	}

	public function has_relationships()
	{
		return 	count($this->relationship_array) > 0 ?
				TRUE : FALSE;
	}

	public function has_active_relationships()
	{
		return $this->relationships;
	}

	public function relates_to_one($entities)
	{
		if( ! is_string($entities))
		{
			throw new Exception("You must provide a string", 1);
		}

		foreach(explode(',', $entities) as $entity)
		{
			$entity = strtolower(trim($entity));
			$this->object_graph['has_one'][$entity] = NULL;
			$this->relationship_array[$entity] = 'has_one';
		}

		return true;
	}

	public function relates_to_many($entities)
	{
		if( ! is_string($entities))
		{
			throw new Exception("You must provide a string", 1);
		}

		foreach(explode(',', $entities) as $entity)
		{	
			$entity = strtolower(trim($entity));
			$this->object_graph['has_many'][$entity] = array();
			$this->relationship_array[$entity] = 'has_many';
			$this->name_relationship("{$entity}", strtolower(get_called_class())."_{$entity}");
		}

		return true;
	}

	public function depends_on($entities) {
		$this->relates_to_one($entities);
	}

	public function add_dependants($entities) {
		if( ! is_string($entities))
		{
			throw new Exception("You must provide a string", 1);
		}

		foreach(explode(',', $entities) as $entity)
		{
			$entity = strtolower(trim($entity));
			$this->object_graph['has_dependants'][$entity] = NULL;
			$this->relationship_array[$entity] = 'has_dependants';
		}

		return true;
	}

	
	public function has_one(Entity $object)
	{
		$this->relationships = TRUE;
		$this->active_relationships[] = (string)$object;
		$this->object_graph['has_one'][(string)$object] = $object;
	}

	public function has_many(Entity $object)
	{
		$this->relationships = TRUE;
		
		if( ! in_array((string)$object, $this->active_relationships))
		{
			$this->active_relationships[] = (string)$object;
		}

		$this->object_graph['has_many'][(string)$object][] = $object;
	}

	public function has_dependants(Entity $object)
	{
		$this->relationships = TRUE;
		$this->active_relationships[] = (string)$object;
		$this->object_graph['has_dependants'][(string)$object] = $object;
	}

	private function join_dependants(&$db, $class, $entity, $ent) {
		if( ! is_null($class)) {
			$db->select(Query_Builder::select_string($class));	
		}

		$db->join($ent,"{$entity}.id={$ent}.{$entity}_id","left");
	}

	private function join_ones(&$db, $class, $entity, $ent) {
		if( ! is_null($class)) {
			$db->select(Query_Builder::select_string($class));	
		}
		
		$db->join($ent,"{$entity}.id={$ent}.{$entity}_id","left");
	}

	//used to mask table names and many-tomany querying
	public function name_relationship($entityName, $relationshipName) {
		$this->relationshipNames[$entityName] = $relationshipName;
	}

	public function get_relationship_name($entityName) {
		return $this->relationshipNames[$entityName];
	}
}

/* End of file relationships.php */
/* Location: ./application/controllers/traits/relationships.php */
