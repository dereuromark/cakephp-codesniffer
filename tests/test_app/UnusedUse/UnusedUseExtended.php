<?php

namespace Foo\Bar;

use Something\CoolFoo;
use Something\SuperNice;
use Something\SuperDuperNice;
use Something\SomeClass;
use \Something\SomeOtherClass;
use IteratorIterator;
use \IteratorIteratorFoo;

class UnusedUseExtended extends IteratorIterator implements SuperNice, SuperDuperNice {

	public function fooBar() {
		$var = new SomeClass();

		$var = SomeTotallyOtherClass::foo();
	}

}
