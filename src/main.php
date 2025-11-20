<?php

namespace Src;

use function PHPSTORM_META\type;

$author = trim("
888       888   888       888   888       888
888   o   888   888   o   888   888   o   888
888  d8b  888   888  d8b  888   888  d8b  888
888 d888b 888   888 d888b 888   888 d888b 888
888d88888b888   888d88888b888   888d88888b888
88888P Y88888   88888P Y88888   88888P Y88888
8888P   Y8888   8888P   Y8888   8888P   Y8888
888P     Y888   888P     Y888   888P     Y888

888888888888P   d888b    888b   d088888888889
       d888P    d8888b   888b   d0888b       
      d888P     d88888b  888b   d0888b       
     d888P      d888Y88b 888b   d08888888b   
    d888P       d888 Y88b888b   d0888b999b    
   d888P        d888  Y88888b   d0888b       
  d888P         d888   Y8888b   d0888b       
d888888888888   d888    Y888b   d088888888889
");

class Model
{
}

class View
{
}

class Controller
{
    public function __construct()
    {
    }
}

class Router //路由控制器
{
    public $getRoutes = [];
    public $postRoutes = [];
    public $_404 = null;

    public function __construct()
    {
        $this->_404 = fn() => print "404";
    }

    public function get(string $url, callable|string $fn)
    {
        if (\is_string($fn) || \is_callable($fn)) $this->getRoutes[$url] = $fn;
        else throw new \InvalidArgumentException("Unsupported route handler type for {$url}");
        return $this;
    }

    public function push(string $url, callable|string $fn)
    {
        if (\is_string($fn) || \is_callable($fn)) $this->postRoutes[$url] = $fn;
        else throw new \InvalidArgumentException("Unsupported route handler type for {$url}");
        return $this;
    }

    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $fn = null;
        if ($method === 'GET' && isset($this->getRoutes[$current])) $fn = $this->getRoutes[$current];
        elseif ($method === 'POST' && isset($this->postRoutes[$current])) $fn = $this->postRoutes[$current];
        else $fn = $this->error_404();
        echo \call_user_func($fn);
    }

    public function error_404()
    {
        return $this->_404;
    }
}
