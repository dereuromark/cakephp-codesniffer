<?php
namespace CodeSniffer\Test\TestCase\Shell;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Cake\Console\ConsoleOutput;
use Cake\Console\ConsoleIo;
use CodeSniffer\Shell\CleanupShell;

/**
 * Class TestCleanupShellOutput
 */
class TestCleanupShellOutput extends ConsoleOutput {

	public $output = '';

	protected function _write($message) {
		$this->output .= $message;
	}

}

class CleanupShellTest extends TestCase {

	public $Cleanup;

	public $testPath;

	public function setUp() {
		parent::setUp();

		Configure::write('debug', true);

		$this->out = new TestCleanupShellOutput();
		$io = new ConsoleIo($this->out);

		$this->Cleanup = new CleanupShell($io);
		$this->Cleanup->initialize();

		$this->testFolder = dirname(dirname(dirname(__FILE__))) . DS . 'test_app' . DS;
	}

	/**
	 * CodeSnifferShellTest::testTokenize()
	 *
	 * @return void
	 */
	public function testUnusedUse() {
		$result = $this->Cleanup->runCommand(array('unused_use'), true);
		$this->assertNull($result);

		$result = $this->out->output;
		$this->assertNotEmpty($result);
	}

	/**
	 * @return void
	 */
	public function testUnusedUseCustomPath() {
		$result = $this->Cleanup->runCommand(array('unused_use', $this->testFolder . 'UnusedUse'), true);
		$this->assertNull($result);

		$result = $this->out->output;
		//debug($result);

		$this->assertTextContains('2 files found. Checking', $result);

		$this->assertTextContains('- SomeOtherClass', $result);
		$this->assertTextContains('1 unused use statement(s) found', $result);

		$this->assertTextContains('- IteratorIteratorFoo', $result);
		$this->assertTextContains('4 unused use statement(s) found', $result);

		$this->assertTextContains('A total of 5 unused use statement(s) found', $result);
	}

}
