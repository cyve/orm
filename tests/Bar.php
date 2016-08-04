<?php
class Bar{
	public $id;
	public $name;
	public $user;
	
	public static $table = 'foo';
	public static $fields = 'name';
	
	public function __construct($id=null, $name=null){
		if($id !== null) $this->id = $id;
		if($name != null) $this->name = $name;
	}
}
