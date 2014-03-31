<?php

namespace Light\Data\Service;

use Light\Data\Helper;

class Projection
{
	private static $root;
	
	/**
	 * @return \Light\Data\Service\Projection
	 */
	public static function getRootProjection()
	{
		if (is_null(self::$root))
		{
			self::$root = new self;
		}
		return self::$root;
	}
	
	/**
	 * Returns a projection of the given object or objects.
	 * This is a shorthand method for quickly projecting a single object or collection.
	 * @param object|array	$objects
	 * @return object|array
	 */
	public static function project_once($objects)
	{
		$projection = new self;
		return $projection->project($objects);
	}
	
	private $parent;
	private $classes = array();
	
	public function __construct(Projection $parent = NULL)
	{
		$this->parent = $parent;
	}
	
	/**
	 * Returns the parent Projection.
	 * @return \Light\Data\Service\Projection
	 */
	public function getParent()
	{
		if (is_null($this->parent) && $this !== self::$root)
		{
			return self::$root;
		}
		return $this->parent;
	}
	
	/**
	 * Returns an object for specifying field inclusions for the given class.
	 * Multiple calls to this method return the same object.
	 * @param string	$className	Name of class
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function getClass($className)
	{
		$className = self::normalizeClassName($className);
		if (!isset($this->classes[$className]))
		{
			$this->classes[$className] = new Projection_Class($className);
		}
		return $this->classes[$className];
	}
	
	/**
	 * Returns a Projector that can efficiently create projections.
	 */
	public function getProjector()
	{
		$names = array_keys($this->classes);
		$ancestors = array();
		$p = $this;
		while (!is_null($p = $p->getParent()))
		{
			$ancestors[] = $p;
			$names = array_merge($names, array_keys($p->classes)); 
		}
		$names = array_unique($names);
		
		$classes = array();
		foreach($names as $name)
		{
			if (isset($this->classes[$name]))
			{
				$sum = clone ($this->classes[$name]);
			}
			else
			{
				$sum = new Projection_Class($name);
			}
			foreach($ancestors as $a)
			{
				if (isset($a->classes[$name]))
				{
					$sum->joinFrom($a->classes[$name]);
				}
			}
			
			$classes[$name] = $sum;
		}
		
		$proj = new Projection_Projector($this, $classes);
		return $proj;
	}
	
	/**
	 * Returns a projection of the given object or objects.
	 * This is a shorthand method for quickly projecting a single object or collection.
	 * In order to execute the projection multiple times, use {@see getProjector()}.
	 * @param object|array	$objects
	 * @return object|array
	 */
	public function project($objects)
	{
		return $this->getProjector()->project($objects);
	}
	
	public static function normalizeClassName($className)
	{
		if ($className[0] != "\\")
		{
			$className = "\\" . $className;
		}
		return $className;
	}

}

class Projection_Class
{
	private $className;
	private $inherit = true;
	private $includeFields		= array();
	private $excludeFields		= array();
	private $transformations	= array();
	private $virtualFields		= array();
	private $aliases			= array();
	private $subclasses			= array();
	private $joinedFromParent	= false;
	
	public function __construct($className)
	{
		$this->className = $className;
	}
	
	/**
	 * Includes all fields in the projection by default.
	 * @return Light\Data\Service\Projection_Class
	 */
	public function include_all()
	{
		$this->inherit = true;
		return $this;
	}
	
	/**
	 * Includes one or more fields in the projection.
	 * @param mixed	$fields
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function included($fields)
	{
		if (func_num_args() > 1)
		{
			$fields = func_get_args();
		}
		else if (is_string($fields))
		{
			$fields = array($fields);
		}
		
		$this->array_add($this->includeFields, $fields);
		return $this;
	}
	
	/**
	 * Excludes one or more fields from the projection.
	 * @param mixed	$fields
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function excluded($fields)
	{
		if (func_num_args() > 1)
		{
			$fields = func_get_args();
		}
		else if (is_string($fields))
		{
			$fields = array($fields);
		}
		
		$this->array_add($this->excludeFields, $fields);
		return $this;
	}
	
	/**
	 * Prevents any fields from being inherited from a parent.
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function exclude_all()
	{
		$this->inherit = false;
		return $this;
	}
	
	/**
	 * Creates an alias for a field.
	 * If the field is not included, it is added automatically.
	 * @param string	$original
	 * @param string	$aliased
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function alias($original, $aliased)
	{
		if (!array_search($original, $this->includeFields))
		{
			$this->includeFields[] = $original;
		}
		
		$this->aliases[$original] = $aliased;
		return $this;
	}
	
	/**
	 * Adds a transformation that will be invoked on the field.
	 * @param string	$field		Field name
	 * @param callback	$callback	A function accepting a single parameter ($value)
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function transformation($field, $callback)
	{
		$this->transformations[$field] = $callback;
		return $this;
	}
	
	/**
	 * Adds a virtual field - one whose value is generated by a callback function.
	 * @param string	$field
	 * @param callback	$callback
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function virtualField($field, $callback)
	{
		$this->virtualFields[$field] = $callback;
		return $this;
	}
	
	/**
	 * Returns a new Projection_Class instance that is qualified for the given path pattern.
	 *
	 * The returned instance will be used to project objects of the parent class that match
	 * the specified field path pattern. If no qualified instance is found for a path, then
	 * the parent class will be used for projecting an object.
	 *
	 * @param string	$pattern	A wildcard pattern to match the field path
	 * @return \Light\Data\Service\Projection_Class
	 */
	public function qualify($pattern)
	{
		if (is_null($this->subclasses))
		{
			throw new \Exception("This class cannot be further qualified");
		}
		if (!isset($this->subclasses[$pattern]))
		{
			$this->subclasses[$pattern] = $clazz = new Projection_Class($this->className);
			$clazz->subclasses = null;
			return $clazz;
		}
		return $this->subclasses[$pattern];
	}
	
	/**
	 * @param Projection_Class $class
	 * @throws \Exception
	 */
	public function joinFrom(Projection_Class $class)
	{
		if ($class->className != $this->className)
		{
			throw new \Exception("Class name mismatch");
		}
		
		if (!$this->inherit)
		{
			return;
		}
		
		$this->array_add($this->excludeFields, $class->excludeFields);
		$this->array_add($this->includeFields, array_diff($class->includeFields, $this->excludeFields));
		$this->aliases			= array_merge($this->array_subkeys($class->aliases, $this->excludeFields), $this->aliases);
		$this->transformations	= array_merge($this->array_subkeys($class->transformations, $this->excludeFields), $this->transformations);
		$this->virtualFields	= array_merge($this->array_subkeys($class->virtualFields, $this->excludeFields), $this->virtualFields);
	}
	
	public function getFields()
	{
		return $this->includeFields;
	}
	
	public function getAliases()
	{
		return $this->aliases;
	}
	
	public function getTransformations()
	{
		return $this->transformations;
	}
	
	public function getVirtualFields()
	{
		return $this->virtualFields;
	}

	/**
	 * Find a qualified version that matches the given path.
	 * @param string	$path	Field path
	 * @return \Light\Data\Service\Projection_Class
	 *					A qualified class instance, if found;
	 *					otherwise, the current class instance.
	 */
	public function getQualifiedClass($path)
	{
		if (is_null($this->subclasses))
		{
			return $this;
		}
		
		foreach($this->subclasses as $pattern => $clazz)
		{
			if (fnmatch($pattern, $path))
			{
				if (!$clazz->joinedFromParent)
				{
					$clazz->joinFrom($this);
					$clazz->joinedFromParent = true;
				}
				
				return $clazz;
			}
		}
		
		return $this;
	}
	
	/**
	 * Adds arg to ref.
	 * @param array $ref
	 * @param array $arg
	 */
	private function array_add(array & $ref, array $arg)
	{
		foreach($arg as $a)
		{
			if (!array_search($a, $ref)) $ref[] = $a; 
		}
	}

	/**
	 * Subtracts arg from ref.
	 * @param array $ref
	 * @param array $arg
	 */
	private function array_sub(array & $ref, array $arg)
	{
		foreach($arg as $a)
		{
			$k = array_search($a, $ref);
			if ($k !== false) unset($ref[$k]);
		}
	}
	
	/**
	 * Returns array with all elements with the given keys removed.
	 * @param array $a
	 * @param array $keys
	 * @return array
	 */
	private function array_subkeys(array $a, array $keys)
	{
		foreach($keys as $key) unset($a[$key]);
		return $a;
	}
}

/**
 * A class
 */
class Projection_Projector
{
	private $projection;
	private $classes = array();
	private $stdClass = "\stdClass";
	
	public function __construct(Projection $proj, array $classes)
	{
		$this->projection 	= $proj;
		$this->classes		= $classes;
	}

	/**
	 * Returns a projection of the given object or collection.
	 * @param object|array	$objects	Object or collection to project.
	 * @param string		$path		Path for class qualification.
	 * @return object|array	An object of type stdClass or an array of such objects.
	 */
	public function project($objects, $path = "")
	{
		if (is_array($objects) || ($objects instanceof \Traversable))
		{
			return $this->projectCollection($objects, $path);
		}
		elseif (is_null($objects))
		{
			return NULL;
		}
		elseif (is_scalar($objects))
		{
			return $objects;
		}
		else
		{
			return $this->projectObject($objects, $path);
		}
	}
	
	/**
	 * Returns the Projection object that created this Projector.
	 * @return \Light\Data\Service\Projection
	 */
	public function getOwner()
	{
		return $this->projection;
	}

	private function projectCollection($coll, $path = "")
	{
		$key = key($coll);
		if (is_int($key) || is_null($key))
		{
			// the array has numeric keys - represent it as an array
			$prj = array();
			foreach($coll as $elem)
			{
				$prj[] = $this->project($elem, $path);
			}
		}
		else
		{
			// the array has string keys - represent it as an object
			$prj = new \stdClass;
			foreach($coll as $key => $elem)
			{
				$prj->$key = $this->project($elem, $path);
			}
		}
		
		return $prj;
	}
	
	private function projectObject($obj, $path = "")
	{
		$clsname = Projection::normalizeClassName(get_class($obj));
		if (!isset($this->classes[$clsname]))
		{
			if ($obj instanceof \stdClass)
			{
				return $obj;
			}
			
			// Try to find a matching projector by inspecting superclasses.   
			$cls = null;
			foreach($this->classes as $clsname => $testClass)
			{
				if ($obj instanceof $clsname)
				{
					$cls = $testClass;
					break;
				}
			}
			
			if (is_null($cls))
			{
				throw new \Exception("Class $clsname is not registered in the Projection");
			}
		}
		
		$wrapped = Helper::wrap($obj);
		
		$prjcls = $this->stdClass;
		$prj = new $prjcls;
		
		$cls = $this->classes[$clsname]->getQualifiedClass($path);
		
		$fields 	= $cls->getFields();
		$aliases	= $cls->getAliases();
		$transforms	= $cls->getTransformations();
		$virtuals	= $cls->getVirtualFields();
		foreach($fields as $field)
		{
			$vpath = empty($path)?$field:$path.".".$field;
			$value = $wrapped->getValue($field);
			
			$name = $field;
			if (isset($aliases[$field]))
			{
				$name = $aliases[$field];
			}
			
			if (!is_scalar($value))
			{
				$value = $this->project($value, $vpath);
			}
			
			if (isset($transforms[$field]))
			{
				$value = call_user_func($transforms[$field], $value);
			}
			
			$prj->$name = $value;
		}
		foreach($virtuals as $name => $callback)
		{
			$vpath = empty($path)?$name:$path.".".$name;
			$value = call_user_func($callback, $obj);

			if (!is_scalar($value))
			{
				$value = $this->project($value, $vpath);
			}
			
			$prj->$name = $value;
		}
		
		return $prj;
	}
}