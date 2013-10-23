<?php
// @sniffs Squiz.WhiteSpace.SemicolonSpacing

class Foo {

	var $x = 'e';

	public function test() {
		$x = 'string';
		$y = 'a long
		string';
	}

}