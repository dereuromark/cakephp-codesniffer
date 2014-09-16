<?php

namespace Foo\Bar;

use Something\Cool;
use Something\SuperCool;
use Something\SomeClass;
use Something\SomeOtherClass;
use Something\SomeTotallyOtherClass;

class UnusedUse extends Cool implements SuperCool {

	public function fooBar() {
		$var = new SomeClass();

		$var = SomeTotallyOtherClass::foo();
	}

}
