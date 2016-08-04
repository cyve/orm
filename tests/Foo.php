<?php
class Foo{
	use Cyve\ORM\Entity;
	
	public $id;
	public $name;
	
	public function __construct($id=null, $name=null){
		if($id !== null) $this->id = $id;
		if($name != null) $this->name = $name;
	}
}
