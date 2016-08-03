<?php
include '../src/Cyve/ORM/EntityManager.php';
include '../src/Cyve/ORM/Entity.php';
include 'Foo.php';

use Cyve\ORM\EntityManager;
use Cyve\ORM\Entity;

$dbh2 = new PDO('sqlite:db/orm', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

$em = new EntityManager($dbh2);

echo "\$em->entity('Foo')->find();";
$results = $em->entity('Foo')->find();
var_dump($results);

echo "\$em->entity('Foo')->count();";
$results = $em->entity('Foo')->count();
var_dump($results);

echo "\$em->find();";
try{
	$results = $em->find();
}
catch(Exception $e){
	var_dump($e->getMessage());
}
