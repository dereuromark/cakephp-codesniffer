<?php

class Foo {

	public function testElseif() {
		if ($x) {

		} else if ($y) {

		} elseif ($y) {

		} else {

		}
	}

	public function testComma() {
		explode(',','a,b,c');

		explode(',' , 'a,b,c');
	}

	public function testControlStructures() {
		if ($x)
			foo();
		elseif ($y)
			bar();
		else
			foobar();
	}
}