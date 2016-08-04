<?php
include '../src/Cyve/ORM/EntityManager.php';
include '../src/Cyve/ORM/Entity.php';
include 'Foo.php';

use Cyve\ORM\EntityManager;
use Cyve\ORM\Entity;

$dbh = new PDO('sqlite:db/orm', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

echo "Foo::find();";
$results = Foo::find();
var_dump($results);

echo "Foo::count();";
$results = Foo::count();
var_dump($results);

echo "Foo::find(null, array('name' => 'desc'));";
$results = Foo::find(null, array('name' => 'desc'));
var_dump($results);

echo "Foo::find(1);";
$results = Foo::find(1);
var_dump($results);

echo "Foo::find(array('name' => 'bar'));";
$results = Foo::find(array('name' => 'bar'));
var_dump($results);

echo "Foo::count(array('name' => 'bar'));";
$results = Foo::count(array('name' => 'bar'));
var_dump($results);

echo "Foo::find(array('name' => 'ba%'));";
$results = Foo::find(array('name' => 'ba%'));
var_dump($results);

echo "Foo::find(null, null, 1);";
$results = Foo::find(null, null, 1);
var_dump($results);

echo '<hr>';

$foo = new Foo(null, 'miam');
var_dump($foo->isNew());
$foo->save();
$foo = Foo::find($foo->id);
var_dump($foo);

$foo->name = 'miam 2';
$foo->save();
$foo = Foo::find($foo->id);
var_dump($foo);

$foo->delete();
$foo = Foo::find($foo->id);
var_dump($foo);

echo '<hr>';

echo "Foo::getTable();";
$method = new ReflectionMethod('Foo', 'getTable');
$method->setAccessible(true);
var_dump($method->invoke(new Foo));

echo "Foo::getFields();";
$method = new ReflectionMethod('Foo', 'getFields');
$method->setAccessible(true);
var_dump($method->invoke(new Foo));

$property = new ReflectionProperty('Foo', 'table');
$property->setAccessible(true);
$property->setValue('foo2');

echo "Foo::getTable();";
$method = new ReflectionMethod('Foo', 'getTable');
$method->setAccessible(true);
var_dump($method->invoke(new Foo));

$property = new ReflectionProperty('Foo', 'fields');
$property->setAccessible(true);
$property->setValue('name,location');

echo "Foo::getFields();";
$method = new ReflectionMethod('Foo', 'getFields');
$method->setAccessible(true);
var_dump($method->invoke(new Foo));
