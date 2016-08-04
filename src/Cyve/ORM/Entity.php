<?php
/**
 * @author Cyril Vermande (cyril@cyrilwebdesign.com)
 * @license MIT
 * @copyright 2016 Cyril Vermande
 *
 * @todo prepared queries
 * @todo validator
 * @todo hooks
 */

namespace Cyve\ORM;

/**
 * Entity
 */
trait Entity
{
	/**
	 * @ver PDO
	 */
	protected static $dbh;
	
	/**
	 * @ver string
	 */
	protected static $table;
	
	/**
	 * @var array
	 */
	protected static $fields;
	
	/**
	 * @return boolean
	 */
	public function isNew(){
		return empty($this->id) || $this->id < 0;
	}
	
	/**
	 * @param array $filters
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return array|object
	 */
	public static function find($filters=array(), $orderBy=array(), $limit=null, $offset=null){
		$dbh = static::getDbh();
		$table = static::getTable(__CLASS__);
		
		if(is_numeric($filters)){
			$filters = array('id' => $filters);
			$limit = 1;
		}
		
		$sql = "SELECT * FROM ".addslashes($table);
		$sql .= static::getFilterSql($filters);
		$sql .= static::getOrderSql($orderBy);
		$sql .= static::getLimitSql($limit, $offset);
		$sql .= ";";
		//echo $sql;die;
		
		$query = $dbh->query($sql);
		$query->setFetchMode(\PDO::FETCH_CLASS, __CLASS__);
		if($limit === 1) $results = $query->fetch();
		else $results = $query->fetchAll();
		
		return $results;
	}
	
	/**
	 * @param array $filters
	 * @return integer
	 */
	public static function count($filters=array()){
		$dbh = static::getDbh();
		$table = static::getTable(__CLASS__);
		
		if(is_numeric($filters)){
			$filters = array('id' => $filters);
			$limit = 1;
		}
		
		$sql = "SELECT COUNT(*) AS count FROM ".addslashes($table);
		$sql .= static::getFilterSql($filters);
		$sql .= ";";
		//echo $sql;die;
		
		$query = $dbh->query($sql);
		$query->setFetchMode(\PDO::FETCH_OBJ);
		return (int) $query->fetch()->count;
	}
	
	/**
	 * @return boolean
	 */
	public function save(){
		if(method_exists(__CLASS__, "validate") && !$this->validate()) throw new \Exception("Invalid data");
		
		$dbh = static::getDbh();
		$table = static::getTable(__CLASS__);
		$fields = static::getFields(__CLASS__);
		
		if($this->isNew()){
			$values = array();
			foreach($fields as $field){
				$values[] = "'".(isset($this->$field) ? addslashes($this->$field) : '')."'";
			}
			
			$sql = "INSERT INTO ".addslashes($table)." (".addslashes(implode(',', $fields)).")";
			$sql .= " VALUES (".implode(',', $values).")";
			$sql .= ";";
		}
		else {
			$values = array();
			foreach($fields as $field){
				$values[] = addslashes($field)." = '".(isset($this->$field) ? addslashes($this->$field) : '')."'";
			}
			
			$sql = "UPDATE ".addslashes($table);
			$sql .= " SET ".implode(',', $values);
			$sql .= " WHERE id = ".addslashes($this->id).";";
		}
		//echo $sql;die;
		
		$result = $dbh->exec($sql);
		if($result && $this->isNew()) $this->id = $dbh->lastInsertId();
		
		return $result !== false;
	}
	
	/**
	 * @return boolean
	 */
	public function delete(){
		$dbh = static::getDbh();
		$table = static::getTable(__CLASS__);
		
		$sql = "DELETE FROM ".addslashes($table)." WHERE id = ".addslashes($this->id).";";
		//echo $sql;die;
		
		$result = $dbh->exec($sql);
		
		return $result !== false;
	}
	
	/**
	 * @param PDO
	 */
	public static function setDbh($dbh){
		static::$dbh = $dbh;
	}
	
	/**
	 * @return PDO
	 */
	public static function getDbh(){
		global $dbh;
		if(empty(static::$dbh) && !empty($dbh)) static::$dbh = $dbh;
		return static::$dbh;
	}
	
	/**
	 * @return string
	 */
	private static function getTable(){
		if(empty(static::$table)) static::$table = strtolower(__CLASS__);
		return static::$table;
	}
	
	/**
	 * @return array
	 */
	private static function getFields(){
		if(empty(static::$fields)){
			$reflectionData = new \ReflectionClass(__CLASS__);
			$staticProperties = array_keys($reflectionData->getStaticProperties());
			$properties = $reflectionData->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
			array_walk($properties, function(&$value, $key){
				$value = $value->getName();
			});
			static::$fields = array_diff($properties, $staticProperties, array('id'));
		}
		elseif(is_string(static::$fields)) static::$fields = explode(',', static::$fields);
		return static::$fields;
	}
	
	/**
	 * @param array $filters
	 * @return string
	 */
	public static function getFilterSql($filters){
		if(empty($filters)) return '';
		
		$where = array();
		foreach($filters as $key=>$value){
			if(is_array($value)) $where[] = addslashes($key)." IN ('".implode("','", array_map('addslashes', $value))."')";
			elseif(substr($value, 0, 1) === "/" && substr($value, -1) === "/") $where[] = addslashes($key)." REGEXP '".addslashes($value)."'";
			elseif(strpos($value, '%') >= 0) $where[] = addslashes($key)." LIKE '".addslashes($value)."'";
			else $where[] = addslashes($key)."='".addslashes($value)."'";
		}
		
		if(sizeof($where) === 0) return '';
		return " WHERE ".implode(" AND ", $where);
	}
	
	/**
	 * @param array $orderBy
	 * @return string
	 */
	public static function getOrderSql($orderBy){
		if(empty($orderBy)) return '';
		
		$order = array();
		foreach($orderBy as $key=>$value){
			$order[] = addslashes($key)." ".addslashes($value);
		}
		
		if(sizeof($order) === 0) return '';
		return " ORDER BY ".implode(",", $order);
	}
	
	/**
	 * @param integer $limit
	 * @param integer $offset
	 * @return string
	 */
	public static function getLimitSql($limit, $offset){
		if(empty($limit) || !filter_var($limit, FILTER_VALIDATE_INT, array('options' => array('min_range' => 0)))) return '';
		if(empty($offset)) $offset = 0;
		else $offset = filter_var($offset, FILTER_VALIDATE_INT, array('options' => array('default' => 0, 'min_range' => 0)));
		return " LIMIT ".$offset.",".$limit;
	}
}

