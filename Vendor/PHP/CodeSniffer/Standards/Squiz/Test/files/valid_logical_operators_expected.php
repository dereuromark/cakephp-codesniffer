<?php
// @sniffs Squiz.Operators.ValidLogicalOperators

class Foo {

	public function test() {
		$x = $foo OR $fo;
		$y = $foo AND $x;

		if ($y || $x) {
		}
		while ($x = ($e && $f)) {
		}
	}

}