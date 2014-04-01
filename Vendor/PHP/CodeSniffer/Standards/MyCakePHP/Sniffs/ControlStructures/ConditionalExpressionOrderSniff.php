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

		// Only sniff for specified tokens
		if (!in_array($tokens[$leftToken]['code'], array(T_MINUS, T_NULL, T_FALSE, T_TRUE, T_LNUMBER, T_CONSTANT_ENCAPSED_STRING))) {
			return;
		}
		$leftTokenStart = $leftToken;

		if ($tokens[$leftToken]['code'] === T_MINUS) {
			if ($tokens[$leftToken + 1]['code'] !== T_LNUMBER) {
				$error = 'Usage of Yoda conditions is not advised. Please switch the expression order.';
				$phpcsFile->addError($error, $stackPtr, 'ExpressionOrder');
				return;
			}
			$leftToken = $leftToken + 1;
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
			$error = 'Usage of Yoda conditions is not advised. Please switch the expression order.';
			$phpcsFile->addError($error, $stackPtr, 'ExpressionOrder');
			return;
		}

		$rightTokenStart = $rightToken;

		// If its T_OPEN_PARENTHESIS we need to find the closing one
		if ($tokens[$rightToken]['code'] === T_OPEN_PARENTHESIS) {
			$rightToken = $tokens[$rightToken]['parenthesis_closer'];
		}

		// Check if we need to inverse comparison operator
		$comparisonTokenValue = $tokens[$comparisonToken]['content'];
		if (in_array($tokens[$comparisonToken]['code'], array(T_GREATER_THAN, T_LESS_THAN,
			T_IS_GREATER_OR_EQUAL, T_IS_SMALLER_OR_EQUAL))) {
			$mapping = array(
				T_GREATER_THAN => '<',
				T_LESS_THAN => '>',
				T_IS_GREATER_OR_EQUAL => '<=',
				T_IS_SMALLER_OR_EQUAL => '>='
			);
			$comparisonTokenValue = $mapping[$tokens[$comparisonToken]['code']];
		}

		$error = 'Usage of Yoda conditions is not advised. Please switch the expression order.';
		$phpcsFile->addFixableError($error, $stackPtr, 'ExpressionOrder');

		// Fix the error
		if ($phpcsFile->fixer->enabled === true) {
			$tmp = '';
			for ($i = $leftTokenStart; $i <= $leftToken; $i++) {
				$tmp .= $tokens[$i]['content'];
			}
			$phpcsFile->fixer->beginChangeset();
			for ($i = $leftTokenStart; $i < $rightTokenStart; $i++) {
				$phpcsFile->fixer->replaceToken($i, '');
			}
			$phpcsFile->fixer->addContent($rightToken, ' ' . $comparisonTokenValue . ' ' . $tmp);
			$phpcsFile->fixer->endChangeset();
		}
	}

}
