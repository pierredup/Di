<?php

class DiTest extends PHPUnit_FrameWork_TestCase
{
    public function setUp()
    {
        Di::clear();
    }

    public function test_di_get_instance()
    {
        $test = Di::get('DiTest\Foo');
        $this->assertInstanceOf('DiTest\Foo', $test);
        $this->assertInstanceOf('DiTest\Bar', $test->baz);
    }

    public function test_di_instance_is_same()
    {
        $test1 = Di::get('DiTest\Foo');
        $test2 = Di::get('DiTest\Foo');

        $this->assertSame($test1, $test2);
        $this->assertSame($test1->baz, $test2->baz);
    }

    public function test_di_new_instance()
    {
        $test1 = Di::get('DiTest\Foo');
        $test2 = Di::get('DiTest\Foo', Di::NEW_INSTANCE);
        $this->assertNotSame($test1, $test2);
        $this->assertSame($test1->baz, $test2->baz);
    }

    public function test_di_new_instance_deep()
    {
        $test1 = Di::get('DiTest\Foo');
        $test2 = Di::get('DiTest\Foo', Di::DEEP);
        $this->assertNotSame($test1, $test2);
        $this->assertNotSame($test1->baz, $test2->baz);
    }

    public function test_map_params()
    {
        Di::map(array(
            'key1' => 'value1',
            'key2' => function() {
                    return 'value2';
                },
            'key3' => Di::get('DiTest\Foo'),
            'key4' => array('DiTest\BazBar', 'getValue')
        ));

        $param1 = Di::get('key1', Di::PARAM);
        $this->assertSame('value1', $param1);

        $param2 = Di::get('key2', Di::PARAM);
        $this->assertSame('value2', $param2);

        $param3 = Di::get('key3', Di::PARAM);
        $this->assertSame(Di::get('DiTest\Foo'), $param3);

        $param4 = Di::get('key4', Di::PARAM);
        $this->assertSame('value14', $param4);

        $this->setExpectedException('InvalidArgumentException', 'Parameter key5 does not exist');
        $param5 = Di::get('key5', Di::PARAM);
    }

    public function test_map_class_params()
    {
        Di::map(array(
            'param1' => Di::get('DiTest\BazBar'),
            'param2' => 'value1'
        ));

        $baz1 = Di::get('DiTest\Baz');
        $this->assertInstanceOf('DiTest\BazBar', $baz1->param1);
        $this->assertSame('value1', $baz1->param2);
    }

    public function test_map_class_default_params()
    {
        $bar = Di::get('DiTest\Bar');
        $baz1 = Di::get('DiTest\Baz');

        $this->assertSame($bar, $baz1->param1);
        $this->assertSame('default', $baz1->param2);
    }

    public function test_overwrite_class_name()
    {
        Di::map(array(
            'DiTest\Bar' => Di::get('DiTest\BazBar')
        ));

        $object = Di::get('DiTest\Baz');

        $this->assertInstanceOf('DiTest\BazBar', $object->param1);
    }

    public function test_default_null_parameter_value()
    {
        $object = Di::get('DiTest\FooBar');
        $this->assertNull($object->param1);
    }
}