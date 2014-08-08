<?php
/**
 * PHP Version 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 * @since         CakePHP CodeSniffer 0.1.14
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Ensures that early returns are not followed by else.
 *
 */
class MyCakePHP_Sniffs_ControlStructures_ReturnEarlySniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_RETURN);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * Ensures that early returns are not followed by else.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param integer              $stackPtr  The position of the current token in the
 *                                        stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		$nextToken = $phpcsFile->findNext(T_CLOSE_CURLY_BRACKET, ($stackPtr + 1));
		if (!$nextToken) {
			return;
		}
		$tokenInfo = $tokens[$nextToken];
		if ($tokenInfo['bracket_opener'] >= $stackPtr) {
			return;
		}

		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
		if (!$nextToken) {
			return;
		}
		if ($tokens[$nextToken]['code'] === T_ELSEIF) {
			$warning = 'The ELSEIF after an early return is not always necessary and can probably be simplified.';
			$phpcsFile->addWarning($warning, $stackPtr, 'Warning');
		}

		if ($tokens[$nextToken]['code'] !== T_ELSE) {
			return;
		}

		$warning = 'The ELSE after an early return is not necessary and should be simplified.';
		$phpcsFile->addWarning($warning, $stackPtr, 'Warning');
	}

}
