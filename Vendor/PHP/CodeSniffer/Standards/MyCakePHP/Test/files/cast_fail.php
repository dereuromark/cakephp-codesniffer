<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.PHP.Cast

class Foo {

	public function aMethod() {
		$a = (int)$y;
		$a = (float)$y;
		$a = (int)$foo;
		$a = (float)$foo;

		$half = intval($modulus / 2);

		$b = intval(date('G', $time));
		$b = intval($f + date('G', $time));
	}

	public function anotherMethod() {
		$a = $x + ((int)$y);
		$a = $x + (intval($y, 9));
		$a = $x + ((int)SomeClass::foo($b, $c, $d));
		$a = $x + (intval(SomeClass::foo($b, $c, $d), 9));
	}

}

