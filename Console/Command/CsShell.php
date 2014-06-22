<?php
App::uses('AppShell', 'Console/Command');
App::uses('UseStatementSanitizer', 'CodeSniffer.Lib');

/**
 * CakePHP CS shell
 *
 * @copyright Copyright © 2014 Mark Scherer
 * @link http://www.dereuromark.de
 * @license MIT License
 */
class CsShell extends AppShell {

	public $report = array();

	protected $_customPaths = array();

	/**
	 * Welcome message
	 */
	public function startup() {
		if (!empty($this->args[0])) {
			$cutomPath = realpath($this->args[0]);
		}
		if ($cutomPath) {
			$this->_customPaths[] = $cutomPath;
		}
		$this->out("<info>CS shell</info> for CakePHP", 2);
	}

	/**
	 * CsShell::unused_use()
	 *
	 * @return void
	 */
	public function unused_use() {
		if (!empty($this->_customPaths)) {
			$this->_paths = $this->_customPaths;
		} elseif (!empty($this->params['plugin'])) {
			$pluginpath = App::pluginPath($this->params['plugin']);
			$this->_paths = array($pluginpath);
		} else {
			$this->_paths = array(APP);
		}
		$this->_findFiles('php');
		$this->out(count($this->_files) . ' files');
		foreach ($this->_files as $file) {
			$this->out(__d('cake_console', 'Checking %s...', $file), 1, Shell::VERBOSE);
			$this->_checkFile($file);
		}
	}

	/**
	 * CsShell::_checkFile()
	 *
	 * @param string $file
	 * @return void
	 */
	protected function _checkFile($file) {
		$UseStatementSanitizer = $this->UseStatementSanitizer = new UseStatementSanitizer($file);
		$unused = $UseStatementSanitizer->getUnused();

		if ($unused) {
			$this->out($file . ':');
			foreach ($unused as $u) {
				$this->out(' - ' . $u);
			}
		} else {
			$this->out($file . ' OK', 1, Shell::VERBOSE);
		}
	}

	/**
	 * Searches the paths and finds files based on extension.
	 *
	 * @param string $extensions
	 * @return void
	 */
	protected function _findFiles($extensions = '') {
		$this->_files = array();
		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				continue;
			}
			$Iterator = new RegexIterator(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
				'/^.+\.(' . $extensions . ')$/i',
				RegexIterator::MATCH
			);
			foreach ($Iterator as $file) {
				$excludes = array('Vendor', 'vendors');
				//Iterator processes plugins/vendors even if not asked to
				if (empty($this->params['plugin'])) {
					$excludes[] = 'Plugin';
					$excludes[] = 'plugins';
				}

				$isIllegalPath = false;
				foreach ($excludes as $exclude) {
					if (strpos($file, $path . $exclude . DS) === 0) {
						$isIllegalPath = true;
						break;
					}
				}
				if ($isIllegalPath) {
					continue;
				}

				if ($file->isFile()) {
					$this->_files[] = $file->getPathname();
				}
			}
		}
	}

	/**
	 * Add options
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->addOptions(array(
			'dry-run' => array(
				'short' => 'd', 'boolean' => true, 'help' => 'Dry Run'
			),
			'plugin' => array('short' => 'p', 'help' => 'Plugin', 'default' => ''),
			//'version' => array('short' => 'V', 'boolean' => true),
			'no-interaction' => array('short' => 'n')
		))
		->addSubcommand('unused_use', array(
			'help' => __d('cake_console', 'Run CS for use statements.'),
			//'parser' => $parser
		));

		return $parser;
	}

}
