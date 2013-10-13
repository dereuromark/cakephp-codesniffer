<?php

/**
 * MyCakePHP_Sniffs_ControlStructures_ConditionalExpressionOrderSniff.
 *
 * Verifies that Yoda conditions (reversed expression order) are not used for comparison.
 *
 * @author    Mark Scherer
 * @license   MIT
 */
class MyCakePHP_Sniffs_ControlStructures_ConditionalExpressionOrderSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_IF, T_ELSEIF);
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

		// Open paranthesis should come next
		$nextToken = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($stackPtr + 1));
		if (!$nextToken) {
			return;
		}

		// Look for the first expression
		$leftToken = $phpcsFile->findNext(T_WHITESPACE, ($nextToken + 1), null, true);
		if (!$leftToken) {
			return;
		}

		if (!in_array($tokens[$leftToken]['code'], array(T_NULL, T_FALSE, T_TRUE, T_LNUMBER, T_CONSTANT_ENCAPSED_STRING))) {
			return;
		}

		// Get the comparison operator
		$comparisonToken = $phpcsFile->findNext(T_WHITESPACE, ($leftToken + 1), null, true);
		$tokensToCheck = array(T_IS_IDENTICAL, T_IS_NOT_IDENTICAL, T_IS_EQUAL, T_IS_NOT_EQUAL, T_GREATER_THAN, T_LESS_THAN,
			T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL);
		if (!in_array($tokens[$comparisonToken]['code'], $tokensToCheck)) {
			return;
		}

		// Look for the right expression
		$rightToken = $phpcsFile->findNext(T_WHITESPACE, ($comparisonToken + 1), null, true);
		if (!$rightToken) {
			return;
		}

		$error = 'Usage of Yoda conditions is not advised. Please switch the expression order.';
		$phpcsFile->addFixableError($error, $stackPtr, 'ExpressionOrder');

		// Fix the error
		if ($phpcsFile->fixer->enabled === true) {
			$tmp = $tokens[$leftToken]['content'];
			$phpcsFile->fixer->beginChangeset();
			$phpcsFile->fixer->replaceToken($leftToken, $tokens[$rightToken]['content']);
			$phpcsFile->fixer->replaceToken($rightToken, $tmp);
			$phpcsFile->fixer->endChangeset();
		}
	}

}
