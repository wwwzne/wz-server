# wzServer

wwwzne的轻量服务器框架

## 安装与引入

```shell
composer require wwwzne/wz-server
```

## 主要模块

1. 初始化对象Wz_config
2. 路由控制器Route
3. 数据库模型Modal
4. 视图及其控制器Controller
5. 工具函数集合Tools
6. 图像处理对象(待做)
7. JSON转译器(待做)
8. 注解与文档注释(待做)
9. 文件上传(待做)
10. 实时通信(待做)
11. 日志管理(待做)

## 数据库连接

```php
/*
参数 string $name 数据库名
参数 DatabaseType $type 数据库类型
参数 mixed $dsn 数据库链接信息
参数 mixed $user 用户名
参数 mixed $psw 密码
返回值 PDO对象实例
*/
$Wz_config->connect()
```

## 路由控制器

### 创建Router类

* 无参数创建

```php
$router = new Router;
$router->run();
```

* 带参数创建

```php
$router = new Router([ "GET/" => [$h, 'run'] ]);
// 等效于
$router = new Router;
$router->define([ "GET/" => [$h, 'run'] ])->run();
```

### 路由字符串

```php
$router->define([ "GET/" => [$h, 'run'] ]);
$router->define([ "get/" => [$h, 'run'] ]);
$router->define([ "GET" => [$h, 'run'] ]);
$router->define([ "POST/" => [$h, 'run'] ]);
$router->define([ "post/" => [$h, 'run'] ]);
$router->define([ "post" => [$h, 'run'] ]);
$router->define([ "GET|POST/" => [$h, 'run'] ]);
$router->define([ "get|post/" => [$h, 'run'] ]);
$router->define([ "POST|GET/" => [$h, 'run'] ]);
$router->define([ "post|get/" => [$h, 'run'] ]);
$router->define([ "get/[0-9]" => [$h, 'run'] ]);
$router->define([ "get/@name/@id" => [$h, 'run'] ])
$router->define([ "get/{name}/{id}" => [$h, 'run'] ])
```

### get请求监控

参数为函数名

```php
function test()
{
    return "<h1 align='center'>holle world</h1>";
};
$router->get("/", "test");
```

参数为匿名函数

```php
$test= function()
{
    return "<h1 align='center'>holle world</h1>";
};
$router->get("/", $test);
```

参数为类回调函数

```php
$router->get("/", [
    new class{
        public function a(){}
    },
    'a'
]);
```

### post请求监控

```php

```

## MVC模式

modal: 模型代表一个存取数据的对象
view: 视图代表模型包含的数据的可视化
control: 控制器控制模型数据作用于视图

```php
$data = new Model("test"); 
$h = new class extends Controller {
    public function index()
    {
        return $this->render('<?= $a ?>', ["a" => 1]);
    }
};
// 数据模型->继承并实现控制器类(内置render函数负责渲染视图)->绑定路由->页面输出
```
