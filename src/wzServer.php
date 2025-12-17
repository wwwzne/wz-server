<?php
/*  888       888   888       888   888       888  */
/*  888   o   888   888   o   888   888   o   888  */
/*  888  d8b  888   888  d8b  888   888  d8b  888  */
/*  888 d888b 888   888 d888b 888   888 d888b 888  */
/*  888d88888b888   888d88888b888   888d88888b888  */
/*  88888P Y88888   88888P Y88888   88888P Y88888  */
/*  8888P   Y8888   8888P   Y8888   8888P   Y8888  */
/*  888P     Y888   888P     Y888   888P     Y888  */
/*                                                 */
/*  888888888888P   d888b    888b   d088888888889  */
/*         d888P    d8888b   888b   d0888b         */
/*        d888P     d88888b  888b   d0888b         */
/*       d888P      d888Y88b 888b   d08888888b     */
/*      d888P       d888 Y88b888b   d0888b999b     */
/*     d888P        d888  Y88888b   d0888b         */
/*    d888P         d888   Y8888b   d0888b         */
/*  d888888888888   d888    Y888b   d088888888889  */
declare(strict_types=1);

namespace Wwwzne\WzServer;

use InvalidArgumentException;
use function array_slice;
use function call_user_func_array;
use function is_callable;
use function is_string;
use function strlen;

require_once 'utils.php';

final class wzServer
{
    public function __construct() { }

    private function __clone() { }

    /*正则get路由存储*/
    private static array $_getRegexRoutes = [];
    /*正则post路由存储*/
    private static array $_postRegexRoutes = [];
    /*页面渲染结果*/
    private static string $_page = "";
    /*404错误页面*/
    private static string $_404 = <<<HTML
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
    /*静态资源*/
    private static ?string $static = null;


    private/*模式匹配*/ static function templateToPattern(string $url): string
    {
        if (strlen($url) === 0 or $url[0] !== '/') $url = "/" . $url;

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
            } elseif (preg_match('/^\{[A-Za-z_][A-Za-z0-9_]*}$/', $seg)) {
                $parts[] = '([^/]+)';
            } else {
                $parts[] = preg_quote($seg, '#');
            }
        }
        return '#^/' . implode('/', $parts) . '$#';
    }

    public/*增加get路由规则*/ static function get(string $url, callable|string $fn): void
    {
        if (is_string($fn) || is_callable($fn)) {
            if (strlen($url) === 0) $url = '/';
            $pattern = self::templateToPattern($url);
            self::$_getRegexRoutes[] = ['pattern' => $pattern, 'handler' => $fn];
        } else throw new InvalidArgumentException("$url error");
    }

    public/*增加post路由规则*/ static function post(string $url, callable|string $fn): void
    {
        if (is_string($fn) || is_callable($fn)) {
            if (strlen($url) === 0) $url = '/';
            $pattern = self::templateToPattern($url);
            self::$_postRegexRoutes[] = ['pattern' => $pattern, 'handler' => $fn];
        } else throw new InvalidArgumentException("$url error");
    }

    public/*批量定义路由*/ static function define(array $i): void
    {
        foreach ($i as $j => $k) {
            if (!is_string($j)) {
                continue;
            } elseif (utils::get_post_start($j) || utils::post_get_start($j)) {
                self::get(trim(substr($j, 8)), $k);
                self::post(trim(substr($j, 8)), $k);
            } elseif (utils::get_start($j)) {
                self::get(trim(substr($j, 3)), $k);
            } elseif (utils::post_start($j)) {
                self::post(trim(substr($j, 4)), $k);
            } else {
                self::get($j, $k);
            }
        }
    }

    public/*静态路由*/ static function setStatic(string $path)
    {
        self::$static = $path;
    }

    public/*启动路由监控*/ static function run(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if (self::$static and str_starts_with($current, self::$static)) {
            $path = dirname($_SERVER['DOCUMENT_ROOT']) . $current;
            if (is_file($path)) {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                $mime = [
                    // 图片
                    'png' => 'image/png',
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    'svg' => 'image/svg+xml',
                    'ico' => 'image/x-icon',
                    'bmp' => 'image/bmp',
                    // 样式/脚本
                    'css' => 'text/css',
                    'js' => 'application/javascript',
                    'mjs' => 'application/javascript',
                    // 字体
                    'woff' => 'font/woff',
                    'woff2' => 'font/woff2',
                    'ttf' => 'font/ttf',
                    'otf' => 'font/otf',
                    // 文档/数据
                    'html' => 'text/html; charset=utf-8',
                    'htm' => 'text/html; charset=utf-8',
                    'txt' => 'text/plain; charset=utf-8',
                    'json' => 'application/json',
                    'xml' => 'application/xml',
                    'pdf' => 'application/pdf',
                    // 音频/视频
                    'mp3' => 'audio/mpeg',
                    'wav' => 'audio/wav',
                    'ogg' => 'audio/ogg',
                    'mp4' => 'video/mp4',
                    'webm' => 'video/webm',
                    'mov' => 'video/quicktime',
                    // 压缩包
                    'zip' => 'application/zip',
                    'gz' => 'application/gzip',
                    'tar' => 'application/x-tar',
                ];
                header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
                header('Content-Length: ' . filesize($path));
                readfile($path);
            } else echo self::$_404;
            exit;
        }
        $fn = fn() => self::$_404;
        $matches = [];
        if ($method === 'GET') {
            foreach (array_reverse(self::$_getRegexRoutes) as $r) {
                if (preg_match($r['pattern'], $current, $m)) {
                    [$fn, $matches] = [$r['handler'], $m];
                    break;
                }
            }
        } elseif ($method === 'POST') {
            foreach (array_reverse(self::$_postRegexRoutes) as $r) {
                if (preg_match($r['pattern'], $current, $m)) {
                    [$fn, $matches] = [$r['handler'], $m];
                    break;
                }
            }
        }
        self::$_page = call_user_func_array($fn, array_slice($matches, 1));
        echo self::$_page;
    }
}