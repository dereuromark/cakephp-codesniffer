<?php
require_once dirname(__FILE__) . '/bootstrap.php';

/**
 * CakePHPStandardTest
 */
class CakePHPStandardTest extends PHPUnit_Framework_TestCase {

	/**
	 * CakePHPStandardTest::setUp()
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();
		if (empty($this->helper)) {
			$this->helper = new TestHelper();
		}
	}

	/**
	 * testFiles
	 *
	 * Run simple syntax checks, if the filename ends with pass.php - expect it to pass.
	 * If a filename ends with expected.php, it will not be checked, but used to assert
	 * the result of the auto-correction of the fail.php one.
	 *
	 * @return array
	 */
	public static function testProvider() {
		return self::_testProvider();
	}

	/**
	 * CakePHPStandardTest::_testProvider()
	 *
	 * @param string $name Name to restrict tests on this subset.
	 * @return array
	 */
	protected static function _testProvider($name = null) {
		$standard = dirname(dirname(__FILE__));

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__FILE__) . '/files'));
		$tests = array();
		foreach ($iterator as $dir) {
			if ($dir->isDir()) {
				continue;
			}

			$file = $dir->getPathname();
			if ($name && strpos($file, DIRECTORY_SEPARATOR . $name . '_') === false) {
				continue;
			}
			if (substr($file, -4) !== '.php') {
				continue;
			}

			if (substr($file, -12) === 'expected.php') {
				continue;
			}

			$expectPass = (substr($file, -8) === 'pass.php');
			$sniffs = null;
			$expectedErrors = null;
			$expectedCorrections = null;
			$expectedResult = null;

			$content = file_get_contents($file);
			if (!$expectPass) {
				preg_match("/\/\/\s*@expectedErrors (\d+)\b/i", $content, $matches);
				if ($matches) {
					$expectedErrors = $matches[1];
				}
				preg_match("/\/\/\s*@expectedCorrections (\d+)\b/i", $content, $matches);
				if ($matches) {
					$expectedCorrections = $matches[1];
				}
			}
			preg_match("/\/\/\s*@sniffs\s*([\,\.\w]+)\b/i", $content, $matches);
			if ($matches) {
				$sniffs = $matches[1];
			}
			$expectedFile = str_replace('_fail.php', '_expected.php', $file, $count);
			if ($count && file_exists($expectedFile)) {
				$expectedResult = file_get_contents($expectedFile);
			}

			$tests[] = array(
				$file,
				$standard,
				$expectPass,
				$sniffs,
				$expectedErrors,
				$expectedCorrections,
				$expectedResult,
			);
		}
		if (empty($tests)) {
			throw new RuntimeException('No test files found');
		}
		return $tests;
	}

	/**
	 * _testFile
	 *
	 * @dataProvider testProvider
	 *
	 * @param string $file
	 * @param string $standard
	 * @param boolean $expectPass
	 */
	public function testFile($file, $standard, $expectPass, $sniffs, $expectedErrors, $expectedCorrections, $expectedResult) {
		$outputStr = $this->helper->runPhpCs($file, $sniffs);
		if ($expectPass) {
			$this->assertNotRegExp(
				"/FOUND \d+ ERROR/",
				$outputStr,
				basename($file) . ' - expected to pass with no errors, some were reported. '
			);
		} else {
			$this->assertRegExp(
				"/FOUND \d+ ERROR/",
				$outputStr,
				basename($file) . ' - expected failures, none reported. '
			);
			preg_match("/FOUND (\d+) ERROR/", $outputStr, $matches);
			if ($expectedErrors) {
				$actualErrors = $matches ? $matches[1] : 0;
				$this->assertSame((int)$expectedErrors, (int)$actualErrors, 'Errors expected: ' . $expectedErrors);
			}
		}

		$outputStr = $this->helper->runPhpCs($file, $sniffs, true);
		if ($expectPass) {
			$this->assertNull($outputStr, 'Should pass, but output is not null');
			return;
		}
		$this->assertNotNull($outputStr, 'Output should not be null');

		if ($expectedResult) {
			$expectedResult = $this->_convertNewlines(trim($expectedResult));
			$outputStr = $this->_convertNewlines(trim($outputStr));
			$this->assertEquals($expectedResult, $outputStr);
		} else {
			//$this->out($actualErrors . ' errors encountered');
		}
	}

	/**
	 * CakePHPStandardTest::_convertNewlines()
	 *
	 * @param string $content
	 * @return string
	 */
	protected function _convertNewlines($content) {
		return str_replace(array("\r\n", "\r"), "\n", $content);
	}

}
