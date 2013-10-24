<?php
require_once dirname(dirname(__FILE__)) . '/CakePHPStandardTest.php';

/**
 * IsWritableSniffTest
 */
class IsWritableSniffTest extends CakePHPStandardTest {

/**
 * testFiles
 *
 * Run simple syntax checks, if the filename ends with pass.php - expect it to pass.
 * If a filename ends with expected.php, it will not be checked, but used to assert
 * the result of the autocorrection of the fail.php one.
 */
	public static function testProvider() {
		$name = 'is_writable';

		return self::_testProvider($name);
	}

}
