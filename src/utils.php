<?php

namespace Wwwzne\WzServer;


final class utils
{
    public/*判断字符串开头是否为post*/ static function post_start(string $i): bool { return !(\strlen($i) < 4) && strtolower(substr($i, 0, 4)) === "post"; }

    public/*判断字符串开头是否为get*/ static function get_start(string $i): bool { return !(\strlen($i) < 3) && strtolower(substr($i, 0, 3)) === 'get'; }

    public/*判断字符串开头是否为get|post*/ static function get_post_start(string $i): bool { return !(\strlen($i) < 8) && strtolower(substr($i, 0, 8)) === 'get|post'; }

    public/*判断字符串开头是否为post|get*/ static function post_get_start(string $i): bool { return !(\strlen($i) < 8) && strtolower(substr($i, 0, 8)) === 'post|get'; }

    public/*打印bool值*/ static function echo_bool(bool|int $i): void { echo ($i ? "true" : "false") . PHP_EOL; }

    public/*http-get请求*/ static function http_get(string $url): string
    {

        $url = trim($url);
        $opts = ['http' => ['method' => 'GET', 'timeout' => 10]];
        $context = stream_context_create($opts);
        $body = @file_get_contents($url, false, $context);
        return $body === false ? '' : htmlentities($body);
    }

    public/*http-post请求*/ static function http_post(string $url, array $data = [], array $headers = []): string
    {
        $url = trim($url);
        $content = http_build_query($data);
        $hdrs = [
            'User-Agent: WzServer/1.0',
            'Content-Type: application/x-www-form-urlencoded',
            'Content-Length: ' . \strlen($content),
        ];
        foreach ($headers as $k => $v) $hdrs[] = \is_int($k) ? $v : "$k: $v";
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $hdrs) . "\r\n",
                'content' => $content,
                'timeout' => 10,
            ],
        ];
        $context = stream_context_create($opts);
        $body = @file_get_contents($url, false, $context);
        return $body === false ? '' : htmlentities($body);
    }
}