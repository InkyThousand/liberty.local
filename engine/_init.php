<?php defined('MONSTRA_ACCESS') or die('No direct script access.');
include ROOT . DS .'engine'. DS .'Monstra.php';
if (strpos(@$_SERVER['SERVER_NAME'], '.local') === false)
{
  Monstra::$environment = Monstra::PRODUCTION;
}
else
{
  Monstra::$environment = Monstra::DEVELOPMENT;
}

if (Monstra::$environment == Monstra::PRODUCTION) {
    error_reporting(0); 
} else {
    error_reporting(-1);
}
Monstra::init();