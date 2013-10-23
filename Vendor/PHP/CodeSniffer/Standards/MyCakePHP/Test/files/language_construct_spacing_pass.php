<?php
// @sniffs MyCakePHP.WhiteSpace.LanguageConstructSpacing

require "$foo";
include_once $bar;

class Foo {

	public function aMethod() {
		require_once $x;
	}

	public function anotherMethod() {
		include $some;
	}

}

