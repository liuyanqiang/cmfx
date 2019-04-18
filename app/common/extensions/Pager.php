<?php
namespace UI\Extensions;

/**
 * 数据分页的类
 */

class Pager
{
    /**
     * 显示的数字页码的个数
     */
    public $pagenum = 10;

    /**
     * 每页显示多少条记录
     */
    public $pagesize = 10;

    /**
     * 第几页
     */
    public $pageindex = 1;

    /**
     * 记录条数
     */
    public $recordcount = 0;

    /**
     * 以下属性值经过计算得到
     */
    protected $_pagecount; // 总页数
    protected $_pagefrom; // 显示的页码开始
    protected $_pageto; // 显示的页码结页
    protected $_pageprev; // 上一页
    protected $_pagenext; // 下一页
    protected $_offset; // 数据偏移值

    /**
     * 构造
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }
            $this->$key = intval($value);
        }
    }

    /**
     * 魔术方法 (支持获取 protected 属性值)
     */
    public function __get($name)
    {
        $property = '_' . strtolower($name);
        $method   = 'get' . ucfirst($name);
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        throw new \Exception("Class [Pager] has not property '{$property}' and method '{$method}'.");
    }

    /**
     * 计算分页
     */
    public function paginate()
    {
        if ($this->pagesize <= 0) {
            $this->pagesize = 20;
        }

        if (0 == $this->recordcount % $this->pagesize) {
            $this->_pagecount = $this->recordcount / $this->pagesize;
        } else {
            $this->_pagecount = floor($this->recordcount / $this->pagesize) + 1;
        }

        if ($this->pageindex > $this->_pagecount) {
            $this->pageindex = $this->_pagecount;
        }

        if ($this->pageindex < 1) {
            $this->pageindex = 1;
        }

        $this->_offset = ($this->pageindex - 1) * $this->pagesize;

        $numquot         = floor(($this->pageindex - 1) / $this->pagenum);
        $this->_pagefrom = $numquot * $this->pagenum + 1;
        $this->_pageto   = $this->_pagefrom + $this->pagenum - 1;
        if ($this->_pageto > $this->_pagecount) {
            $this->_pageto = $this->_pagecount;
        }

        $this->_pageprev = ($this->pageindex > 1) ? ($this->pageindex - 1) : 1;
        $this->_pagenext = ($this->pageindex < $this->_pagecount) ? ($this->pageindex + 1) : $this->_pagecount;

    }
}
