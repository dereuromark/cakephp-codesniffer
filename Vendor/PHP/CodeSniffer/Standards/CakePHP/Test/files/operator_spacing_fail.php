<?php
// @sniffs CakePHP.WhiteSpace.OperatorSpacing

class Foo {

	public function test() {
		$x=$y+1;
		if ($y+$x) {
		}
		$a = ($b/$x)%$e;
	}

	public function test2() {
		$x = $y& $z;
		$x = $y |$z;
		$x&=$y;

		// These should not be touched
		$x &= $y;
		$x = $y && $z;
		$x = $y || $z;
	}

}
