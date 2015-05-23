<?php
include_once(dirname(__DIR__).'/mappers/data_mapper.php');
include_once('class_factory.php');
include_once('converter.php');

class CRUD_Controller extends CI_Controller {

	private $entity_data;

	public $input;

	protected $params = array(
			'strict_search'	=>	false,
    		'format'		=>	'json',
    		'limit'			=>	5,
    		'offset'		=>	0,
    		'complete'		=>	false
    		);

    public function __construct()
    {
        parent::__construct();
    }

    protected function get_one($index)
    {
    	return $this->entity_data[$index];
    }

    private function output()
    {
    	$convert = new Converter();

    	$format = 'to_'.$this->params['format'];

    	if(get_called_class() === 'CRUD_Controller')
    	{
    		/**
    		 * The responses need to be changed to match
    		 * more common server-side responses
    		 */
    		echo $convert->{$format}($this->entity_data);
    	}
    	else
    	{
    		return $convert->{$format}($this->entity_data);
    	}
    }

    private function check_default_params($http)
    {
    	switch($http)
    	{
    		case 'get':
    			foreach($this->params as $key => $value)
		    	{
		    		if( ! empty($_GET["{$key}"]) && $_GET["{$key}"] !== '')
		    		{
		    			$this->params[$key] = strtolower($_GET[$key]);
		    			unset($_GET[$key]);
		    		}
		    	}
    			break;
    		case 'post':
    			foreach($this->params as $key => $value)
		    	{
		    		if( ! empty($_POST[$key]) && $_POST[$key] !== '')
		    		{
		    			$this->params[$key] = $_POST[$key];
		    			unset($_POST[$key]);
		    		}
		    	}
    			break;
    		case 'put':
    			foreach($this->params as $key => $value)
		    	{
		    		if( ! empty($_PUT[$key]) && $_PUT[$key] !== '')
		    		{
		    			$this->params[$key] = $_PUT[$key];
		    			unset($_PUT[$key]);
		    		}
		    	}
    			break;
    		case 'delete':
    			foreach($this->params as $key => $value)
		    	{
		    		if( ! empty($_DELETE[$key]) && $_DELETE[$key] !== '')
		    		{
		    			$this->params[$key] = $_DELETE[$key];
		    			unset($_DELETE[$key]);
		    		}
		    	}
    			break;
    		default:
    			throw new Exception("Unrecognized http", 1);    			
    	}

    	switch(strtolower($this->params['complete']))
    	{
    		case 'true':
    			$this->params['complete'] = true;
    			break;
    		case 'false':
    			$this->params['complete'] = false;
    			break;
    		case true:
    		case false:
    			break;
    		default:
    			throw new Exception("wrong complete parameter", 1);		
    	}
    }

    //Used to create a GET that contains multiple collections
    public function customRead($collectionName) {

    }

    //create a funtion that will parse query parameters and entity(s) data

   
    public function read($entity, $id = NULL)
    {
    	$this->check_default_params('get');

    	$class_factory = new Class_Factory($entity);
		$obj = $class_factory->get_concrete_class();

		if(is_null($id))
		{
			if( ! empty($_GET))
			{
				foreach($this->input->get(NULL, TRUE) as $attribute => $value)
				{
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
			}
		}
		else
		{
			$obj->id = $id;
		}
		
		$mapper = new Data_Mapper($obj);
		$this->entity_data = $mapper->read(
				$this->params['complete'], 
				$this->params['limit'],
				$this->params['offset']
				);

		if(count($this->entity_data) === 1)
		{
			$this->entity_data = $this->get_one(0);
		}

		return $this->output();
	}

	public function create($data_name)
	{
		$class_factory = new Class_Factory($data_name);
		$obj = $class_factory->get_concrete_class();
		
		foreach($this->input->get(NULL, TRUE) as $attribute => $value)
		{
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
				
				if(is_array($value))
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
				{
					$class_factory = new Class_Factory($entity_value[0]);
					$related_obj = $class_factory->get_concrete_class();
					$related_obj->$entity_value[1] = $value;

					$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
				}

				$obj->active_attributes(TRUE);
			}
			else
			{
				throw new Exception("Unknown attribute {$attribute}", 1);
			}
		}

		$mapper = new Data_Mapper($obj);

		$mapper->create();

		return $this->output();
	}

	public function update($data_name, $id)
	{
		$this->check_default_params('get');

		$class_factory = new Class_Factory($data_name);
		$obj = $class_factory->get_concrete_class();
		
		$obj->id = $id;

		foreach($this->input->get(NULL, TRUE) as $attribute => $value)
		{
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
				
				if(is_array($value))
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
				{
					$class_factory = new Class_Factory($entity_value[0]);
					$related_obj = $class_factory->get_concrete_class();
					$related_obj->$entity_value[1] = $value;

					$obj->{$obj->type_of_relationship((string)$related_obj)}($related_obj);
				}

				$obj->active_attributes(TRUE);
			}
			else
			{
				throw new Exception("Unknown attribute {$attribute}", 1);
			}
		}

		$mapper = new Data_Mapper($obj);
		$mapper->update($this->params['complete']);

		return $this->output();
	}

	public function delete($data_name, $id)
	{
		$class_factory = new Class_Factory($data_name);
		$obj = $class_factory->get_concrete_class();

		if(is_null($id))
		{
			throw new Exception("Error Processing Request", 1);
		}
		else
		{
			$obj->id = $id;

			if(empty($_GET) === FALSE)
			{
				foreach($this->input->get(NULL, TRUE) as $attribute => $value)
				{
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
						
						if(is_array($value))
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
						{
							$class_factory = new Class_Factory($entity_value[0]);
							$related_obj = $class_factory->get_concrete_class();
							$related_obj->$entity_value[1] = $value;

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
		}

		$mapper = new Data_Mapper($obj);

		$mapper->update();

		return $this->output();
	}
}

//http://localhost/local/content-server/index.php/test/create/video?genre_id=1&director_id=1&department_id=1&category_id=5&rating_id=1&name=Iron%20Man&description=metal&date=2014-03-01&picture=something.jpg&reference=adass&duration=100&price=24.56&quantity=1
