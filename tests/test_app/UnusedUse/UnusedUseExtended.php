<?php

namespace Foo\Bar;

use Something\CoolFoo;
use Something\SuperNice;
use Something\SuperDuperNice;
use Something\SomeClass;
use \Something\SomeOtherClass;
use IteratorIterator;
use \IteratorIteratorFoo;
use Datetime;
use CaseSensitiveClass;

class UnusedUseExtended extends IteratorIterator implements SuperNice, SuperDuperNice {

	public function fooBar() {
		$var = new SomeClass();

		$var = SomeTotallyOtherClass::foo();

		$date = new \Datetime();

		$class = new casesensitiveclass();
	}

}
