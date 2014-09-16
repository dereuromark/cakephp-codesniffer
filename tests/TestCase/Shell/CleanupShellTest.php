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
		$result = $this->Md->runCommand(array('unused_use'), true);
		$this->assertSame(0, $result);

		$result = $this->out->output;
		debug($result);
	}


	/**
	 * @return void
	 */
	public function testCustomPath() {
		$result = $this->Md->runCommand(array('unused_use', $this->testFolder . 'UnusedUse'), true);
		$this->assertSame(0, $result);

		$result = $this->out->output;
		debug($result);
	}


}
