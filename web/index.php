<?php
use Bookshelf\Core\Db;
use Bookshelf\Core\Router;
use Bookshelf\Core\Session;

include('../vendor/autoload.php');


$session = new Session();
$session ->init();

//put your credentials here
//probably it MUST be stored in other place
$dbName = 'bookshelf';
$dbUserName = 'postgres';
$dbUserPassword = 'postgres';
Db::getInstance($dbName, $dbUserName, $dbUserPassword);


$router = new Router();
$router->handleRequest($_GET);
