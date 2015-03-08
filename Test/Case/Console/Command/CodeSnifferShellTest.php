<?php

App::uses('CodeSnifferShell', 'CodeSniffer.Console/Command');

class CodeSnifferShellTest extends CakeTestCase {

	public $CodeSniffer;

	public $testPath;

	public function setUp() {
		parent::setUp();

		$this->CodeSniffer = new TestCodeSnifferShell();
		$this->CodeSniffer->testPath = CakePlugin::path('CodeSniffer') . 'Test' . DS . 'test_files' . DS;
	}

	/**
	 * CodeSnifferShellTest::testTokenize()
	 *
	 * @return void
	 */
	public function testTokenize() {
		$this->CodeSniffer->initialize();
		$this->CodeSniffer->startup();

		// normal output
		$this->CodeSniffer->params['standard'] = 'CakePHP';
		$this->CodeSniffer->params['verbose'] = false;
		$folder = TMP . 'cs' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}
		copy($this->CodeSniffer->testPath . 'cs' . DS . 'test.php', $folder . 'test.php');
		$this->CodeSniffer->args = [$folder . 'test.php'];
		$this->CodeSniffer->tokenize();

		$this->assertTrue(file_exists($folder . 'test.php.token'));

		// verbose output
		$this->CodeSniffer->params['verbose'] = true;
		copy($this->CodeSniffer->testPath . 'cs' . DS . 'test.php', $folder . 'test2.php');
		$this->CodeSniffer->args = [$folder . 'test2.php'];
		$this->CodeSniffer->tokenize();

		$this->assertTrue(file_exists($folder . 'test2.php.token'));
	}

}

class TestCodeSnifferShell extends CodeSnifferShell {

}
