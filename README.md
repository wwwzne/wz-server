# wzServer

wwwzne的轻量服务器框架

## 安装与引入

```shell
composer require wwwzne/wz-server
```

## 主要模块

1. 主要对象wzServer(路由管理已完成)
2. 工具函数集合utils
3. 图像处理对象(待做)
4. JSON转译器(待做)
5. 注解与文档注释(待做)
6. 文件上传(待做)
7. 实时通信(待做)
8. 日志管理(待做)

## 路由表设置

```php
wzServer::defind(["/" => fn() => "a"])
wzServer::define([ "GET/" => [$h, 'run'] ]);
wzServer::define([ "get/" => [$h, 'run'] ]);
wzServer::define([ "GET" => [$h, 'run'] ]);
wzServer::define([ "POST/" => [$h, 'run'] ]);
wzServer::define([ "post/" => [$h, 'run'] ]);
wzServer::define([ "post" => [$h, 'run'] ]);
wzServer::define([ "GET|POST/" => [$h, 'run'] ]);
wzServer::define([ "get|post/" => [$h, 'run'] ]);
wzServer::define([ "POST|GET/" => [$h, 'run'] ]);
wzServer::define([ "post|get/" => [$h, 'run'] ]);
wzServer::define([ "get/[0-9]" => [$h, 'run'] ]);
wzServer::define([ "get/@name/@id" => [$h, 'run'] ])
wzServer::define([ "get/{name}/{id}" => [$h, 'run'] ])
WzServer::run();
```

## 设置get请求

```php
// 函数回调
function test()
{
    return "<h1 align='center'>holle world</h1>";
};
wzServer::get("/", "tests");
// 匿名函数回调
$test= function()
{
    return "<h1 align='center'>holle world</h1>";
};
wzServer::get("/", $test);
// 类回调函数
wzServer::get("/", [
    new class{
        public function a(){}
    },'a'
]);
```

## 设置post请求

```php
// 函数回调
function test()
{
    return "<h1 align='center'>holle world</h1>";
};
wzServer::post("/", "tests");
// 匿名函数回调
$test= function()
{
    return "<h1 align='center'>holle world</h1>";
};
wzServer::post("/", $test);
// 类回调函数
wzServer::post("/", [
    new class{
        public function a(){}
    },'a'
]);
```