<?php
namespace Cmfx\Assets;

/**
 * 资源管理类 (js、css等)
 */
class Manager
{
    /**
     * 默认组名
     */
    const DEFAULT_GROUP = 'default';

    /**
     * js 资源
     */
    protected $_js = [];

    /**
     * css 资源
     */
    protected $_css = [];

    /**
     * public 目录的系统路径 (assets的上级目录)
     * 当设置 $_publicPath 时才可以按组打包和压缩 js、css 文件
     */
    protected $_publicPath = false;

    /**
     * 静态位置
     * 当设置后，所有 js 和 css 的引用输出都附加 staticUrl 前缀
     */
    protected $_staticUrl = '';

    /**
     * 是否按组打包 (false,不打包; string, 打包文件的存放目录)
     */
    protected $_package = false;

    /**
     * 是否压缩文件
     */
    protected $_compress = false;

    /**
     * 设置公共目录的系统路径
     * @param $path 公共目录所在的系统路径
     */
    public function setPublicPath($path)
    {
        $this->_publicPath = realpath($path);
    }

    /**
     * 设置静态位置
     * @param $url 资源文件所在的 url 位置 (比如: http://www.cdn.com)
     */
    public function setStaticUrl($url)
    {
        if (empty($url)) {
            $this->_staticUrl = '';
        } else {
            $this->_staticUrl = rtrim($url, '/');
        }
    }

    /**
     * 打包设置
     * @param string|boolean $path 目录名称 或 取消打包
     */
    public function setPackage($path)
    {
        if (false === $path) {
            $this->_package = false;
        } elseif (true === $path) {
            $this->_package = '/assets/package';
        } elseif (is_string($path)) {
            if (!empty($path) && !empty($path = trim($path, '/'))) {
                $this->_package = '/' . $path;
            }
        }
    }

    /**
     * 压缩设置
     * @param boolean $compress 是否压缩文件
     */
    public function setCompress($compress)
    {
        $this->_compress = $compress;
    }

    /**
     * 判断是否内联代码(或样式)
     * @param string $jscss 脚本(样式)的代码或文件路径
     * @param boolean 如果是内联代码(样式), 返回 true; 如果是文件路径，返回 false
     */
    protected function _isInline($jscss)
    {
        return strncmp($jscss, '/*', 2) === 0;
    }

    /**
     * 添加 js 脚本引用
     * @param string $group 组名
     * @param string | array $paths 路径 (数组或者单一文件路径)
     */
    public function addJs($group, $paths = null)
    {
        if (null === $paths) {
            $paths = $group;
            $group = self::DEFAULT_GROUP;
        }

        if (!isset($this->_js[$group])) {
            $this->_js[$group] = [];
        }

        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $path = '/' . ltrim($path, '/');
            if (!in_array($path, $this->_js[$group])) {
                $this->_js[$group][] = $path;
            }
        }
    }

    /**
     * 添加内联的 js 代码
     * @param string $group 组名
     */
    public function addJsStart($group = null)
    {
        if (empty($group) || !is_string($group)) {
            $group = self::DEFAULT_GROUP;
        }
        ob_start();
        echo "/* {$group} */\n";
    }

    /**
     * 添加内联的 js 代码结束
     */
    public function addJsEnd()
    {
        $jscode = ob_get_clean();
        if (preg_match('#^\/\* (\w+) \*\/\n#', $jscode, $match)) {
            $group = $match[1];
            if (!isset($this->_js[$group])) {
                $this->_js[$group] = [];
            }
            $this->_js[$group][] = $jscode;
        }
    }

    /**
     * 添加 css 脚本引用
     * @param string $group 组名
     * @param string | array $paths 路径 (数组或者单一文件路径)
     */
    public function addCss($group, $paths = null)
    {
        if (null === $paths) {
            $paths = $group;
            $group = self::DEFAULT_GROUP;
        }

        if (!isset($this->_css[$group])) {
            $this->_css[$group] = [];
        }

        if (!is_array($paths)) {
            $paths = [$paths];
        }

        foreach ($paths as $path) {
            $path = '/' . ltrim($path, '/');
            if (!in_array($path, $this->_css[$group])) {
                $this->_css[$group][] = $path;
            }
        }
    }

    /**
     * 添加内联的 css 样式
     * @param string $group 组名
     */
    public function addCssStart($group = null)
    {
        if (empty($group) || !is_string($group)) {
            $group = self::DEFAULT_GROUP;
        }
        ob_start();
        echo "/* {$group} */\n";
    }

    /**
     * 添加内联的 css 样式结束
     */
    public function addCssEnd()
    {
        $style = ob_get_clean();
        if (preg_match('#^\/\* (\w+) \*\/\n#', $style, $match)) {
            $group = $match[1];
            if (!isset($this->_css[$group])) {
                $this->_css[$group] = [];
            }
            $this->_css[$group][] = $style;
        }
    }

    /**
     * 按组输出 js 引用
     * @param string $group 组名
     */
    public function outputJs($group = null)
    {
        if (!is_string($group)) {
            $group = self::DEFAULT_GROUP;
        }

        if (!isset($this->_js[$group])) {
            return;
        }

        if (!$this->_package) {
            foreach ($this->_js[$group] as $js) {
                if ($this->_isInline($js)) {
                    echo "<script type=\"text/javascript\">\n";
                    echo substr($js, strpos($js, "\n") + 1);
                    echo "</script>\n";
                } else {
                    echo "<script type=\"text/javascript\" src=\"{$this->_staticUrl}{$js}\"></script>\n";
                }
            }
        } else {

            if (false === $this->_publicPath) {
                throw new \Cmfx\Exception('未设置公共目录 (public) 的系统路径');
            }

            $hash        = md5(implode('|', $this->_js[$group]));
            $packageFile = sprintf('%s/%s.js', $this->_package, $hash);
            $packagePath = $this->_publicPath . $packageFile;

            if (!file_exists($packagePath)) {
                $fp = fopen($packagePath, 'ab');
                if (!$fp) {
                    throw new \Cmfx\Exception('无法创建 js 打包文件');
                }
                foreach ($this->_js[$group] as $js) {
                    fwrite($fp, $this->_isInline($js) ? $js : file_get_contents($this->_publicPath . $js));
                    fwrite($fp, PHP_EOL);
                }
                fclose($fp);
            }

            echo "<script type=\"text/javascript\" src=\"{$this->_staticUrl}{$packageFile}\"></script>\n";
        }
    }

    /**
     * 按组输出 css 引用
     * @param string $group 组名
     */
    public function outputCss($group = null)
    {
        if (!is_string($group)) {
            $group = self::DEFAULT_GROUP;
        }

        if (!isset($this->_css[$group])) {
            return;
        }

        if (!$this->_package) {
            foreach ($this->_css[$group] as $css) {
                if ($this->_isInline($css)) {
                    echo "<style type=\"text/css\">\n";
                    echo substr($css, strpos($css, "\n") + 1);
                    echo "</style>\n";
                } else {
                    echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"{$this->_staticUrl}{$css}\" />\n";
                }
            }
        } else {

            if (false === $this->_publicPath) {
                throw new \Cmfx\Exception('未设置公共目录 (public) 的系统路径');
            }

            $hash        = md5(implode('|', $this->_css[$group]));
            $packageFile = sprintf('%s/%s.css', $this->_package, $hash);
            $packagePath = $this->_publicPath . $packageFile;

            if (!file_exists($packagePath)) {
                $fp = fopen($packagePath, 'ab');
                if (!$fp) {
                    throw new \Cmfx\Exception('无法创建 css 打包文件');
                }
                foreach ($this->_css[$group] as $css) {
                    fwrite($fp, $this->_isInline($css) ? $css : file_get_contents($this->_publicPath . $css));
                    fwrite($fp, PHP_EOL);
                }
                fclose($fp);
            }

            echo "<link type=\"text/css\" rel=\"stylesheet\" href=\"{$this->_staticUrl}{$packageFile}\" />\n";
        }
    }
}
