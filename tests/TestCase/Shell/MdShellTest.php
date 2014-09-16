<?php
namespace CodeSniffer\Test\TestCase\Shell;

use Cake\TestSuite\TestCase;
use CodeSniffer\Shell\MdShell;

/**
 * StringTest class
 *
 */
class StringTest extends TestCase {

	public function setUp() {
		parent::setUp();
		$this->Telegram = new Telegram();
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Text);
	}

	public function testContactList() {
		$Client = $this->Telegram->createClient();

		$contactList = $Client->getContactList();
		debug($contactList);
	}

	public function _testDialog() {
		$Client = $this->Telegram->createClient();

		$unreadMessages = $Client->getDialogList();
		debug($unreadMessages);
	}

}
