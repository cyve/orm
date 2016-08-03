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
	 * Constructor
	 */
	public function __construct($dbh, $class=null){
		$this->dbh = $dbh;
		if(!empty($class)) $this->class = $class;
	}
	
	/**
	 * @param string $class
	 * @return EntityManager
	 */
	public function entity($class){
		return new self($this->dbh, $class);
	}
	
	/**
	 * @param array $filters
	 * @param array $orderBy
	 * @param integer $limit
	 * @param integer $offset
	 * @return array|object
	 */
	public function find($filters=array(), $orderBy=array(), $limit=null, $offset=null){
		if(empty($this->class)) throw new \Exception("Undefined entity class");
		$class = $this->class;
		if(empty($class::getDbh())) $class::setDbh($this->dbh);
		return $class::find($filters, $orderBy, $limit, $offset);
	}
	
	/**
	 * @param array $filters
	 * @return integer
	 */
	public function count($filters=array()){
		if(empty($this->class)) throw new \Exception("Undefined entity class");
		$class = $this->class;
		if(empty($class::getDbh())) $class::setDbh($this->dbh);
		return $class::count($filters);
	}
}
