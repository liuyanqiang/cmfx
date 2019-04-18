<?php
namespace Cmfx\Cache;

/**
 * 缓存基类
 */
abstract class Cache
{
    protected $_prefix  = '';
    protected $_options = [];

    public function __construct(array $options = null)
    {
        if (!is_array($options)) {
            $options = [];
        }

        if (isset($options['prefix'])) {
            $this->_prefix = $options['prefix'];
            unset($options['prefix']);
        }

        foreach ($options as $key => $value) {
            if (isset($this->_options[$key])) {
                $this->_options[$key] = $value;
            }
        }
    }
}
