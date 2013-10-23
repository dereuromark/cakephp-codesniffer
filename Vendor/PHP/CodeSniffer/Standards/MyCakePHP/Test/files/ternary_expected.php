<?php

class Foo {

	public function aMethod() {
		$x=$y===false ? true : $z;
		$y = ($x = $y) % 2 ? ($x / $y) : null;
	}

	public function anotherMethod() {
		$x = $y ?
			$v :
			$z;

		$x = $y
			? $v
			: $z;
	}

	public function shortTernary() {
		$x = $foo ?: $y;
		$x = $foo ?: $y;
	}

}

