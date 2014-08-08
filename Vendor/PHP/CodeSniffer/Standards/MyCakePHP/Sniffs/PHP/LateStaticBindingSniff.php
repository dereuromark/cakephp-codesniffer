<?php

/**
 * Makes sure that late static binding via static:: is used instead of self:: to allow
 * extendability.
 *
 * @author Mark Scherer
 * @license MIT
 */
class MyCakePHP_Sniffs_PHP_LateStaticBindingSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_FUNCTION);
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

		if (!isset($tokens[$stackPtr]['scope_opener']) || !isset($tokens[$stackPtr]['scope_closer'])) {
			return;
		}
		$opener = $tokens[$stackPtr]['scope_opener'];
		$closer = $tokens[$stackPtr]['scope_closer'];

		$position = $opener + 1;
		while ($position < $closer) {
			if ($tokens[$position]['code'] == T_SELF && $tokens[$position + 1]['code'] == T_DOUBLE_COLON) {
				$error = '"self::" should be replaced by "static::" to allow late static binding.';
				$phpcsFile->addFixableError($error, $position, 'NotAllowed');

				// Fix the error
				if ($phpcsFile->fixer->enabled === true) {
					$phpcsFile->fixer->beginChangeset();
					$phpcsFile->fixer->replaceToken($position, 'static');
					$phpcsFile->fixer->endChangeset();
				}
			}
			$position++;
		}
	}

}
