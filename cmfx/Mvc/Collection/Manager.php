<?php
namespace Cmfx\Mvc\Collection;

use Cmfx\Mvc\CollectionInterface;
use Phalcon\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\ManagerInterface;

/**
 * Mongo 链接管理类
 */
class Manager implements InjectionAwareInterface, EventsAwareInterface
{
    /**
     * 依赖
     */
    protected $_dependencyInjector;

    /**
     * 记录初始化过的 collection 类
     */
    protected $_initialized;

    /**
     * 最后初始化的 collection 类
     */
    protected $_lastInitialized;

    /**
     * 事件管理
     */
    protected $_eventsManager;

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
     * 返回 MongoDB\Driver\Manger 实例
     */
    public function getMongoManger()
    {
        return $this->_mongo;
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
     * Sets the DependencyInjector container
     */
    public function setDI(DiInterface $dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    /**
     * Returns the DependencyInjector container
     */
    public function getDI()
    {
        return $this->_dependencyInjector;
    }

    /**
     * Sets the event manager
     */
    public function setEventsManager(ManagerInterface $eventsManager)
    {
        $this->_eventsManager = $eventsManager;
    }

    /**
     * Returns the internal event manager
     */
    public function getEventsManager()
    {
        return $this->_eventsManager;
    }

    /**
     * Initializes a model in the models manager
     */
    public function initialize(CollectionInterface $model)
    {
        $className = strtolower(get_class($model));

        if (!isset($this->_initialized[$className])) {

            if (method_exists($model, "initialize")) {
                $model->initialize();
            }

            if (is_object($this->_eventsManager)) {
                $this->_eventsManager->fire("collectionManager:afterInitialize", $model);
            }

            $this->_initialized[$className] = $model;
            $this->_lastInitialized         = $model;
        }
    }

    /**
     * Check whether a model is already initialized
     */
    public function isInitialized($modelName)
    {
        return isset($this->_initialized[strtolower($modelName)]);
    }

    /**
     * Get the latest initialized model
     */
    public function getLastInitialized()
    {
        return $this->_lastInitialized;
    }

}
