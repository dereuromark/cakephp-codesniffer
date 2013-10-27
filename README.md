# CakePHP CodeSniffer Plugin

Author: Mark Scherer

License: MIT

The plugin provides a quick way to run your (default) sniffer rules on your app - or part (Plugin for example) of it.
It comes with good default settings for Cake apps and works out of the box as self-contained system.

By default it
- ignores Plugin as well as webroot and Vendor folders (in app or plugin)
- creates a log file in TMP for larger error reports where the console screen can't hold that much information)
- NEW: Can now also auto-correct most standard CS issues using the phpcs-fixer branch.

It is also quite helpful when creating new sniffer rules (using tokenizer command).


## Requirements

CakePHP 2.x

This is a self-contained CakePHP-only plugin shipped with everything including phpcs and sniffs.
Drag and drop it. Run it. Enjoy.

Possible dependencies:

- my Tools Plugin (for some commands)

## How to use

1. Installation via Composer or Download
 
 a) Add this to your composer.json file (phpcs is included in this repo, not as a Composer dependency)

```
{
  "require" : {
    "dereuromark/cakephp-codesniffer": "dev-master""
  }
}
```
 b) Download the plugin and place it at `APP/Plugin/CodeSniffer` (or ROOT/plugins/ folder).

   ```bash
   cd APP/Plugin
   git clone git://github.com/dereuromark/cakephp-codesniffer.git CodeSniffer
   ```

2. Load the plugin by adding this line to the bottom of your app's `Config/bootstrap.php`:

   ```php
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

NEW: Upgrading the vendor phpcs package to "phpcs-fixer" branch, you can now leverage auto-correction
for some found errors using `--fix` or `-f`:

	cake CodeSniffer.Codesniffer run -f

It will also display a diff on old made changes afterwards.
Using the APP itself, a relative path inside the APP or `-p PluginName` it will by default skip any "Vendor"
and "webroot" folders found. Using an absolute custom path it will not skip anything:

	cake CodeSniffer.Codesniffer run /folder/to/check -f

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

* Detect Yoda conditions.
* IsNull sniff
* Type casting sniff
* @return doc block sniff
* FunctionName sniff
* ControlStructureSpacing
* LanguageConstructSpacing
* Ternary (incl. short ternary) sniff
* DocBlockEnding sniff
* ReturnEarly sniff to detect if a return statement is followed by an ELSE block.
* Doc blocks / comments on correct indentation level (as their subsequent code).
* Line endings on Windows are allowed to be \r\n (default for GIT on Windows for example).
* Make Squiz sniff not falsely report whitespace issues in Windows.

For details see the ruleset.xml in the `Standards` dir.

Most of those added sniffs also have auto-correction included. Use it :)

Note: The "correct" indentation is APP and plugin specific for me. This is the only exception from the official CakePHP
standards and applies to all my (non core) code.
You are free to stick to the official version using the "CakePHP" standard!

### MyCakePHPCore improvements (optional)

* Line endings on Windows are allowed to be \r\n (default for GIT on Windows for example)
* >> 10 new sniffs (see MyCakePHP)

For details see the ruleset.xml in the `Standards` dir.

### Writing new sniffs (and tests for it)

Using this phpcs-fixer branch it is a little bit different to write new sniffs.
You should switch `addError()` with `addFixableError()` and an actual fix where applicable.
Also, writing tests, we then also need an "expected" result to compare our fixed file to.

So basically, for a new sniff IsFoo in MyCakePHP, we need:

* is_foo_fail.php containing all the possible fails (if autocorrected or not)
* is_foo_pass.php containing all passes (and stuff that is just skipped)
* is_foo_expected.php (optional) containing the expected output of the fixed is_foo_fail.php file

Note that you should always put these headers into the fail file:

	// @expectedErrors 4
	// @expectedCorrections 2
	// @sniffs MyCakePHP.WhiteSpace.IsFoo

This asserts that other sniffs don't intefere, as we specifically test this sniff only.
`expectedErrors` and `expectedCorrections` can be used to assert the amount of fails that should be recognized and corrected.
The pass file only needs the `sniffs` part, obviously. The expect file should not contain those headers, at all.

Multiple sniffs can be used comma-separated. If you omit them, all the sniffs of this standard will be run. This is useful for
a complete test file where all sniffs should be checked combined and at once.

You can run the test files using phpunit as always:

	phpunit /path/to/plugin/CodeSniffer/Vendor/PHP/CodeSniffer/Standards/MyCakePHP/tests/Tests/NameOfTest.php

You should run this command from your APP folder.
It will then automatically patch in the plugin's phpcs-fixer branch version and the correct path for TMP.

Token-Tip: When starting to write a new sniff for something, create your pass and fail files first and use
the tokenizer command to tokenize those files into filename.php.token - this will give you the codes
of all used tokens and will make it easier to write sniffs for it. And use -v for verbose information!

Debug-Tip: Use above phpunit test case command with -v to have more debug output for easier developing and testing.

### Comparing sniffs

	cake CodeSniffer.Codesniffer compare [source] [target]

If you extend the CakePHP core standard (or any other), at some point you might want to compare them, regarding
whats sniffs are run. Sometimes, when adding a new sniff, one can easily forget to update the other standard's xml.
This way it can easily be detected - and corrected.

The source can be omitted and will fall back to the default one. The target can also be omitted and will be prompted then.

## TODOS

Installing via composer or some more generic approach? This would avoid the hardwiring of PHPCS and the standards
in this plugin.

Also: The main goal is to push forward automated code correction using the CodeFixer.
Using the same rules to "find" violations we should also be able to "fix" them right away in almost all cases.

Last but not least there is also some more work to be done on the SmellDetector and other tools.
