# orm

Simple Object Relational Mapping for PHP

## Installation

```shell
composer require cyve/orm
composer install
composer dump-autoload
```

## Usage

```php
<?php
include 'vendor/autoload.php';

use Cyve\ORM\EntityManager;
use Cyve\ORM\Entity;

$dbh = new PDO('sqlite:db/orm', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
$em = new EntityManager($dbh);

$items = $em->entity('Foo')->find(array('name' => 'bar'));
```
or
```php
<?php
include 'vendor/autoload.php';

$dbh = new PDO('sqlite:db/orm', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));

class Foo{
	use Cyve\ORM\Entity;
	
	public $id;
	public $name;
	...
}

$items = Foo::find(array('name' => 'bar'));
```
