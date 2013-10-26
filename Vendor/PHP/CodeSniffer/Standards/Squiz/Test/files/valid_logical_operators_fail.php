<?php
// @sniffs Squiz.Operators.ValidLogicalOperators

class Foo {

	public function test() {
		$x = $foo OR $fo;
		$y = $foo AND $x;

		if ($y OR $x) {
		}
		while ($x = ($e AND $f)) {
		}
	}

}