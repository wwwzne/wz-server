<?php

declare(strict_types=1);

namespace Wwwzne\WzServer;

use PDO, PDOException, Exception;

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
const _404_ = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户访问的页面不存在</title>
    <style>html{width:100%;height:100%}*{user-select:none}</style>
</head>
<body style="width:100%;height:100%;margin:0;padding:0;display:grid;place-items:center">
    <h1 style="font-size:5em;font-family:'Comic Sans MS',sans-serif;color:red">
        error:404
    </h1>
</body>
</html>
HTML;

abstract class Tools
{
    public static function error(int $code)
    {
        return [
            "404" => _404_
        ][$code];
    }
    public static $sendGet;
    public static function post_start(string $i)
    {
        return strtolower(substr($i, 0, 4)) === "post";
    }
    public static function get_start(string $i)
    {
        return strtolower(substr($i, 0, 3)) === 'get';
    }
    public static function get_post_start(string $i)
    {
        return strtolower(substr($i, 0, 8)) === 'get|post';
    }
    public static function post_get_start(string $i)
    {
        return strtolower(substr($i, 0, 8)) === 'post|get';
    }
    public static function echo_bool(bool|int $i)
    {
        echo $i ? "true" : "false" . PHP_EOL;
    }
};
enum DatabaseType: string
{
    case MYSQL  = 'mysql';
    case PGSQL  = 'pgsql';
    case SQLITE = 'sqlite';
}

$Wz_config = new class {
    /**
     * PDO对象
     * @var 
     */
    private ?PDO $instance = null;
    /**
     * 数据库类型
     * @var 
     */
    private ?DatabaseType $type = null;
    /**
     * 构造函数
     */
    public function __construct() {}
    /**
     * 数据库连接
     * @param string $name 数据库名
     * @param DatabaseType $type 数据库类型
     * @param mixed $dsn 数据库链接信息
     * @param mixed $user 用户名
     * @param mixed $psw 密码
     * @return PDO|null PDO对象
     */
    public function connect(string $name, DatabaseType $type = DatabaseType::MYSQL, ?string $dsn = null, ?string $user = null, ?string $psw = null): PDO
    {
        if ($this->instance !== null) return $this->instance;
        $this->type = $type;
        $dsn  ??= (getenv('DB_DSN') ?: [
            DatabaseType::PGSQL->value => "pgsql:host=127.0.0.1;port=5432;dbname={$name}",
            DatabaseType::SQLITE->value => "",
            DatabaseType::MYSQL->value => "mysql:host=127.0.0.1;port=3306;dbname={$name};charset=utf8mb4",
        ][$type->value]);
        $user ??=  (getenv('DB_USER') ?: 'root');
        $psw ??=  (getenv('DB_PASS') ?: 'root');
        $opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        try {
            $this->instance = new PDO($dsn, $user, $psw, $opts);
            return $this->instance;
        } catch (PDOException $e) {
            // 开发时直接抛出，生产环境可改为记录日志并抛出或返回 null
            throw $e;
        }
    }
    /**
     * 获取PDO对象
     * @return PDO|null
     */
    public function getInstance(): ?PDO
    {
        return $this->instance;
    }
    public function getDriverType(): string
    {
        return $this->type->value;
    }
};


final class Model
{
    public string $tableName = "";
    public function __construct(string $name)
    {
        $this->tableName = $name;
    }
    public function getdata()
    {
        global $Wz_config;
        if ($Wz_config->getInstance() === null) throw new Exception("数据库连接失败");
        $stmt = $Wz_config->getInstance()->prepare("SELECT * FROM `{$this->tableName}`");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $headers = $rows ? array_keys($rows[0]) : [];
        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
    public function query() {}
}

final class View
{
    private string $_page = "";
    public function __construct(string $_page)
    {
        $this->_page = $_page;
    }
    public function render(array $i = []): string
    {
        $vars = $i;
        extract($vars, EXTR_SKIP);
        ob_start();
        $tpl = $this->_page;
        eval("?>$tpl");
        return (string) ob_get_clean();
    }
}

class Controller
{
    final protected function render(string $template, array $data = [])
    {
        return new View($template)->render($data);
    }
}

final class Router //路由控制器
{
    /**
     * 正则get路由存储
     * @var array
     */
    private array $_getRegexRoutes = [];
    /**
     * 正则post路由存储
     * @var array
     */
    private array $_postRegexRoutes = [];
    /**
     * 页面渲染结果
     * @var string
     */
    private string $_page = "";
    private function templateToPattern(string $url): string
    {
        if (\strlen($url) === 0) $url = '/';
        if ($url[0] !== '/') $url = "/$url";

        // 逐段处理，普通段做 preg_quote，@name 或 {name} 变为 捕获组 ([^/]+)
        $segments = explode('/', ltrim($url, '/'));
        $parts = [];
        foreach ($segments as $seg) {
            if ($seg === '') {
                $parts[] = '';
                continue;
            }
            if ($seg[0] === '@') {
                $parts[] = '([^/]+)';
            } elseif (preg_match('/^\{[A-Za-z_][A-Za-z0-9_]*\}$/', $seg)) {
                $parts[] = '([^/]+)';
            } else {
                $parts[] = preg_quote($seg, '#');
            }
        }

        return '#^/' . implode('/', $parts) . '$#';
    }
    private function addGetRoutes(string $url, callable|string $fn)
    {
        if (\strlen($url) === 0) $url = '/';
        $pattern = $this->templateToPattern($url);
        $this->_getRegexRoutes[] = ['pattern' => $pattern, 'handler' => $fn];
    }
    private function addPostRoutes(string $url, callable|string $fn)
    {
        if (\strlen($url) === 0) $url = '/';
        $pattern = $this->templateToPattern($url);
        $this->_postRegexRoutes[] = ['pattern' => $pattern, 'handler' => $fn];
    }

    public function __construct(array $i = [])
    {
        $this->define($i);
    }

    public function get(string $url, callable|string $fn)
    {
        if (\is_string($fn) || \is_callable($fn)) $this->addGetRoutes($url, $fn);
        else throw new \InvalidArgumentException("{$url} error");
        return $this;
    }

    public function post(string $url, callable|string $fn)
    {
        if (\is_string($fn) || \is_callable($fn)) $this->addPostRoutes($url, $fn);
        else throw new \InvalidArgumentException("{$url} error");
        return $this;
    }
    public function define(array $i)
    {
        foreach ($i as $j => $k) {
            if (Tools::get_post_start($j) || Tools::post_get_start($j)) {
                $this->get(trim(substr($j, 8)), $k);
                $this->post(trim(substr($j, 8)), $k);
            } elseif (Tools::get_start($j)) $this->get(trim(substr($j, 3)), $k);
            elseif (Tools::post_start($j)) $this->post(trim(substr($j, 4)), $k);
            else $this->get($j, $k);
        }
        return $this;
    }
    /**
     * run启动路由监控
     * @return void
     */
    public function run()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $fn = fn() => Tools::error(404);
        $matches = [];
        if ($method === 'GET') {
            foreach (array_reverse($this->_getRegexRoutes) as $r) {
                if (preg_match($r['pattern'], $current, $m)) {
                    $fn = $r['handler'];
                    $matches = $m;
                    break;
                }
            }
        } elseif ($method === 'POST') {
            foreach (array_reverse($this->_postRegexRoutes) as $r) {
                if (preg_match($r['pattern'], $current, $m)) {
                    $fn = $r['handler'];
                    $matches = $m;
                    break;
                }
            }
        }
        $this->_page = \call_user_func_array($fn, \array_slice($matches, 1));
        echo $this;
    }
    /**
     * 辅助内容渲染
     * @return string
     */
    public function __tostring()
    {
        return $this->_page;
    }
}
