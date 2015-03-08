<?php
App::uses('AppShell', 'Console/Command');
App::uses('FolderLib', 'Tools.Utility');
App::uses('Utility', 'Tools.Utility');
App::uses('HttpSocket', 'Network/Http');

if (!defined('WINDOWS')) {
	if (DS == '\\' || substr(PHP_OS, 0, 3) == 'WIN') {
		define('WINDOWS', true);
	} else {
		define('WINDOWS', false);
	}
}

if (strpos(get_include_path(), VENDORS) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . VENDORS);
}
$pluginVendorPath = CakePlugin::path('CodeSniffer') . 'Vendor' . DS;
if (strpos(get_include_path(), $pluginVendorPath) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . $pluginVendorPath);
}

/**
 * CakePHP CodeSniffer plugin
 *
 * @copyright Copyright © Mark Scherer
 * @link http://www.dereuromark.de
 * @license MIT License
 */
class CodeSnifferShell extends AppShell {

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
		$this->out('<info>CodeSniffer plugin</info> for CakePHP', 2);

		if ($standard = Configure::read('CodeSniffer.standard')) {
			$this->standard = $standard;
		}
		parent::startup();
	}

	/**
	 * Catch-all for CodeSniffer commands
	 *
	 * @link http://pear.php.net/manual/en/package.php.php-codesniffer.usage.php
	 * @return void
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
			$path = CakePlugin::path(Inflector::camelize($this->params['plugin'])) . $path;
			$customPath = false;
		} elseif (empty($path)) {
			$path = APP;
			$customPath = false;
		}
		$path = realpath($path);
		if (empty($path)) {
			$this->error('Please provide a valid path.');
		}

		$_SERVER['argv'] = [];
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
			PHP_Timer::start();
		}

		$this->_process();
		$this->out('For details check the phpcs.txt file in your TMP folder.');
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

		$_SERVER['argv'] = [];
		$_SERVER['argv'][] = '--encoding=utf8';
		$standard = $this->standard;
		if ($this->params['standard']) {
			$standard = $this->params['standard'];
		}
		$_SERVER['argv'][] = '--standard=' . $standard;
		$_SERVER['argv'][] = $path;

		$_SERVER['argc'] = count($_SERVER['argv']);

		$res = [];

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
		$this->out('Tokenizing: ' . $path);
		$newPath = dirname($path) . DS . extractPathInfo('basename', $path) . '.token';
		file_put_contents($newPath, $content);
		$this->out('Filename: ' . $newPath);
	}

	/**
	 * CodeSnifferShell::_getTokens()
	 *
	 * @param string $path
	 * @return array Tokens
	 */
	protected function _getTokens($path) {
		include_once('PHP/CodeSniffer.php');
		$phpcs = new PHP_CodeSniffer();
		$phpcs->process([], $this->standard, []);

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
		$pieces = [];
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
				$tokenList = [];
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
		return [implode(' ', $pieces)];
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
			$this->error('Invalid standard');
		}

		$phpcs = new PHP_CodeSniffer_CLI();
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

		$both = $onlyFrom = $onlyTo = [];

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
		include_once 'PHP/CodeSniffer.php';
		return PHP_CodeSniffer::getInstalledStandards();
	}

	/**
	 * CodeSnifferShell::_sniffs()
	 *
	 * @return array
	 */
	protected function _sniffs($standard) {
		include_once 'PHP/CodeSniffer.php';
		$phpcs = new PHP_CodeSniffer();
		$phpcs->process([], $standard);
		$sniffs = $phpcs->getSniffs();
		$sniffs = array_keys($sniffs);
		sort($sniffs);

		$result = [];
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
	 * @return void
	 */
	public function test() {
		$this->_checkCodeSniffer();
	}

	/**
	 * CodeSnifferShell::install()
	 *
	 * - download latest codesniffer (TODO)
	 * - download latest CakePHP sniffs
	 *
	 * @return void
	 */
	public function install() {
		if (!CakePlugin::loaded('Tools')) {
			$this->error('This needs the Tools plugin');
		}
		$this->out('Downloading latest CodeSniffer package');
		$file = 'https://codeload.github.com/squizlabs/PHP_CodeSniffer/zip/phpcs-fixer';
		//$feed = 'http://pear.php.net/feeds/pkg_php_codesniffer.rss';
		$version = $this->_downloadCodeSniffer($file);

		$this->out('Downloading latest CakePHP rules');
		$file = 'https://codeload.github.com/cakephp/cakephp-codesniffer/zip/phpcs-fixer';
		$this->_downloadRules($file);

		$target = CakePlugin::path('CodeSniffer') . 'Vendor' . DS . 'PHP' . DS;
		$rulesTarget = $target . 'CodeSniffer' . DS . 'Standards' . DS . 'CakePHP' . DS;

		$this->out('Installing CodeSniffer package');
		$this->out('Manually copy the tmp files into your vendors folder!');
		$this->out('Installing PHP Codesniffer (phpcs-fixer branch)');
		$this->_installCodeSniffer($target);

		$this->out('Installing CakePHP rules (phpcs-fixer branch)');
		$this->_installRules($rulesTarget);

		//$this->out('Removing tmp folder', 1, Shell::VERBOSE);
		//$Folder = new FolderLib(TMP . 'cs' . DS);
		//$Folder->delete();
		$this->out('Installation complete :)');
	}

	/**
	 * CodeSnifferShell::_extract()
	 *
	 * @param mixed $file
	 * @return void
	 */
	protected function _extract($file) {
		chdir(dirname($file));

		if (WINDOWS && empty($this->params['os']) || !empty($this->params['os']) && $this->params['os'] == 'w') {
			$exePath = App::pluginPath('CodeSniffer') . 'Vendor' . DS . 'exe' . DS;
			$copyFile = str_replace('.tgz', '_.tgz', $file);
			$tarFile = str_replace('.tgz', '_.tar', $file);
			if (file_exists($copyFile)) {
				unlink($copyFile);
			}
			if (file_exists($tarFile)) {
				unlink($tarFile);
			}
			exec('cp ' . $file . ' ' . $copyFile);
			exec($exePath . 'gzip -dr ' . $copyFile);
			exec($exePath . 'tar -xvf ' . $tarFile);
		} else {
			exec('tar -xzf ' . $file);
		}
	}

	/**
	 * CodeSnifferShell::_extractZip()
	 *
	 * @param mixed $file
	 * @return boolean Success
	 */
	protected function _extractZip($file) {
		$Zip = new ZipArchive();
		if (!$Zip->open($file)) {
			$this->error('Cannot open zile file' . $file);
		}
		$Zip->extractTo(dirname($file));
		$Zip->close();
		return true;
	}

	/**
	 * CodeSnifferShell::_installCodeSniffer()
	 *
	 * @param mixed $target
	 * @return boolean Success
	 */
	protected function _installCodeSniffer($target) {
		$tmp = TMP . 'cs' . DS;
		$this->_extractZip($tmp . 'phpcs.zip');

		$folder = $tmp . 'PHP_CodeSniffer-phpcs-fixer' . DS;
		if (WINDOWS) {
			$windowsNewlines = strpos(file_get_contents(__FILE__), "\r\n") !== false;
			if ($windowsNewlines) {
				$this->_correctNewlines($folder);
			}
		}

		//Secure MyCakePHP and MyCakePHPCore rules
		$subfolder = 'CodeSniffer' . DS . 'Standards' . DS . 'MyCakePHP' . DS;
		$Folder = new FolderLib($target . $subfolder);
		$Folder->copy(['to' => $folder . $subfolder]);

		$subfolder = 'CodeSniffer' . DS . 'Standards' . DS . 'MyCakePHPCore' . DS;
		$Folder = new FolderLib($target . $subfolder);
		$Folder->copy(['to' => $folder . $subfolder]);

		// Clear the target folder
		$Folder = new FolderLib($target);
		$Folder->clear();

		// Move everything over now
		$Folder = new FolderLib($folder);
		return $Folder->copy(['to' => $target]);
	}

	/**
	 * Update CakePHP sniffs.
	 *
	 * @param string $target Absolute path to extract to.
	 * @return boolean Success
	 */
	protected function _installRules($target) {
		$tmp = TMP . 'cs' . DS;
		$this->_extractZip($tmp . 'cakephp.zip');

		$folder = $tmp . 'cakephp-codesniffer-phpcs-fixer' . DS;
		if (WINDOWS) {
			$windowsNewlines = strpos(file_get_contents(__FILE__), "\r\n") !== false;
			if ($windowsNewlines) {
				$this->_correctNewlines($folder);
			}
		}

		$Folder = new FolderLib($target);
		$Folder->clear();

		$Folder = new FolderLib($folder);
		return $Folder->copy(['to' => $target]);
	}

	/**
	 * CodeSnifferShell::_correctNewlines()
	 *
	 * @param string $folder
	 * @return void
	 */
	protected function _correctNewlines($folder) {
		$Iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder),
			RecursiveIteratorIterator::CHILD_FIRST);
		foreach ($Iterator as $path) {
			if ($path->isDir()) {
				continue;
		}
		$path = $path->__toString();
			file_put_contents($path, str_replace("\n", "\r\n", file_get_contents($path)));
		}
	}

	/**
	 * CodeSnifferShell::_downloadRules()
	 *
	 * @param string $url
	 * @return boolean Success
	 */
	protected function _downloadRules($url) {
		$tmp = TMP . 'cs' . DS;
		if (!is_dir($tmp)) {
			mkdir($tmp, 0770, true);
		}
		if (file_exists($tmp . 'cakephp.zip') && filemtime($tmp . 'cakephp.zip') > time() - HOUR) {
			$this->out('Found cakephp tmp files, skipping re-download.');
			return true;
		}
		$Http = new HttpSocket(['timeout' => MINUTE, 'ssl_verify_peer' => false, 'ssl_verify_host' => false]);
		$content = $Http->get($url);
		if ($content->code != 200) {
			$this->error('Could not download the cakephp rules from ' . $url);
		}
		if (!file_put_contents($tmp . 'cakephp.zip', $content)) {
			$this->error('Could not store the rules at ' . $tmp);
		}

		return true;
	}

	/**
	 * CodeSnifferShell::_downloadCodeSniffer()
	 *
	 * @param string $url
	 * @return boolean Success
	 */
	public function _downloadCodeSniffer($url) {
		$tmp = TMP . 'cs' . DS;
		if (!is_dir($tmp)) {
			mkdir($tmp, 0770, true);
		}
		if (file_exists($tmp . 'phpcs.zip') && filemtime($tmp . 'phpcs.zip') > time() - HOUR) {
			$this->out('Found phpcs tmp files, skipping re-download.');
			return true;
		}
		$Http = new HttpSocket(['timeout' => MINUTE, 'ssl_verify_peer' => false, 'ssl_verify_host' => false]);
		$content = $Http->get($url);
		if ($content->code != 200) {
			$this->error('Could not download the phpcs repo from ' . $url);
		}
		if (!file_put_contents($tmp . 'phpcs.zip', $content)) {
			$this->error('Could not store the rules at ' . $tmp);
		}

		return true;
	}

	/**
	 * CodeSnifferShell::_downloadCodeSniffer()
	 *
	 * @param string $url
	 * @return string Version
	 */
	protected function _downloadCodeSnifferPear($url) {
		$tmp = TMP . 'cs' . DS;
		if (!is_dir($tmp)) {
			mkdir($tmp, 0770, true);
		}
		if (file_exists($tmp . 'cs.version')) {
			if (!file_exists($tmp . 'cs.tgz')) {
				$this->error('Please clear your ' . $tmp . ' folder');
			}
			$this->out('Found codesniffer tmp files, skipping re-download.', 1, Shell::VERBOSE);
			return file_get_contents($tmp . 'cs.version');
		}
		$Http = new HttpSocket();
		$content = $Http->get($url);
		if ($content->code != 200) {
			$this->error('Could not read rss feed from ' . $url);
		}
		preg_match('/resource\=\"http\:\/\/pear\.php\.net\/package\/PHP_CodeSniffer\/download\/(.+?)\/\"/i', $content, $matches);
		if (empty($matches)) {
			$this->error('Could not find package in rss feed from ' . $url);
		}
		$version = $matches[1];
		$fileUrl = 'http://download.pear.php.net/package/PHP_CodeSniffer-' . $version . '.tgz';

		$Http = new HttpSocket(['timeout' => MINUTE]);
		$content = $Http->get($fileUrl);
		if ($content->code != 200) {
			$this->error('Could not download the cs package from ' . $fileUrl);
		}
		if (!file_put_contents($tmp . 'cs.tgz', $content)) {
			$this->error('Could not store the cs package at ' . $tmp);
		}
		file_put_contents($tmp . 'cs.version', $version);
		return $version;
	}

	/**
	 * Mess detector
	 *
	 * @return void
	 */
	public function phpmd() {
		if (!empty($this->params['version'])) {
			//return passthru('php '.VENDORS."PHP".DS."scripts".DS.'phpmd --help');
		}

		// Allow as much memory as possible by default
		if (extension_loaded('suhosin') && is_numeric(ini_get('suhosin.memory_limit'))) {
			$limit = ini_get('memory_limit');
			if (preg_match('(^(\d+)([BKMGT]))', $limit, $match)) {
				$shift = ['B' => 0, 'K' => 10, 'M' => 20, 'G' => 30, 'T' => 40];
				$limit = ($match[1] * (1 << $shift[$match[2]]));
			}
			if (ini_get('suhosin.memory_limit') > $limit && $limit > -1) {
				ini_set('memory_limit', ini_get('suhosin.memory_limit'));
			}
		} else {
			ini_set('memory_limit', -1);
		}

		// Check php setup for cli arguments
		if (!isset($_SERVER['argv']) && !isset($argv)) {
			fwrite(STDERR, 'Please enable the "register_argc_argv" directive in your php.ini', PHP_EOL);
			exit(1);
		}

		$_SERVER['argv'] = [];
		$_SERVER['argv'][] = 'phpcs';
		$_SERVER['argv'][] = VENDORS . 'PHP' . DS;
		$_SERVER['argv'][] = 'xml';
		$_SERVER['argv'][] = 'codesize';
		//$_SERVER['argv'][] = '--error-severity=1';
		//$_SERVER['argv'][] = '--warning-severity=1';
		//$_SERVER['argv'][] = '--config-show';

		$_SERVER['argc'] = count($_SERVER['argv']);

		// Load command line utility
		require_once 'PHP/PMD/TextUI/Command.php';

		// Run command line interface
		exit(PHP_PMD_TextUI_Command::main($_SERVER['argv']));
	}

	/**
	 * Check if CodeSniffer.phar is available
	 * Offer to install if it isn't available
	 */
	protected function _checkCodeSniffer() {
		$_SERVER['argv'] = [];
		$_SERVER['argv'][] = 'phpcs';
		$_SERVER['argv'][] = '--version';

		$this->_process();
	}

	/**
	 * CodeSnifferShell::_process()
	 *
	 * @return int Exit
	 */
	protected function _process() {
		include_once 'PHP/CodeSniffer/CLI.php';

		$phpcs = new PHP_CodeSniffer_CLI();
		$phpcs->checkRequirements();

		$cliValues = $phpcs->getCommandLineValues();

		if ($this->params['fix']) {
			// Override some of the command line settings that might be used and stop us
			// gettting a diff file.
			$diffFile = TMP . 'phpcbf-fixed.diff';

			$cliValues['generator'] = '';
			$cliValues['explain'] = false;
			$cliValues['reports'] = ['diff' => $diffFile];

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
				$output = [];
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
			$this->err('An error occured during processing.');
		}

		return $exit;
	}

	/**
	 * Add options from CodeSniffer
	 * or CakePHP's Shell will exit upon unrecognized options.
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addOptions([
			'help' => ['short' => 'h', 'boolean' => true],
			'quiet' => ['short' => 'q', 'boolean' => true],
			'verbose' => ['short' => 'v', 'boolean' => true],
			'no-interaction' => ['short' => 'n'],
			'standard' => [
				'short' => 's',
				'description' => 'Standard to use (defaults to CakePHP)',
				'default' => ''
			],
			'plugin' => [
				'short' => 'p',
				'description' => 'Plugin to use (combined with path subpath of this plugin).',
				'default' => ''
			],
			'ext' => [
				'short' => 'e',
				'description' => 'Extensions to check (comma separated list). Defaults to php. Use * to allow all extensions.',
				'default' => ''
			],
			'sniffs' => [
				'description' => 'Checking files for specific sniffs only (comma separated list). E.g.: Generic.PHP.LowerCaseConstant,CakePHP.WhiteSpace.CommaSpacing',
				'default' => ''
			],
			'fix' => [
				'short' => 'f',
				'description' => 'Fix right away: Auto-correct errors and warnings where possible.',
				'boolean' => true
			],
		])
		->addSubcommand('test', [
			'help' => __d('cake_console', 'Test CS and list its installed version.'),
			//'parser' => $parser
		])
		->addSubcommand('standards', [
			'help' => __d('cake_console', 'List available standards and the current default one.'),
			//'parser' => $parser
		])
		->addSubcommand('compare', [
			'help' => __d('cake_console', 'Compare available standards (diff).'),
		])
		->addSubcommand('tokenize', [
			'help' => __d('cake_console', 'Tokenize file as {filename}.token and store it in the same dir.'),
			//'parser' => $parser
		])
		->addSubcommand('run', [
			'help' => __d('cake_console', 'Run CS on the specified path.'),
			//'parser' => $parser
		])
		->addSubcommand('install', [
			'help' => __d('cake_console', 'Install/update current CakePHP Sniffs.'),
			//'parser' => $parser
		]);

		return $parser;
	}

}
