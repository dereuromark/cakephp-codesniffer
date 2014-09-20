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

class CsShellTest extends TestCase {

	public $Cs;

	public $testPath;

	public function setUp() {
		parent::setUp();

		$this->out = new TestCsShellOutput();
		$io = new ConsoleIo($this->out);

		$this->Cs = new CsShell($io);
		$this->Cs->initialize();

		$this->Cs->testPath = dirname(dirname(dirname(__FILE__))) . DS . 'test_app' . DS;
	}

	/**
	 * CsShellTest::testTokenize()
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

	/**
	 * CsShellTest::testRun()
	 *
	 * @return void
	 */
	public function testRun() {
		$folder = TMP . 'Cs' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		// normal output
		copy($this->Cs->testPath . 'Cs' . DS . 'test.php', $folder . 'test.php');
		$path = $folder . 'test.php';

		$result = $this->Cs->runCommand(array('run', $path), true);
		$this->assertSame(1, $result);

		$this->assertTrue(file_exists(TMP . 'phpcs.txt'));

		$result = file_get_contents(TMP . 'phpcs.txt');
		$this->assertTextContains('[x] Opening brace should be on a new line', $result);

		$output = $this->out->output;
		$this->assertTextContains('For details check the phpcs.txt file in your TMP folder', $output);
	}

	/**
	 * CsShellTest::testRunFix()
	 *
	 * @return void
	 */
	public function testRunFix() {
		$folder = TMP . 'Cs' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		// normal output
		copy($this->Cs->testPath . 'Cs' . DS . 'test.php', $folder . 'test.php');
		$path = $folder . 'test.php';

		$result = $this->Cs->runCommand(array('run', $path, '-f'), true);
		$this->assertSame(1, $result);

		$result = file_get_contents($folder . 'test.php');

		// Running it again should now show all OK (as auto-fixer corrected them).
		$result = $this->Cs->runCommand(array('run', $path), true);
		$this->assertSame(0, $result);

		$result = file_get_contents(TMP . 'phpcs.txt');
		$this->assertSame('', trim($result));

		$output = $this->out->output;
		//debug($output);

		$result = file_get_contents($folder . 'test.php');
		//debug($result);
	}

}