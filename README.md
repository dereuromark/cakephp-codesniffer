# CakePHP CodeSniffer Plugin

Author: Mark Scherer

License: MIT

This is quite helpful when creating new sniffer rules (using tokenizer command) or to quickly run
your default standard sniffs against your CakePHP app. It also provides some good default values regarding
default settings in general.

## Requirements

CakePHP v2.x

## How to use

1. Download the plugin and place it at `APP/Plugin/CodeSniffer` (or ROOT/plugins/ folder).

   ```bash
   cd APP/Plugin
   git clone git://github.com/dereuromark/cakephp-codesniffer.git CodeSniffer
   ```

2. Load the plugin by adding this line to the bottom of your app's `Config/bootstrap.php`:

   ```php
   <?php
   CakePlugin::load('CodeSniffer'); // or just CakePlugin::loadAll();
   ```

3. That's all! CodeSniffer is ready for use.

   ```bash
   cake CodeSniffer.CodeSniffer run [path]
   ```

If you do not provide a path, it will automatically run the sniffer for your APP path.

You can also quickly sniff one of your plugins:

	cake CodeSniffer.CodeSniffer run -p Tools

And by providing a path, as well, it will use it as sub path of your plugin:

	cake CodeSniffer.CodeSniffer run Model -p Tools

Note that it will also create a full debug log file in your TMP folder. This is useful if there are a lot
of errors and warnings.

If you want to display a list of available standards, use the "standards" command:

	cake CodeSniffer.Codesniffer standards

You can also check only a specific sniff:

	cake CodeSniffer.CodeSniffer run /folder/to/check --standard=MyCakePHP
		--sniffs=MyCakePHP.ControlStructures.ReturnEarly

### Settings/Options

By default it uses the CakePHP rules.
You can overwrite the default at runtime or globally using your APP configs:

	// Use our own standards "MyCakePHP" as default
	Configure::write('CodeSniffer.standard', 'MyCakePHP');

	// A "Custom" standard that is somewhere else on your file system
	Configure::write('CodeSniffer.standard', '/absolute/path/to/Custom');

### Tokenizer

You can use the tokenizer command to debug your PHP files. This can be very useful when writing
your own rules. You can output just the token name list, but it is usually better to use the verbose
output:

	cake CodeSniffer.Codesniffer tokenize /path/to/file.ext -v

This will create a file `/path/to/file.ext.token` with all token names added in comment lines.

### MyCakePHP improvements (optional)

* Doc blocks / comments on correct indentation level (as their subsequent code).
* Line endings on Windows are allowed to be \r\n (default for GIT on Windows for example).
* ReturnEarly sniff to detect if a return statement is followed by an ELSE block.
* Make Squiz sniff not falsely report whitespace issues in Windows.

For details see the ruleset.xml in the `Standards` dir.

Note: The "correct" indentation is APP and plugin specific for me. This is the only exception from the official CakePHP
standards and applies to all my (non core) code.
You are free to stick to the official version using the "CakePHP" standard!

### MyCakePHPCore improvements (optional)

* Line endings on Windows are allowed to be \r\n (default for GIT on Windows for example)

## TODOS

Installing via composer or some more generic approach? This would avoid the hardwiring of PHPCS and the standards
in this plugin.

Also: The main goal is to push forward automated code correction using the CodeFixer.
Using the same rules to "find" violations we should also be able to "fix" them right away in almost all cases.

Last but not least there is also some more work to be done on the SmellDetector and other tools.