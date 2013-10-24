<?php
// @sniffs MyCakePHP.PHP.FunctionName

if (is_Numeric($foo)) {
}

function someThing() {
	$is = sTrTolower($was);
}

function some_thing() {
	$is = strtolower($was);
}

class Foo {

	public function bar() {
		$x = Is_array($array);

		$y = some_thing($x);
	}

}