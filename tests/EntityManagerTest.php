<?php
include '../src/Cyve/ORM/EntityManager.php';
include '../src/Cyve/ORM/Entity.php';
include 'Bar.php';

use Cyve\ORM\EntityManager;
use Cyve\ORM\Entity;

$dbh2 = new PDO('sqlite:db/orm', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

$em = new EntityManager($dbh2);

echo "\$em->find();";
try{
	$results = $em->find();
}
catch(Exception $e){
	var_dump($e->getMessage());
}

echo '<hr>';

echo "\$em->entity('Bar', 'foo', array('name'));";
$em = $em->entity('Bar', 'foo', array('name'));
var_dump($em);

echo '<hr>';

echo "\$em->find();";
var_dump($em->find());

echo "\$em->count();";
var_dump($em->count());

echo '<hr>';

echo "\$bar = new Bar(null, 'miam');<br>";
echo "\$em->save(\$bar);";
$bar = new Bar(null, 'miam');
$em->save($bar);
var_dump($em->find($bar->id));

echo "\$bar->name = 'miam 2';<br>";
echo "\$em->save(\$bar);";
$bar->name = 'miam 2';
$em->save($bar);
var_dump($em->find($bar->id));

echo "\$em->delete(\$bar);";
$em->delete($bar);
var_dump($em->find($bar->id));

echo '<hr>';

$method = new ReflectionMethod('Cyve\ORM\EntityManager', 'getFilterSql');
$method->setAccessible(true);
echo "\$em->getFilterSql(array('name' => array('foo','bar')));";
var_dump($method->invokeArgs($em, array(array('name' => array('foo','bar')))));
echo "\$em->getFilterSql(array('name' => '/^foo/'));";
var_dump($method->invokeArgs($em, array(array('name' => '/^foo/'))));
echo "\$em->getFilterSql(array('name' => 'foo%'));";
var_dump($method->invokeArgs($em, array(array('name' => 'foo%'))));
echo "\$em->getFilterSql(array('name' => 'foo'));";
var_dump($method->invokeArgs($em, array(array('name' => 'foo'))));

$method = new ReflectionMethod('Cyve\ORM\EntityManager', 'getOrderSql');
$method->setAccessible(true);
echo "\$em->getOrderSql(array('name' => 'asc', 'date' => 'desc'));";
var_dump($method->invokeArgs($em, array(array('name' => 'asc', 'date' => 'desc'))));

$method = new ReflectionMethod('Cyve\ORM\EntityManager', 'getLimitSql');
$method->setAccessible(true);
echo "\$em->getLimitSql(100);";
var_dump($method->invokeArgs($em, array(100)));
echo "\$em->getLimitSql(100,10);";
var_dump($method->invokeArgs($em, array(100,10)));

echo "\$em->getTable();";
var_dump($em->getTable());

echo "\$em->getFields();";
var_dump($em->getFields());

echo "\$em->setTable('foo2')->getTable();";
var_dump($em->setTable('foo2')->getTable());

echo "\$em->setFields(array('name','location'))->getFields();";
var_dump($em->setFields(array('name','location'))->getFields());
