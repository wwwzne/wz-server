<?php

require_once(__DIR__ . "/src/main.php");

use Src\Router;

$test = function () {
    return "<h1 align='center'>holle world</h1>";
};

$router = new Router;
$router->get("/", $test);

$router->run();
