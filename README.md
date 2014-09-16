# CakePHP CodeSniffer Plugin

[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-codesniffer.png?branch=3.0)](https://travis-ci.org/dereuromark/cakephp-codesniffer)
[![License](https://poser.pugx.org/dereuromark/cakephp-codesniffer/license.svg)](https://packagist.org/packages/dereuromark/cakephp-codesniffer)

**Rewrite for CakePHP 3.0**

Author: Mark Scherer

The plugin provides a quick way to run your (default) sniffer rules on your app - or part (plugin for example) of it.
It comes with good default settings for CakePHP apps and works out of the box as self-contained system.
Additionally it allows the usual CakePHP handling regarding shells and adds a few goodies on top.

You can also always just run the base commands manually, but the shell really helps to keep things DRY and easy.

## Requirements

CakePHP 3.x

This is a self-contained CakePHP-only plugin shipped with everything including phpcs, phpmd and sniffs.
Drag and drop it. Run it. Enjoy.

Possible dependencies see composer.json

## How to use

1. Installation via Composer

   a) Add this to your composer.json file (phpcs is included in this repo, not as a Composer dependency)

   ```
   {
       "require" : {
           "dereuromark/cakephp-codesniffer": "3.0.x-dev"
       }
   }
   ```

2. Load the plugin by adding this line to the bottom of your app's `Config/bootstrap.php`:

   ```php
   Plugin::load('CodeSniffer'); // or just Plugin::loadAll();
   ```

3. That's all! CodeSniffer is ready for use.

   ```bash
   cake CodeSniffer.[ShellName] run [/some/optional/path]
   ```

### PHPMD Mess Detector
The PHPMD lib is included as the following command:
```bash
	cake CodeSniffer.Md run [/some/optional/path]
 ```
If you do not provide a path, it will automatically run the sniffer for your APP (root) path, (usually `/src` + `/tests`).

By default it
- only looks at PHP files.
- only uses the unused code sniff (as the others are sometimes somewhat controversal).
- ignores `plugins`, `vendor`, `webroot` folders etc (in app or plugin - with no custom path argument set)
- creates a log file in TMP for larger error reports where the console screen can't hold that much information)

Tips:
- Use `-v` for more output/infos.
- To run it on a plugin, use `-p PluginName`.
- To display on screen instead of logging away use `-f display`.
- To use all rulesets, use `-r *`
- To allow any suffix, use `-s *`
- To exlude custom folders, use `-e list,of,folders`

Pro-tips:
- To dry-run it, use `-d`. It will show the resulting command that would be executed.
- You can use or mix custom rule sets: `-r codesize,/my/rules.xml`.
- You can specicy multiple source directories in case you want to create one output for certain parts of your code:
  `cake CodeSniffer.Md run /path1/,/path2,...`.
- Leverage the exit codes 0,1,2 for Travis/Jenkins CI.
- You can use shell aliasing to make the command shorter - even `md [/some/optional/path]` etc.

For details and more tips see [github.com/phpmd/phpmd](https://github.com/phpmd/phpmd).

### Cleanup
The Cleanup shell has some nice tools for code cleanup:
```bash
	cake CodeSniffer.Cleanup unused_use [/some/optional/path]
 ```
If you do not provide a path, it will automatically run the sniffer for your APP (root) path, (usually `/src` + `/tests`).

By default it checks all PHP files for the use statements and which are not necessary because the included classes are never used.

Tips:
- Use `-v` for more output/infos.
- To run it on a plugin, use `-p PluginName`.

### PHPCS
... coming up

### Settings/Options

By default it uses the pre-defined settings.
You can overwrite the default at runtime or globally using your APP configs:
```php
// Use our own ruleset "codesize" as default
Configure::write('CodeSniffer.ruleset', 'codesize');

// A "custom" ruleset that is somewhere else on your file system
Configure::write('CodeSniffer.ruleset', '/absolute/path/to/custom.xml');
```

Same applies for all other config keys.

## TODOS
There is also some more work to be done on the SmellDetector and other tools.
