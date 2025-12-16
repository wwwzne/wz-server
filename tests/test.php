<?php
require_once(__DIR__ . "/../src/wzServer.php");

use Wwwzne\WzServer\wzServer;

wzServer::define([
    "/" => fn() => "a",
    "/a" => fn() => "b",
    "/1" => fn() => "c",
    "get/a/a" => fn() => "d",
    "get/1/1" => fn() => "e",
    "post/b/b" => fn() => "f",
    "post/2/2" => fn() => "g",
    "/c/{name}" => fn(string $name) => $name,
    "/d/@name" => fn(string $name) => $name,
    "post/f/{name}" => fn(string $name) => $name,
    "post/g/@name" => fn(string $name) => $name,
    "/h/@name" => fn(string $n) => $n,
    "post|geti/@name" => fn(string $n) => $n,
    "get|post/j/@name" => fn(string $o) => $o,
    "get|post/k/@" => [new class { function f(string $i) { return $i; } }, 'f']
]);
wzServer::run();

