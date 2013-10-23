<?php
// @sniffs Squiz.WhiteSpace.CastSpacing

class Foo {

	public function test() {
		$x = (string)'string' ;
		if ($y = (int)$foo) {
		}
		while((bool)$x !== (bool)$y) {
		}

	}

}