<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.WhiteSpace.LanguageConstructSpacing

require"$foo";
include_once ("$foo");

class Foo {

	public function aMethod() {
		require_once($x);
	}

	public function anotherMethod() {
		include ($some);
	}

}

