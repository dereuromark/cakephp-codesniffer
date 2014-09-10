<?php
/**
 * PHP Version 5
 *
 * MyCakePHP_Sniffs_PHP_CastSniff
 *
 * @category  PHP
 * @author    Mark Scherer <dereuromark@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 */

/**
 * Ensures that casting is used over methods.
 *
 * intval($val) => (int)$val
 * floatval($val) => (float)$val
 */
class MyCakePHP_Sniffs_PHP_CastSniff implements PHP_CodeSniffer_Sniff {

/**
 * Returns an array of tokens this test wants to listen for.
 *
 * @return array
 */
	public function register() {
		return array(T_STRING);
	}

/**
 * Processes this test, when one of its tokens is encountered.
 *
 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
 * @param integer              $stackPtr  The position of the current token in the
 *                                        stack passed in $tokens.
 * @return void
 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		$content = strtolower($tokens[$stackPtr]['content']);
		if ($content !== 'intval' && $content !== 'floatval') {
			return;
		}
		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}

		$lastToken = $tokens[$nextToken]['parenthesis_closer'];

		// We want to skip intval($number, $argument) as the base is relevant here
		$argumentToken = $phpcsFile->findPrevious(T_WHITESPACE, ($lastToken - 1), null, true);
		$commaToken = $phpcsFile->findPrevious(T_WHITESPACE, ($argumentToken - 1), null, true);
		if ($tokens[$commaToken]['code'] === T_COMMA) {
			return;
		}

		// For now we skip on non trivial cases
		$continue = $lastToken <= ($nextToken + 2);
		if (!$continue) {
			return;
		}

		$error = 'Usage of ' . $tokens[$stackPtr]['content'] . ' not allowed; use casting instead';
		$phpcsFile->addFixableError($error, $stackPtr, 'NotAllowed');

		// Try to determine if we can remove the parentheses
		$removeParentheses = $lastToken <= ($nextToken + 2);

		// Fix the error
		if ($phpcsFile->fixer->enabled === true) {
			if ($content === 'floatval') {
				$phpcsFile->fixer->beginChangeset();
				$phpcsFile->fixer->replaceToken($stackPtr, '(float)');
				// Can be removed for trivial arguments
				if ($removeParentheses) {
					$phpcsFile->fixer->replaceToken($nextToken, '');
					$phpcsFile->fixer->replaceToken($lastToken, '');
				}
				$phpcsFile->fixer->endChangeset();
			}
			if ($content === 'intval') {
				$phpcsFile->fixer->beginChangeset();
				$phpcsFile->fixer->replaceToken($stackPtr, '(int)');
				// Can be removed for trivial arguments
				if ($removeParentheses) {
					$phpcsFile->fixer->replaceToken($nextToken, '');
					$phpcsFile->fixer->replaceToken($lastToken, '');
				}
				$phpcsFile->fixer->endChangeset();
			}
		}
	}

}
