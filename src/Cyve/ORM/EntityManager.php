<?php
/**
 * @author Cyril Vermande (cyril@cyrilwebdesign.com)
 * @license MIT
 * @copyright 2016 Cyril Vermande
 */

namespace Cyve\ORM;

/**
 * EntityManager
 */
class EntityManager
{
	/**
	 * @var PDO
	 */
	private $dbh;
	
	/**
	 * @var string
	 */
	private $class;
	
	/**
	 * @var string
	 */
	private $table;
	
	/**
	 * @var array
	 */
	private $fields;
	
	/**
	 * Constructor
	 */
	public function __construct($dbh, $class=null, $table=null, $fields=null){
		$this->dbh = $dbh;
		if(!empty($class)) $this->class = $class;
		if(!empty($table)) $this->table = $table;
		if(!empty($fields)) $this->fields = $fields;
	}
	
	/**
	 * Retrieve an instance for the specified class
	 *
	 * @param string $class
	 * @return EntityManager
	 */
	public function entity($class, $table=null, $fields=null){
		return new self($this->dbh, $class, $table, $fields);
	}
	
	/**
	 * Read objects from database
	 *
	 * @param array $filters
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return array|object
	 */
	public function find($filters=array(), $orderBy=array(), $limit=null, $offset=null){
		if(empty($this->class)) throw new \Exception("Undefined entity class");
		
		$dbh = $this->dbh;
		$class = $this->class;
		$table = $this->getTable();
		
		if(is_numeric($filters)){
			$filters = array('id' => $filters);
			$limit = 1;
		}
		
		$sql = "SELECT * FROM ".addslashes($table);
		$sql .= $this->getFilterSql($filters);
		$sql .= $this->getOrderSql($orderBy);
		$sql .= $this->getLimitSql($limit, $offset);
		$sql .= ";";
		//echo $sql;die;
		
		$query = $dbh->query($sql);
		$query->setFetchMode(\PDO::FETCH_CLASS, $class);
		if($limit === 1) $results = $query->fetch();
		else $results = $query->fetchAll();
		
		return $results;
	}
	
	/**
	 * Count objects in database
	 *
	 * @param array $filters
	 * @return integer
	 * @throw Exception if EntityManager::class is undefined
	 */
	public function count($filters=array()){
		if(empty($this->class)) throw new \Exception("Undefined EntityManager::class");
		
		$dbh = $this->dbh;
		$table = $this->getTable();
		
		if(is_numeric($filters)){
			$filters = array('id' => $filters);
			$limit = 1;
		}
		
		$sql = "SELECT COUNT(*) AS count FROM ".addslashes($table);
		$sql .= $this->getFilterSql($filters);
		$sql .= ";";
		//echo $sql;die;
		
		$query = $dbh->query($sql);
		$query->setFetchMode(\PDO::FETCH_OBJ);
		return (int) $query->fetch()->count;
	}
	
	/**
	 * Create or update object in database
	 *
	 * @return boolean
	 */
	public function save($object){
		$dbh = $this->dbh;
		$table = $this->getTable();
		$fields = $this->getFields();
		
		if(empty($object->id)){
			$values = array();
			foreach($fields as $field){
				$values[] = "'".(isset($object->$field) ? addslashes($object->$field) : '')."'";
			}
			
			$sql = "INSERT INTO ".addslashes($table)." (".addslashes(implode(',', $fields)).")";
			$sql .= " VALUES (".implode(',', $values).")";
			$sql .= ";";
		}
		else {
			$values = array();
			foreach($fields as $field){
				$values[] = addslashes($field)." = '".(isset($object->$field) ? addslashes($object->$field) : '')."'";
			}
			
			$sql = "UPDATE ".addslashes($table);
			$sql .= " SET ".implode(',', $values);
			$sql .= " WHERE id = ".addslashes($object->id).";";
		}
		//echo $sql;die;
		
		$result = $dbh->exec($sql);
		if($result && empty($object->id)) $object->id = $dbh->lastInsertId();
		
		return $result !== false;
	}
	
	/**
	 * Delete object from database
	 *
	 * @return boolean
	 * @throw Exception if EntityManager::class is undefined
	 */
	public function delete($param){
		if(empty($this->class)) throw new \Exception("Undefined EntityManager::class");
		
		$dbh = $this->dbh;
		$table = $this->getTable();
		
		if(is_numeric($param)){
			$param = array('id' => $param);
		}
		elseif($param instanceof $this->class){
			$param = array('id' => $param->id);
		}
		
		$sql = "DELETE FROM ".addslashes($table);
		$sql .= $this->getFilterSql($param);
		$sql .= ";";
		//echo $sql;die;
		
		$result = $dbh->exec($sql);
		
		return $result !== false;
	}
	
	/**
	 * Set class
	 *
	 * @param string
	 * @return EntityManager
	 */
	public function setClass($class){
		$this->class = $class;
		return $this;
	}
	
	/**
	 * Get class
	 *
	 * @return string
	 */
	public function getClass(){
		return $this->class;
	}
	
	/**
	 * Set table
	 *
	 * @param string
	 * @return EntityManager
	 * @throw Exception if EntityManager::class is undefined
	 */
	public function setTable($table){
		if(empty($this->class)) throw new \Exception("Undefined EntityManager::class");
		$this->table = $table;
		return $this;
	}
	
	/**
	 * Get table
	 *
	 * @return string
	 */
	public function getTable(){
		if(empty($this->table) && !empty($this->class)) $this->table = strtolower($this->class);
		return $this->table;
	}
	
	/**
	 * Set fields
	 *
	 * @param array
	 * @return EntityManager
	 * @throw Exception if EntityManager::class is undefined
	 */
	public function setFields($fields){
		if(empty($this->class)) throw new \Exception("Undefined EntityManager::class");
		$this->fields = $fields;
		return $this;
	}
	
	/**
	 * Get fields
	 *
	 * @return array
	 */
	public function getFields(){
		if(empty($this->fields) && !empty($this->class)){
			$reflectionData = new \ReflectionClass($this->class);
			$staticProperties = array_keys($reflectionData->getStaticProperties());
			$properties = $reflectionData->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
			array_walk($properties, function(&$value, $key){
				$value = $value->getName();
			});
			$this->fields = array_diff($properties, $staticProperties, array('id'));
		}
		return $this->fields;
	}
	
	/**
	 * Format "WHERE" section for SQL query
	 *
	 * @param array $filters
	 * @return string
	 */
	protected function getFilterSql($filters){
		if(empty($filters)) return '';
		
		$where = array();
		foreach($filters as $key=>$value){
			if(is_array($value)) $where[] = addslashes($key)." IN ('".implode("','", array_map('addslashes', $value))."')";
			elseif(substr($value, 0, 1) === "/" && substr($value, -1) === "/") $where[] = addslashes($key)." REGEXP '".addslashes($value)."'";
			elseif(strpos($value, '%') !== false) $where[] = addslashes($key)." LIKE '".addslashes($value)."'";
			else $where[] = addslashes($key)."='".addslashes($value)."'";
		}
		
		if(sizeof($where) === 0) return '';
		return " WHERE ".implode(" AND ", $where);
	}
	
	/**
	 * Format "ORDER BY" section for SQL query
	 *
	 * @param array $orderBy
	 * @return string
	 */
	protected function getOrderSql($orderBy){
		if(empty($orderBy)) return '';
		
		$order = array();
		foreach($orderBy as $key=>$value){
			$order[] = addslashes($key)." ".addslashes($value);
		}
		
		if(sizeof($order) === 0) return '';
		return " ORDER BY ".implode(",", $order);
	}
	
	/**
	 * Format "LIMIT" section for SQL query
	 *
	 * @param integer $limit
	 * @param integer $offset
	 * @return string
	 */
	protected function getLimitSql($limit, $offset=0){
		if(empty($limit) || !filter_var($limit, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)))) return '';
		if(empty($offset)) $offset = 0;
		else $offset = filter_var($offset, FILTER_VALIDATE_INT, array('options' => array('default' => 0, 'min_range' => 0)));
		return " LIMIT ".$offset.",".$limit;
	}
}
