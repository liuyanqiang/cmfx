<?php
namespace UI\Extensions;

/**
 * mongo 简单操作类
 *
 * 主要用于快速备份和恢复数据，正常的数据操作使用 \Cmfx\Mvc\Collection,
 * 如需要增加更多功能可参考 https://github.com/mongodb/mongo-php-library
 */
class Mongo
{
    /**
     * mongo 的链接管理 (\MongoDB\Driver\Manager)
     */
    protected $_mongo;

    /**
     * 数据库名称
     */
    protected $_database;

    /**
     * constructor
     */
    public function __construct($connection)
    {
        $this->_mongo = new \MongoDB\Driver\Manager($connection);
    }

    /**
     * 选择 database
     */
    public function selectDatabase($database)
    {
        $this->_database = $database;
    }

    /**
     * 返回 database 名称
     */
    public function getDatabase()
    {
        return $this->_database;
    }

    /**
     * 解析查询参数
     * @param array &$filter 查询过滤项(引用输出)
     * @param array &$options 查询设置项(引用输出)
     * @param array $parameters 输入的查询参数
     */
    protected function parseParameters(&$filter, &$options, array $parameters = null)
    {
        if (is_null($parameters)) {
            $parameters = [];
        }

        $filter = [];
        if (isset($parameters[0])) {
            $filter = $parameters[0];
            unset($parameters[0]);
        }
        $options = $parameters;

        if (isset($filter['_id']) && is_string($filter['_id']) && preg_match('/^[a-f\d]{24}$/i', $filter['_id'])) {
            $filter['_id'] = new \MongoDB\BSON\ObjectId($filter['_id']);
        }
    }

    /**
     * 查询所有集合
     */
    public function listCollections(array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $cmd = ['listCollections' => 1];

        if (!empty($filter)) {
            $cmd['filter'] = $filter;
        }

        if (isset($options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $options['maxTimeMS'];
        }

        $database = $this->getDatabase();

        $cursor = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    /**
     * 创建集合
     */
    public function createCollection($collection, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $cmd = ['create' => $collection];

        foreach (['autoIndexId', 'capped', 'flags', 'max', 'maxTimeMS', 'size', 'validationAction', 'validationLevel'] as $option) {
            if (isset($options[$option])) {
                $cmd[$option] = $options[$option];
            }
        }

        foreach (['collation', 'indexOptionDefaults', 'storageEngine', 'validator'] as $option) {
            if (isset($options[$option])) {
                $cmd[$option] = (object) $options[$option];
            }
        }

        $database = $this->getDatabase();

        $cursor = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    /**
     * 删除集合
     */
    public function dropCollection($collection)
    {
        $cmd = [
            'drop' => $collection,
        ];

        $database = $this->getDatabase();

        $cursor = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    /**
     * 查询索引
     */
    public function listIndexes($collection, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $cmd = ['listIndexes' => $collection];

        if (isset($options['maxTimeMS'])) {
            $cmd['maxTimeMS'] = $options['maxTimeMS'];
        }

        $database = $this->getDatabase();

        $cursor = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    /**
     * 创建索引
     */
    public function createIndexes($collection, array $indexes, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $cmd = [
            'createIndexes' => $collection,
            'indexes'       => $indexes,
        ];

        $database = $this->getDatabase();

        $cursor = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    /**
     * 删除索引
     */
    public function dropIndexes($collection, $indexName, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $cmd = [
            'dropIndexes' => $collection,
            'index'       => $indexName,
        ];

        $database = $this->getDatabase();

        $cursor = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array']);

        return $cursor->toArray();
    }

    /**
     * 查询文档
     */
    public function find($collection, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $query = new \MongoDB\Driver\Query($filter, $options);

        $database  = $this->getDatabase();
        $namespace = sprintf('%s.%s', $database, $collection);

        $cursor = $this->_mongo->executeQuery($namespace, $query);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

        return $cursor;
    }

    /**
     * 创建文档
     */
    public function create($collection, $data)
    {
        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->insert($data);

        $database  = $this->getDatabase();
        $namespace = sprintf('%s.%s', $database, $collection);

        $result = $this->_mongo->executeBulkWrite($namespace, $bulk);
        return empty($result->getWriteErrors());
    }

    /**
     * 删除文档
     * @param array $parameters 查询参数
     * @return integer 删除文件数目
     */
    public static function remove($collection, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->delete($filter, $options);

        $database  = $this->getDatabase();
        $namespace = sprintf('%s.%s', $database, $collection);

        $result = $this->_mongo->executeBulkWrite($namespace, $bulk);
        return $result->getDeletedCount();
    }

    /**
     * 查询集合中的文档数目
     */
    public function count($collection, array $parameters = null)
    {
        $this->parseParameters($filter, $options, $parameters);

        $cmd = [];

        $cmd['count'] = $collection;
        $cmd['query'] = (object) $filter;

        if (isset($options['collation'])) {
            $cmd['collation'] = (object) $options['collation'];
        }

        foreach (['hint', 'limit', 'maxTimeMS', 'skip'] as $option) {
            if (isset($options[$option])) {
                $cmd[$option] = $options[$option];
            }
        }

        $database = $this->getDatabase();
        $cursor   = $this->_mongo->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $result   = current($cursor->toArray());

        if (!isset($result->n) || !(is_integer($result->n) || is_float($result->n))) {
            throw new Exception('count 未返回数字型的 "n" 数值');
        }

        return intval($result->n);
    }
}
