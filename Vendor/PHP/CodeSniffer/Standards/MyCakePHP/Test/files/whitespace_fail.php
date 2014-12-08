<?php
// @expectedErrors 0
// @expectedCorrections 4
// @sniffs MyCakePHP.NamingConventions.ValidClassBrackets

/**
 * @docblock
 */
class Foo {

	/**
	 * @docblock
	 * @return void
	 */
	public function aMethod() {
		if ( $foo && $bar) {

		} elseif ($foo || $bar ) {

		}
	}

	/**
	 * @docblock
	 * @return void
	 */
	public function anotherMethod() {
		while ( $fooBar ) {

		}
	}

}
