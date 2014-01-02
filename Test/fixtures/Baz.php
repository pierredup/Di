<?php

namespace DiTest;

class Baz
{
    public $param1;
    public $param2;

    public function __construct(Bar $param1, $param2 = 'default')
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
    }
}

class BazBar extends Bar
{
    public static function getValue()
    {
        return 'value14';
    }
}