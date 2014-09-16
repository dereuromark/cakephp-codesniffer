<?php
namespace CodeSniffer\Test\TestCase\Shell;

use Cake\TestSuite\TestCase;
use CodeSniffer\Shell\MdShell;

/**
 * MdShellTest class
 *
 */
class MdShellTest extends TestCase {

	public $Md;

	public function setUp() {
		parent::setUp();

		$this->Md = new MdShell();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testBasic() {
		//$this->Md->run();
	}

}
