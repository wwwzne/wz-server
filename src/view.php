<?php
declare(strict_types=1);

namespace Wwwzne\WzServer;

final class view
{
    /*文件路径*/
    private string $file;
    /*参数列表*/
    private array $data;
    /*渲染结果*/
    private ?string $page = null;

    public function __construct(string $path, array $params = [])
    {
        $this->file = $this->resolvePath($path);
        $this->data = $params;
    }

    /**
     * 追加/覆盖模板参数
     */
    public function with(array $params): self
    {
        $this->data = $params + $this->data;
        // 变更参数后清理缓存的渲染结果
        $this->page = null;
        return $this;
    }

    /**
     * 渲染模板并返回字符串
     */
    public function render(): string
    {
        if ($this->page !== null) return $this->page;

        if (!is_file($this->file) || !is_readable($this->file)) {
            return '';
        }

        // 将参数导入到模板作用域（变量名与键一致）
        if (!empty($this->data)) {
            extract($this->data, EXTR_SKIP);
        }

        ob_start();
        try {
            include $this->file;
        } finally {
            $this->page = ob_get_clean();
        }
        return $this->page;
    }

    /**
     * echo 对象时输出页面内容
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * 解析模板路径：
     * - 绝对路径或存在的真实路径直接使用
     * - 相对路径优先相对于入口脚本目录，其次相对于 DOCUMENT_ROOT
     */
    private function resolvePath(string $path): string
    {
        $path = trim($path);
        $real = @realpath($path);
        if ($real && is_file($real)) return $real;

        $scriptDir = dirname($_SERVER['SCRIPT_FILENAME']);
        $candidate = $scriptDir . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        $real = @realpath($candidate);
        if ($real && is_file($real)) return $real;

        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? $scriptDir, '/\\');
        $candidate = $docRoot . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
        $real = @realpath($candidate);
        return ($real && is_file($real)) ? $real : $candidate;
    }
}
