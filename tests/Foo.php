<?php
use Cyve\ORM\Entity;

class Foo{
	use Entity;
	
	public $id;
	public $name;
	
	public function __construct($id=null, $name=null){
		if($id !== null) $this->id = $id;
		if($name != null) $this->name = $name;
	}
}
