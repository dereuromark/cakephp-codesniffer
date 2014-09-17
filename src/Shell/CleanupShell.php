<?php
namespace CodeSniffer\Shell;

use Cake\Console\Shell;
use Cake\Core\Plugin;
use CodeSniffer\Lib\UseStatementSanitizer;

/**
 * CakePHP Cleanup shell
 *
 * @copyright Copyright © 2014 Mark Scherer
 * @link http://www.dereuromark.de
 * @license MIT License
 */
class CleanupShell extends Shell {

	protected $_customPaths = array();

	/**
	 * Welcome message
	 *
	 * @return void
	 */
	public function startup() {
		if (!empty($this->args[0])) {
			$customPath = realpath($this->args[0]);
		}
		if (!empty($customPath)) {
			$this->_customPaths[] = $customPath;
		}
		$this->out("<info>Codesniffer.Cleanup shell</info> for CakePHP", 2);
	}

	/**
	 * CsShell::unused_use()
	 *
	 * @return void
	 */
	public function unusedUse() {
		if (!empty($this->_customPaths)) {
			$this->_paths = $this->_customPaths;
		} elseif (!empty($this->params['plugin'])) {
			$pluginpath = Plugin::path($this->params['plugin']);
			$this->_paths = array($pluginpath);
		} else {
			$this->_paths = array(ROOT);
		}
		$this->out('Path: ' . implode(', ' , $this->_paths));
		$this->_findFiles('php');
		$this->out(count($this->_files) . ' files found. Checking ...');
		$this->count = 0;
		foreach ($this->_files as $file) {
			$this->out(sprintf('Checking %s...', $file), 1, Shell::VERBOSE);
			$this->_checkFile($file);
		}
		$this->out('Finished!');
		if ($this->count) {
			$this->out('A total of ' . $this->count . ' unused use statement(s) found.');
		}
	}

	/**
	 * CsShell::_checkFile()
	 *
	 * @param string $file File
	 * @return void
	 */
	protected function _checkFile($file) {
		$UseStatementSanitizer = $this->UseStatementSanitizer = new UseStatementSanitizer($file);
		$unused = $UseStatementSanitizer->getUnused($this->params['case-insensitive']);

		$file = str_replace(ROOT, '', $file);
		if ($unused) {
			$this->out($file . ':');
			foreach ($unused as $u) {
				$this->out(' - ' . $u);
			}
			$count = count($unused);
			$this->count += $count;
			$this->out('(' . $count . ' unused use statement(s) found)');
		} else {
			$this->out($file . ' OK', 1, Shell::VERBOSE);
		}
	}

	/**
	 * Searches the paths and finds files based on extension.
	 *
	 * @param string $extensions Extensions
	 * @return void
	 */
	protected function _findFiles($extensions = '') {
		$this->_files = array();
		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				continue;
			}
			$Iterator = new \RegexIterator(
				new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)),
				'/^.+\.(' . $extensions . ')$/i',
				\RegexIterator::MATCH
			);
			foreach ($Iterator as $file) {
				$excludes = array('vendor');
				//Iterator processes plugins/vendors even if not asked to
				if (empty($this->params['plugin'])) {
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
	 * Get option parser.
	 *
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->description(
			'Useful cleanup commands for CakePHP projects and more.'
		)->addSubcommand('unused_use', array(
			'help' => 'Check for unnecessary `use` statements.',
			'parser' => $parser
		))->addOption('dry-run', [
			'help' => 'Dry-run it.',
			'short' => 'd',
			'boolean' => true
		])->addOption('plugin', [
			'help' => 'Plugin name.',
			'short' => 'p',
			'default' => ''
		])->addOption('case-insensitive', [
			'help' => 'Case insensitive (as PHP does not care about casing).',
			'short' => 'i',
			'boolean' => true
		]);

		return $parser;
	}

}
