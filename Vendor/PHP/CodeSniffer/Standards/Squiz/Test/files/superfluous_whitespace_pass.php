<?php
// @sniffs Squiz.WhiteSpace.SuperfluousWhitespace

class Foo {

	public function test() {
		$x = 'string';

		if ($y = ( int )$foo) {
		}
		while( ( bool)$x !==(bool ) $y) {
		}
	}

	public function foo() {
	}
}