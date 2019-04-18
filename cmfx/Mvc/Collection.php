<?php
namespace Cmfx\Mvc;

use ArrayIterator;
use Cmfx\Mvc\Collection\Exception;
use Cmfx\Mvc\Collection\Manager;
use Cmfx\Mvc\Collection\TypeMapArrayIterator;
use Phalcon\Di;
use Phalcon\DiInterface;
use Phalcon\Di\InjectionAwareInterface;

abstract class Collection implements CollectionInterface, InjectionAwareInterface, \Serializable
{
    /**
     * find 方法返回类型
     */
    const FIND_TYPE_CLASS  = 1; // collection 实例 (默认)
    const FIND_TYPE_ARRAY  = 2; // 数组
    const FIND_TYPE_CURSOR = 3; // 游标

    /**
     * DI 依赖
     */
    protected $_dependencyInjector;

    /**
     * 链接管理实例
     */
    protected $_manager;

    /**
     * 集合名称
     */
    protected $_source;

    /**
     * 构造函数
     * @param DiInterface $dependencyInjector 注入依赖实例
     * @param Manager $collectionManager mongo 链接管理服务
     */
    final public function __construct(DiInterface $dependencyInjector = null, Manager $collectionManager = null)
    {
        if (is_null($dependencyInjector)) {
            $dependencyInjector = Di::getDefault();
        }

        $this->setDI($dependencyInjector);

        if (method_exists($this, 'onConstruct')) {
            // @note: 如果要设置 collection 类的 manager，可以在 onConstruct 方法中
            $this->onConstruct();
        }

        if (is_null($this->_manager)) {
            $this->setManager($collectionManager);
        }

        $this->_manager->initialize($this);
    }

    /**
     * 设置 DI
     */
    public function setDI(DiInterface $dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    /**
     * 返回 DI
     */
    public function getDI()
    {
        return $this->_dependencyInjector;
    }

    /**
     * 静态方法返回 DI
     */
    public static function DI()
    {
        return \Phalcon\Di::getDefault();
    }

    /**
     * 设置 mongo 的链接管理
     * @param string|Manager $manager 服务名称或实例
     */
    public function setManager($manager = null)
    {
        if (empty($manager)) {
            $manager = 'mongo';
        }

        if (is_string($manager)) {
            $this->_manager = $this->_dependencyInjector->getShared($manager);
        } elseif ($manager instanceof Manager) {
            $this->_manager = $manager;
        }
    }

    /**
     * 返回 mongo 的链接管理服务实例
     */
    public function getManager()
    {
        return $this->_manager;
    }

    /**
     * 设置集合(collection)名称
     * @param string @source 集合(collection)名称
     */
    protected function setSource($source)
    {
        $this->_source = $source;
        return $this;
    }

    /**
     * 返回集合(collection)名称
     */
    public function getSource()
    {
        if (is_null($this->_source)) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $class = substr($class, $pos + 1);
            }
            // 把以驼峰式命名的类名转换为下划线连接的集合名称
            $this->_source = strtolower(preg_replace('/([A-Z])/', '_\\0', lcfirst($class)));
        }
        return $this->_source;
    }

    /**
     * 解析查询参数
     * @param array &$filter 查询过滤项(引用输出)
     * @param array &$options 查询设置项(引用输出)
     * @param array $parameters 输入的查询参数
     */
    protected static function parseParameters(&$filter, &$options, array $parameters = null)
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
     * 按 _id 查询一个文档
     * @param ObjectId | string $id 要查询的 _id 值
     * @return CollectionInterface 实例, 无相关文档返回 false
     */
    public static function findById($id)
    {
        return static::findFirst([['_id' => $id]]);
    }

    /**
     * 按 _id 查询一个文档
     * @param ObjectId | string $id 要查询的 _id 值
     * @return CollectionInterface 实例, 无相关文档返回 false
     */
    public static function findFirstById($id)
    {
        return static::findFirst([['_id' => $id]]);
    }

    /**
     * 查询第一个符合条件的文档
     * @param array $parameters 查询参数
     * @return CollectionInterface 实例, 无相关文档返回 false
     */
    public static function findFirst(array $parameters = null)
    {
        $parameters['limit'] = 1;

        $rows = static::find($parameters);
        if (count($rows) == 0) {
            return false;
        }

        return $rows[0];
    }

    /**
     * 查询符合条件的文档
     * @param array $parameters 查询参数
     * @param integer $findType 返回类型 (FIND_TYPE_CLASS, FIND_TYPE_ARRAY, FIND_TYPE_CURSOR)
     * @return array | cursor, 根据 $findType 参数返回相应数组数据或游标
     */
    public static function find(array $parameters = null, $findType = self::FIND_TYPE_CLASS)
    {
        self::parseParameters($filter, $options, $parameters);

        $query = new \MongoDB\Driver\Query($filter, $options);

        $model     = new static();
        $conn      = $model->getManager()->getMongoManger();
        $namespace = sprintf('%s.%s', $model->getManager()->getDatabase(), $model->getSource());

        $cursor = $conn->executeQuery($namespace, $query);

        if ($findType == self::FIND_TYPE_CLASS) {
            $rows = [];
            foreach ($cursor as $c) {
                $m = clone $model;
                $m->fromArray(get_object_vars($c));
                $rows[] = $m;
            }
            return $rows;
        }

        if ($findType == self::FIND_TYPE_ARRAY) {
            return $cursor->toArray();
        }

        return $cursor;
    }

    /**
     * 聚合查询
     * @param array $parameters 查询参数
     * @return array | cursor, 根据 $options 参数返回相应数组数据或游标
     */
    public static function aggregate(array $parameters = null)
    {
        // 键为整数的参数皆放入 $pipeline, 否则视为 $options
        if (is_null($parameters)) {
            $parameters = [];
        }

        $pipeline = [];
        $options  = [
            'allowDiskUse' => false,
            'useCursor'    => false,
        ];

        foreach ($parameters as $k => $v) {
            if (is_int($k)) {
                $pipeline[] = $v;
            } else {
                $options[$k] = $v;
            }
        }

        // 数据库和集合
        $model      = new static();
        $conn       = $model->getManager()->getMongoManger();
        $database   = $model->getManager()->getDatabase();
        $collection = $model->getSource();

        // 创建聚合命令
        $cmd = [];

        $cmd['aggregate']    = $collection;
        $cmd['pipeline']     = $pipeline;
        $cmd['allowDiskUse'] = $options['allowDiskUse'];

        if ($options['useCursor']) {
            $cmd['cursor'] = isset($options["batchSize"])
            ? ['batchSize' => $options["batchSize"]]
            : new \stdClass;
        }

        if (isset($options['collation'])) {
            $cmd['collation'] = (object) $options['collation'];
        }

        foreach (['maxTimeMS'] as $option) {
            if (isset($options[$option])) {
                $cmd[$option] = $options[$option];
            }
        }

        // 执行命令
        $cursor = $conn->executeCommand($database, new \MongoDB\Driver\Command($cmd));

        // 返回结果
        if ($options['useCursor']) {
            if (isset($options['typeMap'])) {
                $cursor->setTypeMap($options['typeMap']);
            }
            return $cursor;
        }

        $result = current($cursor->toArray());

        if (!isset($result->result) || !is_array($result->result)) {
            throw new Exception('聚合命令未返回 "result" 数组');
        }

        if (isset($options['typeMap'])) {
            return new TypeMapArrayIterator($result->result, $options['typeMap']);
        }

        return new ArrayIterator($result->result);
    }

    /**
     * 计算符合条件的文档数量
     * @param array $parameters 查询参数
     * @return integer 匹配的文档数量
     */
    public static function count(array $parameters = null)
    {
        self::parseParameters($filter, $options, $parameters);

        $model      = new static();
        $conn       = $model->getManager()->getMongoManger();
        $database   = $model->getManager()->getDatabase();
        $collection = $model->getSource();

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

        $cursor = $conn->executeCommand($database, new \MongoDB\Driver\Command($cmd));
        $result = current($cursor->toArray());

        if (!isset($result->n) || !(is_integer($result->n) || is_float($result->n))) {
            throw new Exception('count 未返回数字型的 "n" 数值');
        }

        return intval($result->n);
    }

    /**
     * 是否存在符合条件的文档
     * @param array $parameters 查询参数
     * @return boolean 相应文档是否存在
     */
    public static function exists(array $parameters = null)
    {
        return static::count($parameters) > 0;
    }

    /**
     * 删除文档
     * @param array $parameters 查询参数
     * @return integer 删除文件数目
     */
    public static function remove(array $parameters = null)
    {
        self::parseParameters($filter, $options, $parameters);

        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->delete($filter, $options);

        $model      = new static();
        $conn       = $model->getManager()->getMongoManger();
        $database   = $model->getManager()->getDatabase();
        $collection = $model->getSource();
        $namespace  = sprintf('%s.%s', $database, $collection);

        $result = $conn->executeBulkWrite($namespace, $bulk);
        return $result->getDeletedCount();
    }

    /**
     * 保存数据到某一文档 (新建或更新)
     * @param array $data 文档数据
     * @return boolean 是否保存成功
     */
    public function save(array $data = null)
    {
        $document = false;
        if (isset($this->_id)) {
            $document = static::findById($this->_id);
        }

        if ($document !== false) {
            return $this->update($data);
        }

        return $this->create($data);
    }

    /**
     * 新建文档
     * @param array $data 文档数据 (如果 $data 为空，数据取自 $this 实例本身)
     * @return boolean 是否新建成功
     */
    public function create(array $data = null)
    {
        if (is_null($data)) {
            $data = $this->toArray();
        } else {
            $this->fromArray($data);
        }

        $bulk = new \MongoDB\Driver\BulkWrite();
        $_id  = $bulk->insert($data);

        if ($_id !== null) {
            $this->_id = $_id;
        }

        $conn       = $this->getManager()->getMongoManger();
        $database   = $this->getManager()->getDatabase();
        $collection = $this->getSource();
        $namespace  = sprintf('%s.%s', $database, $collection);

        $result = $conn->executeBulkWrite($namespace, $bulk);
        return empty($result->getWriteErrors());
    }

    /**
     * 更新文档
     * @param array $data 文档数据 (如果 $data 为空，数据取自 $this 实例本身)
     * @return boolean 是否更新成功
     */
    public function update(array $data = null)
    {
        if (is_null($data)) {
            $data = $this->toArray();
        } else {
            $this->fromArray($data);
        }

        self::parseParameters($filter, $options, [
            ['_id' => $this->_id],
            'multi'  => false,
            'upsert' => false,
        ]);

        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->update($filter, $data, $options);

        $conn       = $this->getManager()->getMongoManger();
        $database   = $this->getManager()->getDatabase();
        $collection = $this->getSource();
        $namespace  = sprintf('%s.%s', $database, $collection);

        $result = $conn->executeBulkWrite($namespace, $bulk);
        return empty($result->getWriteErrors());
    }

    /**
     * 删除文档
     * @return boolean 是否删除成功
     */
    public function delete()
    {
        self::parseParameters($filter, $options, [
            ['_id' => $this->_id],
            'limit' => 1,
        ]);

        $bulk = new \MongoDB\Driver\BulkWrite();
        $bulk->delete($filter, $options);

        $conn       = $this->getManager()->getMongoManger();
        $database   = $this->getManager()->getDatabase();
        $collection = $this->getSource();
        $namespace  = sprintf('%s.%s', $database, $collection);

        $result = $conn->executeBulkWrite($namespace, $bulk);
        return empty($result->getWriteErrors());
    }

    /**
     * 转换为数组
     */
    public function toArray()
    {
        return array_filter(
            get_object_vars($this),
            function ($k) {
                return ($k[0] !== '_' || $k === '_id');
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * 从数组设置属性值
     */
    public function fromArray(array $data)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if ($key === '_id' || ctype_alpha($key[0])) {
                    $this->{$key} = $value;
                }
            }
        }
    }

    /**
     * 序列化
     */
    public function serialize()
    {
        return serialize($this->toArray());
    }

    /**
     * 反序列化
     */
    public function unserialize($data)
    {
        $attributes = unserialize($data);

        if (is_array($attributes)) {

            $this->_dependencyInjector = Di::getDefault();
            if (!is_object($this->_dependencyInjector)) {
                throw new Exception('需要依赖容器以获得 mongo 服务');
            }

            $this->setManager();

            foreach ($attributes as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}
