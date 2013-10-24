# My CakePHP Code Sniffer

This code works with [phpcs](http://pear.php.net/manual/en/package.php.php-codesniffer.php)
and checks code against a modified version of the coding standards used in CakePHP.

NEW: Now it also has auto-fixing for almost all sniffs using the `phpcs-fixer` branch.

## Installation

### Usage

After installation you can check code compliance to the standard using
`phpcs`:

	phpcs --standard=MyCakePHP /path/to/code

### Changes to the CakePHP one

* Detect Yoda conditions.
* IsNull sniff
* IsInt/IsWritable sniff
* TypeCasting sniff
* FunctionName sniff
* ControlStructureSpacing
* LanguageConstructSpacing
* DocBlockEnding sniff
* LanguageConstructSpacing sniff
* Ternary (incl. short ternary) sniff
* @return doc block sniff
* Indentation correct (same level as methods and attributes for classes)
* LF on Windows are allowed to be \r\n

### TODO

* No private methods or attributes (error instead of warning)
* More whitespace sniffs
* Sniff for deprecations
