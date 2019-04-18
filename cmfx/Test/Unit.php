<?php
namespace Cmfx\Test;

/**
 * 单元测试的基类
 */
abstract class Unit
{
    /**
     * 测试起始时间
     */
    protected static $_startTime = 0;

    /**
     * 测试单元数目
     */
    protected static $_numTask = 0;

    /**
     * 测试通过的项数
     */
    protected static $_numPass = 0;

    /**
     * 测试失败的项数
     */
    protected static $_numError = 0;

    /**
     * 测试统计显示
     */
    public static function summary()
    {
        if (self::$_numTask == 0) {
            return;
        }

        echo sprintf(
            "共执行 %d 个测试单元, 共有 %d 项测试, 通过 %d , 失败 %d \n",
            self::$_numTask,
            self::$_numPass + self::$_numError,
            self::$_numPass,
            self::$_numError
        );

        $delta = microtime(true) - self::$_startTime;
        echo sprintf("总执行时间 %.3f 毫秒\n", $delta * 1000);
    }

    /**
     * 构造函数
     * 实例化新的测试单元时增加单元计数
     */
    public function __construct()
    {
        self::$_numTask++;
        if (self::$_startTime === 0) {
            self::$_startTime = microtime(true);
        }
    }

    /**
     * 为真的断言
     * @param boolean $expr 条件表达式
     * @param string $msg 提示信息
     * @param mixed 需要特别输出显示的数值
     */
    public function assertTrue($expr, $msg = null, $val = null)
    {
        echo "\t";

        if (($expr) === true) {
            self::$_numPass++;
            echo '正确';
        } else {
            self::$_numError++;
            echo '错误';
        }

        if (!empty($msg)) {
            echo "\t({$msg})";
        }

        if (!empty($val)) {
            echo " ({$val})";
        }

        echo "\n";
    }

    /**
     * 为假的断言
     * @param boolean $expr 条件表达式
     * @param string $msg 提示信息
     * @param mixed 需要特别输出显示的数值
     */
    public function assertFalse($expr, $msg = null, $val = null)
    {
        echo "\t";

        if (($expr) === false) {
            self::$_numPass++;
            echo '正确';
        } else {
            self::$_numError++;
            echo '错误';
        }

        if (!empty($msg)) {
            echo "\t({$msg})";
        }

        if (!empty($val)) {
            echo " ({$val})";
        }

        echo "\n";
    }
}
