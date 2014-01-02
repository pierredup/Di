<?php

namespace DiTest;

use DiTest\Bar as Baz;

class Foo
{
    public $baz;

    public function __construct(Baz $baz)
    {
        $this->baz = $baz;
    }
}