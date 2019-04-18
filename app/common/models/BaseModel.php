<?php

namespace UI\Models;

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as SimpleResultset;
use Phalcon\DI\FactoryDefault;

class BaseModel extends Model {

    /**
     * 集合名称
     */
    protected $_source;

    /**
     * 返回集合(collection)名称
     */
    public function getSource() {
        if (is_null($this->_source)) {
            $class = get_class($this);
            if (($pos = strrpos($class, '\\')) !== false) {
                $class = substr($class, $pos + 1);
            }
            // 把以驼峰式命名的类名转换为下划线连接的集合名称
            $this->_source = 'zs_' . strtolower(preg_replace('/([A-Z])/', '_\\0', lcfirst($class)));
        }
        return $this->_source;
    }

    public function _query($sql) {
        return new SimpleResultset(null, $this, $this->getReadConnection()->query($sql));
    }

    public function _execute($sql) {
        return $this->getWriteConnection()->execute($sql);
    }

    static public function db() {
        $di = FactoryDefault::getDefault();
        return $di->getDb();
    }

    /**
     * 静态方法返回 DI
     */
    public static function DI()
    {
        return \Phalcon\Di::getDefault();
    }

    /**
     * 我不知道原生model是否有此方法
     * @param $id int
     * @return Model
     */
    public static function findById($id){
        return static::findFirst("id={$id}");
    }

    public static function findFirstById($id){
        return static::findFirst([
            "conditions" => "id = {$id}"
        ]);
    }

}
