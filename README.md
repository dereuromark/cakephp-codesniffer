# CakePHP CodeSniffer Plugin

**Rewrite for CakePHP 3.0**

Author: Mark Scherer

License: MIT

The plugin provides a quick way to run your (default) sniffer rules on your app - or part (Plugin for example) of it.
It comes with good default settings for CakePHP apps and works out of the box as self-contained system.

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
       "dereuromark/cakephp-codesniffer": "3.0-dev"
     }
   }
   ```

2. Load the plugin by adding this line to the bottom of your app's `Config/bootstrap.php`:

   ```php
   CakePlugin::load('CodeSniffer'); // or just CakePlugin::loadAll();
   ```

3. That's all! CodeSniffer is ready for use.

   ```bash
   cake CodeSniffer.[ShellName] run [some/optional/path]
   ```

### PHPMD Mess Detector
The PHPMD lib is included as the following command:
```bash
	cake CodeSniffer.Md run [some/optional/path]
 ```
If you do not provide a path, it will automatically run the sniffer for your APP (root) path, (usually `/src` + `/tests`).

By default it
- only looks at PHP files.
- only uses the unused code sniff (as the others are sometimes somewhat controversal).
- ignores `plugins`, `vendor`, `webroot` folders etc (in app or plugin - with no custom path argument set)
- creates a log file in TMP for larger error reports where the console screen can't hold that much information)

Tips:
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

For details and more tips see [github.com/phpmd/phpmd](https://github.com/phpmd/phpmd).

### PHPCS
... coming up

### Settings/Options

By default it uses the pre-defined settings.
You can overwrite the default at runtime or globally using your APP configs:

	// Use our own ruleset "codesize" as default
	Configure::write('CodeSniffer.ruleset', 'codesize');

	// A "custom" ruleset that is somewhere else on your file system
	Configure::write('CodeSniffer.ruleset', '/absolute/path/to/custom.xml');

Same applies for all other config keys.

## TODOS
There is also some more work to be done on the SmellDetector and other tools.
