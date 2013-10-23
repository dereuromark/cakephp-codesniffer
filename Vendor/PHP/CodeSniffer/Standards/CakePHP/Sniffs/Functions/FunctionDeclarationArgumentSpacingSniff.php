<?php
if (class_exists('Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff', true) === false) {
	$error = 'Class Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff not found';
	throw new PHP_CodeSniffer_Exception($error);
}

/**
 * CakePHP_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff.
 *
 * Checks that arguments in function declarations are spaced correctly.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class CakePHP_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff extends
	Squiz_Sniffs_Functions_FunctionDeclarationArgumentSpacingSniff {

	/**
	 * How many spaces should surround the equals signs.
	 *
	 * @var int
	 */
	public $equalsSpacing = 1;

}
