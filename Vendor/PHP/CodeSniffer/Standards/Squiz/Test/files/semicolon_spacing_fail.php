<?php
// @sniffs Squiz.WhiteSpace.SemicolonSpacing

class Foo {

	var $x = 'ee' ;

	public function test() {
		$x = 'string' ;
		$y = 'a long' .
			'string'
		;
	}

}