<?php
$pluginVendorPath = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))) . DIRECTORY_SEPARATOR;
if (strpos(get_include_path(), $pluginVendorPath) === false) {
	set_include_path(get_include_path() . PATH_SEPARATOR . $pluginVendorPath);
}

require_once 'PHP/CodeSniffer/CLI.php';

class TestHelper {

	protected $_rootDir;
	protected $_dirName;
	protected $_phpcs;

	public function __construct() {
		$this->_rootDir = dirname(dirname(__FILE__));
		$this->_dirName = basename($this->_rootDir);

		set_include_path(
			$this->_rootDir .
			PATH_SEPARATOR .
			$this->_rootDir . '/Sniffs' .
			PATH_SEPARATOR .
			get_include_path()
		);
		$this->_phpcs = new PHP_CodeSniffer_CLI();
		spl_autoload_register(array($this, 'autoload'), true, true);
	}

	/**
	 * Because PHPCS will assume our classes will contain the name
	 * of the checked out code directory we have to make a class that matches that.
	 *
	 * @param string $class The classname to try and load.
	 */
	public function autoload($class) {
		$originalClass = $class;
		if (strpos($class, $this->_dirName) !== false) {
			$class = str_replace($this->_dirName, 'Squiz', $class);
		}
		if (class_exists($class, false)) {
			eval('class ' . $originalClass . ' extends ' . $class . '{}');
		}
	}

	/**
	 * Run PHPCS on a file.
	 * Always run phpunit relative from app folder, this will make sure
	 * the tmp files are stored in the app's TMP folder.
	 *
	 * Full debug output with -v for phpunit:
	 *
	 *   phpunit /path/to/Test.php -v
	 *
	 * @param string $file to run.
	 * @return string The output from phpcs.
	 */
	public function runPhpCs($file, $sniffs = null, $fixer = false) {
		$options = $this->_phpcs->getDefaults();
		$options = array_merge($options, array(
			'encoding' => 'utf-8',
			'files' => array($file),
			'standard' => array($this->_rootDir . '/ruleset.xml'),
		));
		if ($sniffs) {
			if (!is_array($sniffs)) {
				$sniffs = explode(',', $sniffs);
			}
			$options['sniffs'] = $sniffs;
		}
		if ($fixer) {
			$path = getcwd() . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;
			$diffFile = $path . 'phpcbf-fixed.diff';
			$options['reports'] = array('diff' => $diffFile);
			if (file_exists($diffFile)) {
				unlink($diffFile);
			}
		}

		ob_start();
		$this->_phpcs->process($options);
		$result = ob_get_contents();
		ob_end_clean();

		// Small hack to have easy debug output for testing and creating new sniffs
		global $argv;
		$isVerbose = !empty($argv) && in_array('-v', $argv, true);

		preg_match("/FOUND (\d+) ERROR/", $result, $matches);
		$actualErrors = $matches ? $matches[1] : 0;

		if (!$fixer) {
			if ($isVerbose) {
				echo 'File: ' . basename($file) . PHP_EOL;
				echo "Errors: $actualErrors\n";
			}
			return $result;
		}

		if (!file_exists($diffFile)) {
			return null;
		}
		$diff = file_get_contents($diffFile);

		if ($isVerbose) {
			echo PHP_EOL;
			print_r($diff);
			echo PHP_EOL;
		}

		$outputFile = $path . 'phpcbf-fixed.output';
		$cmd = "patch -p0 -ui \"$diffFile\" --output=\"$outputFile\"";
		$debugOutput = array();
		$retVal = null;
		exec($cmd, $debugOutput, $retVal);

		$output = file_get_contents($outputFile);
		if (!$isVerbose) {
			unlink($diffFile);
			unlink($outputFile);
		}

		if ($isVerbose) {
			echo PHP_EOL;
			print_r($output);
			echo PHP_EOL.PHP_EOL;
		}

		// Remove test specific stuff
		$output = preg_replace('/\/\/\s*@expectedErrors [\d]+\s/i', '', $output);
		$output = preg_replace('/\/\/\s*@expectedCorrections [\d]+\s/i', '', $output);
		$output = preg_replace('/\/\/\s*@sniffs\s*([\,\.\w]+)\b\s/i', '', $output);

		if ($retVal === 0) {
			// Everything went well.
			//$exit = 1;

			return $output;
		}

		echo '*** ERROR ***' . PHP_EOL;
		print_r($debugOutput);

		//print_r(file_get_contents($outputFile . '.rej'));
		echo "Returned: $retVal\n";
		//$exit = 3;
		echo '***' . PHP_EOL;

		return;
	}

}
