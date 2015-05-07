<?php
include_once(dirname(dirname(__FILE__)).'/mappers/data_mapper.php');
include_once('class_factory.php');
include_once('converter.php');
include_once('parameters.php');

class CRUD_Service {

    private function output($data, $type)
    {
    	$convert = new Converter($data);

    	switch($type) {
    		case 'json':
    			return $convert->to_json();
    			break;
    		case 'array':
    			return $convert->to_array();
    		default:
    			die("sorry, {$type} format not supported for response");
    	}
    }

    //Used to create a GET that contains multiple collections
    public function customRead($collectionName) {

    }

    //create a funtion that will parse query parameters and entity(s) data
    //
    public function read($entity, Array $values, Parameters $parameters) {
    	$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();
		
		foreach($values as $attribute => $value) {

			if(property_exists($obj, $attribute))
			{
				$obj->$attribute = $value;
				$obj->active_attributes(TRUE);
			}
			//this may be creating duplicate ojects if passed actor_age, actor_lastname
			//solution is to add a check for an already created object. Such solution
			//can be done by retrieving the stored entity in the objec graph. Then,
			//adding the following attributes to it
			//Another point: The creation of entities that are not necesarily related
			//to the main entity can allow for deep search. depth will then become
			//infinity to allow deep search on any enetity down the tree.
			else {
				$temp = explode('_', $attribute);
				$attr = substr($attribute, strlen("{$temp[0]}_"));

				if($obj->valid_relationship($temp[0])) {
					//$entity_value = explode('_', $attribute);

					$class_factory = new Class_Factory($temp[0]);
					$related_obj = $class_factory->get_concrete_class();

					if( ! property_exists($related_obj, $attr))	{
						throw new Exception("Unkown attribute {$attr} for {$related_obj}", 1);
					}

					$related_obj->$attr = $value;
					$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
					$related_obj->active_attributes(TRUE);
				}
				else {
					throw new Exception("Unknown attribute {$attribute}", 1);
				}
			}
		}
//		die(var_dump($obj));
		$mapper = new Data_Mapper($obj);
		$result = $mapper->read($parameters);

		return $this->output($result, $parameters->response_format);
	}

    /*public function read($entity, Array $values, Parameters $parameters) {
    	$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();
		
		foreach($values as $attribute => $value) {

			if(property_exists($obj, $attribute))
			{
				$obj->$attribute = $value;
				$obj->active_attributes(TRUE);
			}
			elseif($obj->valid_relationship(explode('_', $attribute)[0]))
			{
				$entity_value = explode('_', $attribute);
				
				$class_factory = new Class_Factory($entity_value[0]);
				$related_obj = $class_factory->get_concrete_class();
				
				if( ! property_exists($related_obj, $entity_value[1]))
				{
					throw new Exception("Unkown attribute {$entity_value[1]} for {$related_obj}", 1);
				}
				
				$related_obj->$entity_value[1] = $value;
				$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
				$obj->active_attributes(TRUE);
			}
			else
			{
				throw new Exception("Unknown attribute {$attribute}", 1);
			}
		}

		$mapper = new Data_Mapper($obj);
		$result = $mapper->read($parameters);

		return $this->output($result, $parameters->response_format);
	}*/

	public function create($entity, Array $values, Parameters $parameters) {
		$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();
		
		foreach($values as $attribute => $value) {
			if(property_exists($obj, $attribute))
			{
				$obj->$attribute = $value;
				$obj->active_attributes(TRUE);
			}
			else {
				$temp = explode('_', $attribute);
				$attr = substr($attribute, strlen("{$temp[0]}_"));

				if($obj->valid_relationship($temp[0])) {
					//$entity_value = explode('_', $attribute);

					if($attr != 'id') {
						throw new Exception("You may only pass an id", 1);
					}

					if(is_array($value))
					{
						foreach($value as $val) {
							$class_factory = new Class_Factory($temp[0]);
							$related_obj = $class_factory->get_concrete_class();
							$related_obj->$attr = $val;

							$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
						}
					}
					else
					{
						$class_factory = new Class_Factory($temp[0]);
						$related_obj = $class_factory->get_concrete_class();
						$related_obj->$attr = $value;

						$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
					}

					$obj->active_attributes(TRUE);
				}
				else
				{
					throw new Exception("Unknown attribute {$attribute}", 1);
				}
			}
		}

		$mapper = new Data_Mapper($obj);
		$result = $mapper->create($parameters);

		return $this->output($result, $parameters->response_format);
	}

	public function update($entity, Array $values, Parameters $parameters) {

		$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();

		foreach($values as $attribute => $value) {
			if(property_exists($obj, $attribute))
			{
				$obj->$attribute = $value;
				$obj->active_attributes(TRUE);
			}
			else {
				$temp = explode('_', $attribute);
				$attr = substr($attribute, strlen("{$temp[0]}_"));

				if($obj->valid_relationship($temp[0])) {
					$entity_value = explode('_', $attribute);

					if($attr != 'id')
					{
						throw new Exception("You may only pass an id", 1);
					}

					/*if(is_array($value))
					{
						foreach($value as $val)
						{
							$class_factory = new Class_Factory($entity_value[0]);
							$related_obj = $class_factory->get_concrete_class();
							$related_obj->$entity_value[1] = $val;

							$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
						}
					}
					else
					{*/
						$class_factory = new Class_Factory($temp[0]);
						$related_obj = $class_factory->get_concrete_class();
						$related_obj->$attr = $value;

						$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
					//}

					$obj->active_attributes(TRUE);
				}
				else
				{
					throw new Exception("Unknown attribute {$attribute}", 1);
				}
			}
		}

		$mapper = new Data_Mapper($obj);
		$result = $mapper->update($parameters);

		return $this->output($result, $parameters->response_format);
	}

	public function delete($entity, Array $values, Parameters $parameters) {

		$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();

		foreach($values as $attribute => $value) {
			if(property_exists($obj, $attribute))
			{
				$obj->$attribute = $value;
				$obj->active_attributes(TRUE);
			}
			elseif($obj->valid_relationship(explode('_', $attribute)[0]))
			{
				$entity_value = explode('_', $attribute);

				if($entity_value[1] != 'id')
				{
					throw new Exception("You may only pass an id", 1);
				}
				
					$class_factory = new Class_Factory($entity_value[0]);
					$related_obj = $class_factory->get_concrete_class();
					$related_obj->$entity_value[1] = $value;

					$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
			

				$obj->active_attributes(TRUE);
			}
			else
			{
				throw new Exception("Unknown attribute {$attribute}", 1);
			}
		}

		$mapper = new Data_Mapper($obj);
		$result = $mapper->delete($parameters);

		return $this->output($result, $parameters->response_format);
	}
}

//http://localhost/local/content-server/index.php/test/create/video?genre_id=1&director_id=1&department_id=1&category_id=5&rating_id=1&name=Iron%20Man&description=metal&date=2014-03-01&picture=something.jpg&reference=adass&duration=100&price=24.56&quantity=1
