<?php
namespace CodeSniffer\Test\TestCase\Shell;

use Cake\TestSuite\TestCase;
use Cake\Core\Configure;
use Cake\Console\ConsoleOutput;
use Cake\Console\ConsoleIo;
use CodeSniffer\Shell\MdShell;

/**
 * Class TestMdShellOutput
 */
class TestMdShellOutput extends ConsoleOutput {

	public $output = '';

	protected function _write($message) {
		$this->output .= $message;
	}

}

/**
 * MdShellTest class
 *
 */
class MdShellTest extends TestCase {

	public $Md;

	public function setUp() {
		parent::setUp();

		$this->out = new TestMdShellOutput();
		$io = new ConsoleIo($this->out);

		$this->Md = new MdShell($io);
		$this->Md->initialize();

		$this->testFolder = dirname(dirname(dirname(__FILE__))) . DS . 'test_app' . DS;
		if (file_exists(TMP . 'report.txt')) {
			unlink(TMP . 'report.txt');
		}
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function _testBasic() {
		$result = $this->Md->runCommand(array('run'), true);
		$this->assertSame(0, $result);
	}

	/**
	 * @return void
	 */
	public function testCustomPath() {
		$result = $this->Md->runCommand(array('run', $this->testFolder . 'Ok'), true);
		$this->assertSame(0, $result);
	}

	/**
	 * @return void
	 */
	public function testCustomPathIssues() {
		$result = $this->Md->runCommand(array('run', $this->testFolder . 'NotOk'), true);
		$this->assertSame(2, $result);

		$report = file_get_contents(TMP . 'report.txt');
		$this->assertTextContains('Avoid unused local variables such as \'$foo\'.', $report);
		$this->assertTextContains('Avoid unused parameters such as \'$something\'.', $report);
	}

	/**
	 * @return void
	 */
	public function testCustomPathIssuesDirectOutput() {
		$result = $this->Md->runCommand(array('run', $this->testFolder . 'NotOk', '-v', '-f', 'display'), true);
		$this->assertSame(2, $result);

		$result = $this->out->output;
		$this->assertTextContains('Found 2 issue(s)', $result);
		$this->assertTextContains('Avoid unused local variables such as \'$foo\'.', $result);
		$this->assertTextContains('Avoid unused parameters such as \'$something\'.', $result);
	}

}
