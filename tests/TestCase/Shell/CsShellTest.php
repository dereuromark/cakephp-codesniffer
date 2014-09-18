<?php
namespace CodeSniffer\Test\TestCase\Shell;

use Cake\TestSuite\TestCase;
use Cake\Console\ConsoleOutput;
use Cake\Console\ConsoleIo;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use CodeSniffer\Shell\CsShell;

/**
 * Class TestCleanupShellOutput
 */
class TestCsShellOutput extends ConsoleOutput {

	public $output = '';

	protected function _write($message) {
		$this->output .= $message;
	}

}

class CodeSnifferShellTest extends TestCase {

	public $Cs;

	public $testPath;

	public function setUp() {
		parent::setUp();

		Configure::write('debug', true);

		$this->out = new TestCsShellOutput();
		$io = new ConsoleIo($this->out);

		$this->Cs = new CsShell($io);
		$this->Cs->initialize();

		$this->Cs->testPath = dirname(dirname(dirname(__FILE__))) . DS . 'test_app' . DS;
	}

	/**
	 * CodeSnifferShellTest::testTokenize()
	 *
	 * @return void
	 */
	public function testTokenize() {
		$folder = TMP . 'Cs' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		// normal output
		copy($this->Cs->testPath . 'Cs' . DS . 'test.php', $folder . 'test.php');
		$path = $folder . 'test.php';

		$result = $this->Cs->runCommand(array('tokenize', $path), true);

		$this->assertTrue(file_exists($folder . 'test.php.token'));

		// verbose output
		copy($this->Cs->testPath . 'Cs' . DS . 'test.php', $folder . 'test2.php');
		$path = $folder . 'test2.php';

		$result = $this->Cs->runCommand(array('tokenize', $path, '-v'), true);

		$this->assertTrue(file_exists($folder . 'test2.php.token'));
	}

}