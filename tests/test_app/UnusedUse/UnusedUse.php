<?php

namespace Foo\Bar;

use Something\Cool;
use Something\SomeClass;
use Something\SomeOtherClass;
use Something\SomeTotallyOtherClass;

class UnusedUse extends Cool {

	public function fooBar() {
		$var = new SomeClass();

		$var = SomeTotallyOtherClass::foo();
	}

}
