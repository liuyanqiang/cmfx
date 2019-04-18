<?php
namespace Cmfx\Mvc\Collection;
use ArrayIterator;

/**
 * 应用类型映射到文档的迭代器
 */
class TypeMapArrayIterator extends ArrayIterator
{
    private $typeMap;

    /**
     * 构造函数
     *
     * @param array $documents 文档数组
     * @param array $typeMap 类型映射
     */
    public function __construct(array $documents = [], array $typeMap)
    {
        parent::__construct($documents);
        $this->typeMap = $typeMap;
    }

    /**
     * 返回应用类型映射转换的当前文档数据
     */
    public function current()
    {
        return \MongoDB\BSON\toPHP(
            \MongoDB\BSON\fromPHP(parent::current()),
            $this->typeMap
        );
    }
}
