<?php
// @sniffs MyCakePHP.PHP.FunctionName

if (is_numeric($foo)) {
}

function someThing() {
	$is = strtolower($was);
}

class Foo {

	public function bar() {
		$x = is_array($array);

		$y = someThing($x);
	}

	public function untouched() {
		// These should not be touched
		$this->Strtolower();
		$lower = new Strtolower();
		SomeStatic::Strtolower();
		Hash::foo();
	}

	/**
	 * gettype is also a PHP method
	 */
	public static function getType($var) {
	}

}