<?php
namespace UI\Extensions;

/**
 * 搜索辅助类
 * 整理搜索条件供后端查询，生成查询表单供前端显示
 */

class Searcher
{
    /**
     * 搜索字段名的前缀
     */
    protected $_prefix = 's_';

    /**
     * 数据的字段数据
     */
    protected $_fields = [];

    /**
     * 构造
     */
    public function __construct(array $fields = [])
    {
        $this->setFields($fields);
    }

    /**
     * 设置字段名前缀
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = strval($prefix);
    }

    /**
     * 获取字段名前缀
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    /**
     * 获取查询字符串
     * @param boolean $refresh 是否强制刷新
     * @return string 返回url中的 querystring 部分
     */
    public function getQueryString($refresh = false)
    {
        static $qs = null;
        if ($refresh || is_null($qs)) {
            $arr = [];
            foreach ($this->_fields as $field => $v) {
                $field = $this->_prefix . $field;
                if (isset($v['input'])) {
                    $arr[] = $field . '=' . urlencode($v['input']);
                }
            }
            $qs = implode('&', $arr);
        }
        return $qs;
    }

    /**
     * 设置搜索字段
     * @param array $fields 搜索字段的设置数据
     * @param boolean 是否先清空已有的字段设置
     */
    public function setFields(array $fields, $clean = false)
    {
        if ($clean) {
            $this->_fields = [];
        }

        foreach ($fields as $key => $data) {
            if (!is_array($data) || empty($data)) {
                continue;
            }
            if (!isset($data['type'])) {
                $data['type'] = 'text';
            }
            if (!isset($data['label'])) {
                $data['label'] = $key;
            }
            $this->_fields[$key] = $data;
        }
    }

    /**
     * 获取字段设置
     * @return array 各字段设置
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * 支持对字段的具体属性设置
     *
     * 例子:
     *  $this->setFieldsType('category', 'select');
     *  $this->setFieldsType([ 'phone' => 'text', category' => 'select' ]);
     *  $this->setFieldsData([ 'category' => $categoryList ]);
     */
    public function __call($method, $args)
    {
        if (strncmp('setFields', $method, 9) != 0) {
            throw new \Exception(
                sprintf('%s::%s is not implemented', __CLASS__, $method)
            );
        }

        $argc = count($args);

        if ($argc == 1 && is_array($args[0])) {
            $data = &$args[0];
        } elseif ($argc == 2 && is_string($args[0])) {
            $data = [
                $args[0] => $args[1],
            ];
        }

        if (!isset($data)) {
            throw new \Exception(
                sprintf('%s::%s invalid arguments', __CLASS__, $method)
            );
        }

        $property = strtolower(substr($method, 9));

        foreach ($data as $field => $value) {
            if (!isset($this->_fields[$field])) {
                throw new \Exception(
                    sprintf('%s::%s field %s is not set', __CLASS__, $method, $field)
                );
            }
            $this->_fields[$field][$property] = $value;
        }
    }

    /**
     * 获取查询条件
     * $paramGetter callable 获取参数的方法, 比如在控制器中传入: [$this->request, 'get']
     * @return array 用于数据过滤的查询条件
     */
    public function getCondition($paramGetter)
    {
        $cond = [];
        foreach ($this->_fields as $field => &$data) {

            $sfield  = $this->_prefix . $field;
            $filter  = isset($data['filter']) ? $data['filter'] : null;
            $default = isset($data['default']) ? $data['default'] : null;

            $input = call_user_func_array($paramGetter, [$sfield, $filter, $default]);

            if (!is_null($input) && $input !== '') {
                
                // 记录当前搜索值，在页面搜索表单中显示
                $data['input'] = $input;

                // 记录字段的搜索值，在查询时过滤
                $cond[$field] = $input;
            }
        }
        return $cond;
    }

}
