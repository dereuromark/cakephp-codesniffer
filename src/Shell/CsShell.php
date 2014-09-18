<?php
namespace CodeSniffer\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Utility\Inflection;
use CodeSniffer\Utility\Utility;

if (!defined('WINDOWS')) {
	if (DS == '\\' || substr(PHP_OS, 0, 3) == 'WIN') {
		define('WINDOWS', true);
	} else {
		define('WINDOWS', false);
	}
}
/*
if (strpos(get_include_path(), VENDORS) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . VENDORS);
}
$pluginVendorPath = Plugin::path('CodeSniffer') . 'Vendor' . DS;
if (strpos(get_include_path(), $pluginVendorPath) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . $pluginVendorPath);
}
*/

/**
 * CakePHP Cs shell
 *
 * @copyright Copyright © Mark Scherer
 * @link http://www.dereuromark.de
 * @license MIT License
 */
class CsShell extends Shell {

	public $standard = 'CakePHP';

	public $ext = 'php';

	/**
	 * Directory where CodeSniffer sniffs resides
	 */
	public $sniffsDir;

	/**
	 * Welcome message
	 */
	public function startup() {
		$this->out('<info>CodeSniffer.Cs shell</info> for CakePHP', 2);

		if ($standard = Configure::read('CodeSniffer.standard')) {
			$this->standard = $standard;
		}
		$this->standard = 'Zend';

		parent::startup();
	}

	/**
	 * Catch-all for CodeSniffer commands
	 *
	 * @link http://pear.php.net/manual/en/package.php.php-codesniffer.usage.php
	 * @return int Exit code
	 */
	public function run() {
		// for larger PHP files we need some more memory
		ini_set('memory_limit', '512M');

		$path = null;
		$customPath = false;
		if (!empty($this->args)) {
			$path = $this->args[0];
			$customPath = true;
		}
		if (!empty($this->params['plugin'])) {
			$path = Plugin::path(Inflector::camelize($this->params['plugin'])) . $path;
			$customPath = false;
		} elseif (empty($path)) {
			$path = APP;
			$customPath = false;
		}
		$path = realpath($path);
		if (empty($path)) {
			$this->error('Please provide a valid path.');
		}

		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = '--encoding=utf8';
		$standard = $this->standard;
		if ($this->params['standard']) {
			$standard = $this->params['standard'];
		}
		$_SERVER['argv'][] = '--standard=' . $standard;
		if ($this->params['sniffs']) {
			$_SERVER['argv'][] = '--sniffs=' . $this->params['sniffs'];
		}

		$_SERVER['argv'][] = '--report-file=' . TMP . 'phpcs.txt';
		if (!$this->params['quiet']) {
			$_SERVER['argv'][] = '-p';
		}
		if ($this->params['verbose']) {
			$_SERVER['argv'][] = '-v';
			$_SERVER['argv'][] = '-s';
		}
		//$_SERVER['argv'][] = '--error-severity=1';
		//$_SERVER['argv'][] = '--warning-severity=1';

		if (!$customPath) {
			$ignored = '--ignore=_*,*__*,*/webroot/*,*/Vendor/*';
			if (empty($this->params['plugin'])) {
				$ignored .= ',*/Plugin/*';
			}
			$_SERVER['argv'][] = $ignored;
		}

		$ext = $this->ext;
		if ($this->params['ext'] === '*') {
			$ext = '';
		} elseif ($this->params['ext']) {
			$ext = $this->params['ext'];
		}
		if ($ext) {
			$_SERVER['argv'][] = '--extensions=' . $ext;
		}

		$_SERVER['argv'][] = $path;

		$_SERVER['argc'] = count($_SERVER['argv']);

		// Optionally use PHP_Timer to print time/memory stats for the run.
		// Note that the reports are the ones who actually print the data
		// as they decide if it is ok to print this data to screen.
		@include_once 'PHP/Timer.php';
		if (class_exists('PHP_Timer', false) === true) {
			\PHP_Timer::start();
		}

		$exit = $this->_process();
		$this->out('For details check the phpcs.txt file in your TMP folder.');
		return $exit;
	}

	/**
	 * Tokenize a specific file like `/path/to/file.ext`.
	 * Creates a file `/path/to/file.ext.token` with all token names
	 * added in comment lines.
	 *
	 * @return void
	 */
	public function tokenize() {
		if (!empty($this->args)) {
			$path = $this->args[0];
			$path = realpath($path);
		}
		if (empty($path) || !is_file($path)) {
			$this->error('Please select a path to a file');
		}

		$res = array();

		$tokens = $this->_getTokens($path);
		$array = file($path);

		foreach ($array as $key => $row) {
			$res[] = rtrim($row);
			if ($tokenStrings = $this->_tokenize($key + 1, $tokens)) {
				foreach ($tokenStrings as $string) {
					$res[] = '// ' . $string;
				}
			}
		}
		$content = implode(PHP_EOL, $res);
		$this->out('Tokenizing file:');
		$this->out('- ' . $path);
		$newPath = dirname($path) . DS . pathinfo($path, PATHINFO_BASENAME) . '.token';
		file_put_contents($newPath, $content);
		$this->out('Outpit filename:');
		$this->out('- ' . $newPath);
	}

	/**
	 * CodeSnifferShell::_getTokens()
	 *
	 * @param string $path
	 * @return array Tokens
	 */
	protected function _getTokens($path) {
		$phpcs = new \PHP_CodeSniffer();
		$phpcs->process(array(), $this->standard, array());

		$file = $phpcs->processFile($path);
		$file->start();
		return $file->getTokens();
	}

	/**
	 * CodeSnifferShell::_tokenize()
	 *
	 * @param integer $row
	 * @param array $tokens
	 * @return array
	 */
	protected function _tokenize($row, $tokens) {
		$pieces = array();
		foreach ($tokens as $key => $token) {
			if ($token['line'] > $row) {
				break;
			}
			if ($token['line'] < $row) {
				continue;
			}
			if ($this->params['verbose']) {
				$type = $token['type'];
				unset($token['type']);
				unset($token['content']);
				unset($token['code']);
				$tokenList = array();
				foreach ($token as $k => $v) {
					if (is_array($v)) {
						if (empty($v)) {
							continue;
						}
						$v = json_encode($v);
					}
					$tokenList[] = $k . '=' . $v;
				}
				$pieces[] = $type . ' (' . $key . ') ' . implode(', ', $tokenList);
			} else {
				$pieces[] = $token['type'];
			}
		}
		if ($this->params['verbose']) {
			return $pieces;
		}
		return array(implode(' ', $pieces));
	}

	/**
	 * Convert options to string
	 *
	 * @param array $options Options array
	 * @return string Results
	 */
	protected static function _optionsToString($options) {
		if (empty($options) || !is_array($options)) {
			return '';
		}
		$results = '';
		foreach ($options as $option => $value) {
			if (strlen($results) > 0) {
				$results .= ' ';
			}
			if (empty($value)) {
				$results .= "--$option";
			}
			else {
				$results .= "--$option=$value";
			}
		}

		return $results;
	}

	/**
	 * List all available standards
	 *
	 * @return void
	 */
	public function standards() {
		$this->out('Current standard: ' . $this->standard, 2);

		$standards = $this->_standards();

		$this->out('The installed coding standards are:');
		$this->out(implode(', ', $standards));

		$standard = $this->standard;
		if (!empty($this->args[0])) {
			$standard = $this->args[0];
		}
		if (!in_array($standard, $standards)) {
			$this->error('Invalid standard `'. $standard . '`');
		}

		$phpcs = new \PHP_CodeSniffer_CLI();
		$phpcs->explainStandard($standard);
	}

	/**
	 * CodeSnifferShell::compare()
	 *
	 * @return void
	 */
	public function compare() {
		$from = $this->standard;

		$available = $this->_standards();

		if (count($this->args) > 1) {
			$to = $this->args[1];
			$from = $this->args[0];
		} elseif (count($this->args) === 1) {
			$to = $this->args[0];
		} else {
			$to = $this->in('Compare ' . $from . ' to ...', $available);
		}
		if (!$to) {
			return $this->error('Invalid source or target');
		}

		$sniffsFrom = $this->_sniffs($from);
		$sniffsTo = $this->_sniffs($to);

		$both = $onlyFrom = $onlyTo = array();

		foreach ($sniffsFrom as $sniff) {
			if (!in_array($sniff, $sniffsTo)) {
				$onlyFrom[] = $sniff;
			} else {
				$both[] = $sniff;
			}
		}
		foreach ($sniffsTo as $sniff) {
			if (!in_array($sniff, $sniffsFrom)) {
				$onlyTo[] = $sniff;
			} elseif (!in_array($sniff, $both)) {
				$both[] = $sniff;
			}
		}

		$fromText = $from . ' (' . count($sniffsFrom) . ' sniffs)';
		$toText = $to . ' (' . count($sniffsTo) . ' sniffs)';
		$this->out(sprintf('Comparing %s to %s:', $fromText, $toText), 2);

		if ($onlyFrom) {
			$onlyFrom = Utility::expandList($onlyFrom);

			$this->out($from . ' has the following sniffs ' . $to . ' does not have:');
			foreach ($onlyFrom as $name => $groups) {
				foreach ($groups as $group => $sniffs) {
					$this->out(' * ' . $name . '.' . $group);
					foreach ($sniffs as $sniff) {
						$this->out('   - ' . $sniff);
					}
				}
			}
			$this->out();
		}

		if ($onlyTo) {
			$onlyTo = Utility::expandList($onlyTo);

			$this->out($to . ' has the following sniffs ' . $from . ' does not have:');
			foreach ($onlyTo as $name => $groups) {
				foreach ($groups as $group => $sniffs) {
					$this->out(' * ' . $name . '.' . $group);
					foreach ($sniffs as $sniff) {
						$this->out('   - ' . $sniff);
					}
				}
			}
			$this->out();
		}

		if ($both) {
			$both = Utility::expandList($both);

			$this->out($to . ' and ' . $from . ' both the following sniffs:', 1, Shell::VERBOSE);
			foreach ($both as $name => $groups) {
				foreach ($groups as $group => $sniffs) {
					$this->out(' * ' . $name . '.' . $group, 1, Shell::VERBOSE);
					foreach ($sniffs as $sniff) {
						$this->out('   - ' . $sniff, 1, Shell::VERBOSE);
					}
				}
			}
			$this->out();
		}
	}

	/**
	 * CodeSnifferShell::_standards()
	 *
	 * @return array
	 */
	protected function _standards() {
		//include_once 'PHP/CodeSniffer.php';
		return \PHP_CodeSniffer::getInstalledStandards();
	}

	/**
	 * CodeSnifferShell::_sniffs()
	 *
	 * @return array
	 */
	protected function _sniffs($standard) {
		$phpcs = new \PHP_CodeSniffer();
		$phpcs->process(array(), $standard);
		$sniffs = $phpcs->getSniffs();
		$sniffs = array_keys($sniffs);
		sort($sniffs);

		$result = array();
		foreach ($sniffs as $sniff) {
			$result[] = $this->_formatSniff($sniff);
		}
		return $result;
	}

	protected function _formatSniff($sniff) {
		$parts = explode('_', str_replace('\\', '_', $sniff));
		return $parts[0] . '.' . $parts[2] . '.' . substr($parts[3], 0, -5);
	}

	/**
	 * @return int Exit code
	 */
	public function test() {
		return $this->_checkCodeSniffer();
	}

	/**
	 * Check if CodeSniffer.phar is available
	 * Offer to install if it isn't available
	 *
	 * @return int Exit code
	 */
	protected function _checkCodeSniffer() {
		$_SERVER['argv'] = array();
		$_SERVER['argv'][] = 'phpcs';
		$_SERVER['argv'][] = '--version';

		return $this->_process();
	}

	/**
	 * CodeSnifferShell::_process()
	 *
	 * @return int Exit code
	 */
	protected function _process() {
		$phpcs = new \PHP_CodeSniffer_CLI();
		$phpcs->checkRequirements();

		$cliValues = $phpcs->getCommandLineValues();

		if ($this->params['fix']) {
			// Override some of the command line settings that might be used and stop us
			// gettting a diff file.
			$diffFile = TMP . 'phpcbf-fixed.diff';

			$cliValues['generator'] = '';
			$cliValues['explain'] = false;
			$cliValues['reports'] = array('diff' => $diffFile);

			if (file_exists($diffFile) === true) {
				unlink($diffFile);
			}
		}
		$numErrors = $phpcs->process($cliValues);

		$exit = 0;
		if ($this->params['fix']) {
			if (file_exists($diffFile) === false) {
				// Nothing to fix.
				if ($numErrors === 0) {
					// And no errors reported.
					$exit = 0;
				} else {
					// Errors we can't fix.
					$exit = 2;
				}
			} else {
				$cmd = "patch -p0 -ui \"$diffFile\"";
				$output = array();
				$retVal = null;
				exec($cmd, $output, $retVal);
				unlink($diffFile);

				if ($retVal === 0) {
					// Everything went well.
					$filesPatched = count($output);
					echo "Patched $filesPatched files\n";
					$exit = 1;
				} else {
					print_r($output);
					echo "Returned: $retVal\n";
					$exit = 3;
				}
			}
		}

		if ($numErrors !== 0) {
			if ($exit === 0) {
				$exit = 1;
			}
			$this->out('An error occured during processing.');
		}

		return $exit;
	}

	/**
	 * Add options from CodeSniffer
	 * or CakePHP's Shell will exit upon unrecognized options.
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->description(
			'CodeSniffer Md Shell to detect code issues.'
		)->addOption('no-interaction', [
			'help' => 'No Interaction.',
			'short' => 'n',
			'boolean' => true
		])->addOption('plugin', [
			'help' => 'Plugin to use (combined with path subpath of this plugin).',
			'short' => 'p',
			'default' => ''
		])->addOption('standard', [
			'help' => 'Standard to use (defaults to CakePHP).',
			'short' => 's',
			'default' => ''
		])->addOption('ext', [
			'help' => 'Extensions to check (comma separated list). Defaults to php. Use * to allow all extensions.',
			'short' => 'e',
			'default' => ''
		])->addOption('sniffs', array(
				'help' => 'Checking files for specific sniffs only (comma separated list). E.g.: Generic.PHP.LowerCaseConstant,CakePHP.WhiteSpace.CommaSpacing',
				'default' => ''
		))->addOption('fix', [
			'help' => 'Fix right away: Auto-correct errors and warnings where possible.',
			'short' => 'f',
			'boolean' => true
		])->addSubcommand('test', array(
			'help' => 'Test CS and list its installed version.',
		))->addSubcommand('standards', array(
			'help' => 'List available standards and the current default one.',
		))->addSubcommand('compare', array(
			'help' => 'Compare available standards (diff).',
		))->addSubcommand('run', array(
			'help' => 'Run CS on the specified path.',
		))->addSubcommand('tokenize', array(
			'help' =>  'Tokenize file as {filename}.token and store it in the same dir.',
			'parser' => [
				'description' => 'Tokenize file as {filename}.token and store it in the same dir.',
				'arguments' => [],
				'options' => []
			]
		));

		return $parser;
	}

}
