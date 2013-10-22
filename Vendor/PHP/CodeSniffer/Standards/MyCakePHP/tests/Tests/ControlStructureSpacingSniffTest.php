<?php
require_once dirname(dirname(__FILE__)) . '/CakePHPStandardTest.php';

/**
 * ControlStructureSpacingSniffTest
 */
class ControlStructureSpacingSniffTest extends CakePHPStandardTest {

/**
 * testFiles
 *
 * Run simple syntax checks, if the filename ends with pass.php - expect it to pass.
 * If a filename ends with expected.php, it will not be checked, but used to assert
 * the result of the autocorrection of the fail.php one.
 */
	public static function testProvider() {
		$name = 'control_structure_spacing';

		return self::_testProvider($name);
	}

}
