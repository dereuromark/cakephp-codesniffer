<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.PHP.TypeCasting

class Foo {

	public function aMethod() {
		$a = (BOOL)$y;
		$a = !! $y;
		$a = (boolean) $foo;
		$a =(integer)$foo;
	}

	public function anotherMethod() {
		if (($x = (aRray) $foo) !== $some) {
		}
	}

}

