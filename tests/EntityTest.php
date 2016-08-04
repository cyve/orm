<?php
include '../src/Cyve/ORM/EntityManager.php';
include '../src/Cyve/ORM/Entity.php';
include 'Foo.php';

use Cyve\ORM\EntityManager;
use Cyve\ORM\Entity;

$dbh = new PDO('sqlite:db/orm', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

echo "Foo::getEntityManager();";
var_dump(Foo::getEntityManager());

echo '<hr>';

echo "Foo::find();";
var_dump(Foo::find());

echo "Foo::count();";
var_dump(Foo::count());

echo "Foo::find(null, array('name' => 'desc'));";
var_dump(Foo::find(null, array('name' => 'desc')));

echo "Foo::find(1);";
var_dump(Foo::find(1));

echo "Foo::find(array('name' => 'bar'));";
var_dump(Foo::find(array('name' => 'bar')));

echo "Foo::count(array('name' => 'bar'));";
var_dump(Foo::count(array('name' => 'bar')));

echo "Foo::find(array('name' => 'ba%'));";
var_dump(Foo::find(array('name' => 'ba%')));

echo "Foo::find(null, null, 1);";
var_dump(Foo::find(null, null, 1));

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

$property = new ReflectionProperty('Foo', 'em');
$property->setAccessible(true);
$property->setValue(null);

$property = new ReflectionProperty('Foo', 'table');
$property->setAccessible(true);
$property->setValue('foo2');

$property = new ReflectionProperty('Foo', 'fields');
$property->setAccessible(true);
$property->setValue('name,location');

echo "Foo::getEntityManager();";
var_dump(Foo::getEntityManager());
