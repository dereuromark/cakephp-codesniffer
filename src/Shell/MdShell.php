<?php
/**
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CodeSniffer\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;

/**
 * Shell for PHPMD wrapper.
 *
 */
class MdShell extends Shell {

	/**
	 * Override main() for help message hook
	 *
	 * @return void
	 */
	public function main() {
		$this->out('Use as following:');
		$this->out('cake CodeSniffer.Md run [folder]');
		$this->out('You can also always directly use the `phpmd` command.'. 2);

		$this->out('Available formats: xml, text, html, display.');
		$this->out('Available rulesets: cleancode, codesize, controversial, design, naming, unusedcode, *.');
		$this->out('By default it will use `text` format outputted to TMP/report.txt and the `unusedcode` ruleset.');
	}

	/**
	 * MdShell::run()
	 *
	 * @return int Status
	 */
	public function run() {
		$this->out('Running PHPMD Mess Detector...', 2);

		$path = ROOT;
		if (!empty($this->params['plugin'])) {
			$path = Plugin::path($this->params['plugin']);
		}
		if (!empty($this->args)) {
			$path = realpath($this->args[0]);
		}
		if (!is_dir($path)) {
			return $this->error('Invalid path: ' . $path);
		}

		$this->params += (array)Configure::read('CodeSniffer');

		$options = array();
		if (!empty($this->params['reportfile'])) {
			$options['reportfile'] = $this->params['reportfile'];
		}
		if (!empty($this->params['suffixes']) && $this->params['suffixes'] !== '*') {
			$options['suffixes'] = $this->params['suffixes'];
		}
		if (!empty($this->params['exclude']) && empty($this->args[0])) {
			$options['exclude'] = $this->params['exclude'];
		}
		$format = $this->params['format'];
		$ruleset = $this->params['ruleset'];

		return $this->_run($path, $format, $ruleset, $options);
	}

	/**
	 * MdShell::_run()
	 *
	 * @param string $path
	 * @param string $format
	 * @param string $ruleset
	 * @param array $options
	 * @return int Status
	 */
	public function _run($path, $format = null, $ruleset = null, $options = array()) {
		$commandPath = dirname(dirname(dirname(__FILE__))) . DS . 'vendor' . DS . 'bin' . DS;
		if (!is_dir($commandPath)) {
			$commandPath = ROOT . DS . 'bin' . DS;
		}
		if (!is_file($commandPath . 'phpmd')) {
			return $this->error('Could not find bin dir with `phpmd` file.');
		}

		if (!$format) {
			$format = 'text';
		}

		if (!$ruleset) {
			$ruleset = 'unusedcode';
		}
		if ($ruleset === '*') {
			$ruleset = 'cleancode,codesize,controversial,design,naming,unusedcode';
		}

		if (empty($options['reportfile'])) {
			$options['reportfile'] = TMP . 'report.txt';
		}
		if ($format === 'display') {
			$format = 'text';
			unset($options['reportfile']);
		}

		if ($this->params['verbose'] && $options) {
			$this->out('Path: ' .$path);
			$this->out('Format: ' . $format);
			$this->out('Ruleset: ' . $ruleset);
			$this->out('Options:');
		}
		foreach ($options as $k => $v) {
			if ($this->params['verbose']) {
				$this->out('- ' . $k . ': ' . $v);
			}
			$options[$k] = '--' . $k . ' "'.$v.'"';
		}
		if ($this->params['verbose'] && $options) {
			$this->out();
		}

		$commandOptions = !empty($options) ? (' ' . implode(' ', $options)) : '';

		$command = $commandPath . 'phpmd ' . $path . ' ' . $format . ' ' . $ruleset . $commandOptions;
		if ($this->params['dry-run']) {
			$this->out('Executing:');
			$this->out('`' . $command . '`', 2);
			$ret = 0;
			$output = array();
		} else {
			exec($command, $output, $ret);
		}

		foreach ($output as $k => $v) {
			if ($v === '') {
				unset($output[$k]);
			}
		}

		$this->out('Done :)');
		if ($output) {
			$this->out('Found '. count($output).' issue(s):');
		}
		if ($output && $this->params['verbose']) {
			foreach ($output as $k => $v) {
				$output[$k] = str_replace(ROOT, '', $v);
			}
			$this->out($output);
		}

		if (!empty($options['reportfile'])) {
			$this->out('See the report file for details.');
		}

		return $ret;
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser->description(
			'CodeSniffer Md Shell to detect code issues.'
		)->addOption('dry-run', [
			'help' => 'Dry-run it.',
			'short' => 'd',
			'boolean' => true
		])->addOption('plugin', [
			'help' => 'Plugin name.',
			'short' => 'p',
			'default' => ''
		])->addOption('format', [
			'help' => 'Output format (text, html, xml). Defaults to text.',
			'short' => 'f',
			'default' => 'text'
		])->addOption('ruleset', [
			'help' => 'Ruleset. Defaults to unusedcode.',
			'short' => 'r',
			'default' => ''
		])->addOption('suffixes', [
			'help' => 'Allowed extensions. Defaults to `php`.',
			'short' => 's',
			'default' => 'php'
		])->addOption('exclude', [
			'help' => 'Excluded folders. Defaults to `vendor`',
			'short' => 'e',
			'default' => 'vendor,plugins,webroot,logs,tmp'
		])->addOption('reportfile', [
			'help' => 'Custom report file (absolute).',
			'default' => ''
		]);

		return $parser;
	}

}
