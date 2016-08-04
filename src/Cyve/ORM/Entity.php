<?php
/**
 * @author Cyril Vermande (cyril@cyrilwebdesign.com)
 * @license MIT
 * @copyright 2016 Cyril Vermande
 *
 * @todo prepared queries
 * @todo validator
 */

namespace Cyve\ORM;

/**
 * Entity
 */
trait Entity
{
	/**
	 * @var EntityManager
	 */
	protected static $em;
	
	/**
	 * @ver string
	 */
	protected static $table;
	
	/**
	 * @var array
	 */
	protected static $fields;
	
	/**
	 * Read objects from database
	 *
	 * @param array $filters
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return array|object
	 */
	public static function find($filters=array(), $orderBy=array(), $limit=null, $offset=null){
		return static::getEntityManager()->find($filters, $orderBy, $limit, $offset);
	}
	
	/**
	 * Count objects in database
	 * 
	 * @param array $filters
	 * @return integer
	 */
	public static function count($filters=array()){
		return static::getEntityManager()->count($filters);
	}
	
	/**
	 * Returns true if object is not saved in database
	 *
	 * @return boolean
	 */
	public function isNew(){
		return empty($this->id) || $this->id < 0;
	}
	
	/**
	 * Create or update object in database
	 *
	 * @return boolean
	 */
	public function save(){
		$this->preSave();
		$result = static::getEntityManager()->save($this);
		$this->postSave($result);
		return $result;
	}
	
	/**
	 * Executed before saving object
	 *
	 * @return
	 */
	public function preSave(){}
	
	/**
	 * Executed after saving object
	 *
	 * @param boolean
	 */
	public function postSave($result){}
	
	/**
	 * Delete object from database
	 *
	 * @return boolean
	 */
	public function delete(){
		$this->preDelete();
		$result = static::getEntityManager()->delete($this->id);
		$this->postDelete($result);
		return $result;
	}
	
	/**
	 * Executed before deleting object
	 *
	 * @return
	 */
	public function preDelete(){}
	
	/**
	 * Executed after deleting object
	 *
	 * @param boolean
	 */
	public function postDelete($result){}
	
	/**
	 * Retrieve entity manager
	 *
	 * @return EntityManager
	 */
	public static function getEntityManager(){
		global $dbh;
		
		$class = __CLASS__;
		$table = empty(static::$table) ? strtolower(__CLASS__) : static::$table;
		if(empty(static::$fields)){
			$reflectionData = new \ReflectionClass(__CLASS__);
			$staticProperties = array_keys($reflectionData->getStaticProperties());
			$properties = $reflectionData->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
			array_walk($properties, function(&$value, $key){
				$value = $value->getName();
			});
			$fields = array_diff($properties, $staticProperties, array('id'));
		}
		else{
			$fields = explode(',', static::$fields);
		}
		
		if(!static::$em instanceof EntityManager) static::$em = new EntityManager($dbh, $class, $table, $fields);
		return static::$em;
	}
}

