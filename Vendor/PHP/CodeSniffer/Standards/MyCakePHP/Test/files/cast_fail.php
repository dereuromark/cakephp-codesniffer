<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.PHP.Cast

class Foo {

	public function aMethod() {
		$a = intval($y);
		$a = floatval($y);
		$a = (int)$foo;
		$a = (float)$foo;
	}

	public function anotherMethod() {
		$a = $x + (intval($y));
		$a = $x + (intval($y, 9));
	}

}

