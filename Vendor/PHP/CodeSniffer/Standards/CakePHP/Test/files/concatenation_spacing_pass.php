<?php
// @sniffs CakePHP.Strings.ConcatenationSpacing

class Foo {

	public function test() {
		$x = $y . $x;

		$y = 'some string'  .  'more';
	}

	public function test2() {
		$x = $y
			. $x;

		$y = 'some string' .
			'more';
	}

}
