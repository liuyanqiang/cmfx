<?php
namespace UI\Tests\Units;

use \Cmfx\Test\Unit as BaseUnit;
use \UI\Tests\Models\Foo;

/**
 * 测试 \Cmfx\Mvc\Collection 类功能代码
 */
class CollectionUnit extends BaseUnit
{
    /**
     * 初始测试
     */
    public function initialize()
    {
        Foo::remove();
        $n = Foo::count();
        $this->assertTrue($n == 0, '清空 Foo 集合中的所有文档');
    }

    /**
     * 创建文档测试
     */
    public function createTest()
    {
        $f        = new Foo();
        $f->name  = 'bar';
        $f->age   = 11;
        $f->favs  = ['banana', 'apple', 'pear'];
        $f->score = ['maths' => 100, 'english' => 98];
        $success  = $f->create();
        $this->assertTrue($success, '通过赋值实例属性创建文档', $f->_id);

        $f       = new Foo();
        $success = $f->create([
            'name'  => 'good',
            'age'   => 4,
            'favs'  => ['piano'],
            'score' => ['history' => 77],
        ]);
        $this->assertTrue($success, '通过参数数组创建文档', $f->_id);

        $f       = new Foo();
        $success = $f->create([
            '_id'   => 1,
            'name'  => 'good',
            'age'   => 5,
            'favs'  => ['piano'],
            'score' => ['history' => 77],
        ]);
        $this->assertTrue($success, '指定整数 _id=1 创建文档', $f->_id);

        $f       = new Foo();
        $success = $f->create([
            '_id'   => 2,
            'name'  => 'good',
            'age'   => 18,
            'favs'  => ['piano'],
            'score' => ['history' => 77],
        ]);
        $this->assertTrue($success, '指定整数 _id=2 创建文档', $f->_id);

        $f        = new Foo();
        $f->_id   = 2;
        $f->name  = 'bar';
        $f->age   = 25;
        $f->favs  = ['banana', 'apple', 'pear'];
        $f->score = ['maths' => 100, 'english' => 98];

        $thrown = false;
        try {
            $f->create();
        } catch (\Exception $e) {
            $thrown = true;
            $this->assertTrue($success, '插入重复 _id=2 抛出异常:' . $e->getMessage(), $f->_id);
        }

        if (!$thrown) {
            $this->assertTrue(false, '插入重复 _id=2 抛出异常:' . $e->getMessage(), $f->_id);
        }
    }

    /**
     * 更新文档测试
     */
    public function updateTest()
    {
        $name = 'name' . date('YmdHis');

        $f = Foo::findFirst();

        if (false === $f) {
            $this->assertTrue(false, '不可单独执行此项测试，依赖于 create ');
            return;
        }

        $f->name = $name;
        $f->update();

        $f1 = Foo::findFirstById($f->_id);
        $this->assertTrue($f1->name == $name, '通过改变实例属性更新文档', '更新随机姓名匹配' . $f1->name);

        $f->update(['name' => 'aqiao']);
        $f1 = Foo::findFirstById($f->_id);
        $this->assertTrue($f1->name == 'aqiao', '通过数组参数更新文档', $f1->name);
        $this->assertTrue(!isset($f1->age), '通过数组参数更新文档，只保留数组内的数值');

        $f1   = Foo::findFirstById(1);
        $favs = $f1->favs;

        $n1         = count($f1->favs);
        $f1->favs[] = 'violin';
        $f1->update(['$set' => ['favs' => $f1->favs]]);

        $f2 = Foo::findFirstById(1);
        $n2 = count($f2->favs);

        $this->assertTrue(
            ($n1 + 1 == $n2) && ($f2->favs[$n2 - 1] == 'violin') && ($f1->name == $f2->name),
            '通过 $set 方式更新指定字段',
            '[' . implode(',', $favs) . '] --> [' . implode(',', $f2->favs) . ']'
        );
    }

    /**
     * 查找文档测试
     */
    public function findTest()
    {
        $d = Foo::findFirst();

        if (false === $d) {
            $this->assertTrue(false, '不可单独执行此项测试，依赖于 create ');
            return;
        }

        $d1 = Foo::findFirstById($d->_id);
        $this->assertTrue($d->_id == $d1->_id, '通过 findFirstById 查找文档', $d1->_id);

        $d2 = Foo::findById($d->_id);
        $this->assertTrue($d->_id == $d2->_id, '通过 findById 查找文档', $d2->_id);

        $d3 = Foo::findById(strval($d->_id));
        $this->assertTrue($d->_id == $d3->_id, '通过 findById 传入字符串类型 _id 查找文档', $d3->_id);

        $d4 = Foo::findFirst(['_id' => $d->_id]);
        $this->assertTrue($d->_id == $d4->_id, '通过 findFirst 传入 ObjectId 查找文档', $d4->_id);

        $d5 = Foo::findFirst(['_id' => strval($d->_id)]);
        $this->assertTrue($d->_id == $d5->_id, "通过 findFirst 传入 ['_id' => '{$d->_id}'] 查找文档", $d5->_id);

        $docs1 = Foo::find();
        $docs2 = Foo::find(['sort' => ['age' => 1]]);
        $docs3 = Foo::find(['sort' => ['age' => -1]]);

        $n1 = count($docs1);
        $n2 = count($docs2);
        $n3 = count($docs3);

        $this->assertTrue(
            ($n1 > 0) && ($n1 == $n2) && ($n2 == $n3),
            '通过 find 查询多个文档'
        );

        $this->assertTrue($docs2[0]->_id == $docs3[$n3 - 1]->_id, '通过 find 传入 sort 参数查询文档');

        $docs4 = Foo::find([
            'sort'  => ['age' => 1],
            'skip'  => $n1 - 1,
            'limit' => 1,
        ]);

        $this->assertTrue(
            count($docs4) == 1 && $docs4[0]->_id == $docs3[0]->_id,
            '通过 find 传入 sort、skip、limit 参数查询文档'
        );

    }

    /**
     * 序列化测试
     */
    public function serializeTest()
    {
        $d = Foo::findFirst();

        if (false === $d) {
            $this->assertTrue(false, '不可单独执行此项测试，依赖于 create ');
            return;
        }

        $data = serialize($d);
        $d1   = unserialize($data);

        $this->assertTrue(
            (get_class($d) == get_class($d1)) && ($d->_id == $d1->_id),
            '序列化和反序列化文档',
            get_class($d) . '/' . $d->_id
        );
    }

    /**
     * 删除文档测试
     */
    public function deleteTest()
    {
        $d = Foo::findFirst();

        if (false === $d) {
            $this->assertTrue(false, '不可单独执行此项测试，依赖于 create ');
            return;
        }

        $_id     = $d->_id;
        $success = $d->delete();

        $d1 = Foo::findById($_id);

        $this->assertTrue($success && $d1 === false, '通过 delete 删除文档');
    }

    /**
     * 静态方法删除测试
     */
    public function removeTest()
    {
        $n = Foo::count();
        $this->assertTrue(true, 'Foo 集合中当前有' . $n . '个文档');

        if ($n > 0) {
            Foo::remove(['limit' => 1]);
            $m = Foo::count();
            $this->assertTrue($m + 1 == $n, '通过 Collection::remove 删除一个文档');
        }

        Foo::remove();
        $n = Foo::count();
        $this->assertTrue($n == 0, '通过 Collection::remove 删除所有文档');
    }
}
