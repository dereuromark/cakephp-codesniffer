<?php

require "$foo";
include_once "$foo";

class Foo {

	public function aMethod() {
		require_once $x;
	}

	public function anotherMethod() {
		include $some;
	}

}

