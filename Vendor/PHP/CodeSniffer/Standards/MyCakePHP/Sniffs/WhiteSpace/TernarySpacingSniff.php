<?php

/**
 * Note that CodeSniffer has a bug regarding this token,
 *
 * In `$x = $f?false:null;` (without a space after ?)
 *
 *   $f?false:null;
 *
 * would become
 *
 *   ... T_VARIABLE T_INLINE_THEN T_GOTO_LABEL T_NULL T_SEMICOLON
 *
 */
class MyCakePHP_Sniffs_WhiteSpace_TernarySpacingSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_INLINE_THEN);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile All the tokens found in the document.
	 * @param integer $stackPtr The position of the current token
	 *    in the stack passed in $tokens.
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		// Make sure there is one space before.
		$previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

		if ($stackPtr - $previousToken === 1) {
			$error = 'Expected 1 space before ternary IF, none found';
			$phpcsFile->addFixableError($error, $stackPtr, 'NotFound');
			if ($phpcsFile->fixer->enabled === true) {
				$phpcsFile->fixer->addContent($previousToken, ' ');
			}
		}

		// Make sure there is one space after - but not for short ternary.
		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		$shortTernary = false;
		if ($tokens[$nextToken]['code'] === T_INLINE_ELSE) {
			$shortTernary = true;

			if ($nextToken - $stackPtr > 1) {
				$error = 'Expected no space between short ternary IF/ELSE, 1 found';
				$phpcsFile->addFixableError($error, $stackPtr, 'TooMany');
				if ($phpcsFile->fixer->enabled === true) {
					$phpcsFile->fixer->replaceToken($stackPtr + 1, '');
				}
			}
		}

		if (!$shortTernary && $nextToken - $stackPtr === 1) {
			$error = 'Expected 1 space after ternary IF, none found';
			$phpcsFile->addFixableError($error, $stackPtr, 'NotFound');
			if ($phpcsFile->fixer->enabled === true) {
				$phpcsFile->fixer->addContent($stackPtr, ' ');
			}
		}

		// Make sure the : has the correct spacing.
		$inlineElse = $phpcsFile->findNext(T_INLINE_ELSE, ($stackPtr + 1), null, false);

		if (!$shortTernary) {
			$previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($inlineElse - 1), null, true);
			if ($inlineElse - $previousToken === 1) {
				$error = 'Expected 1 space before ternary ELSE, none found';
				$phpcsFile->addFixableError($error, $inlineElse, 'NotFound');
				if ($phpcsFile->fixer->enabled === true) {
					$phpcsFile->fixer->addContent($previousToken, ' ');
				}
			}
		}

		$nextToken = $phpcsFile->findNext(T_WHITESPACE, $inlineElse + 1, null, true);
		if ($nextToken - $inlineElse === 1) {
			$error = 'Expected 1 space after ternary ELSE, none found';
			$phpcsFile->addFixableError($error, $inlineElse, 'NotFound');
			if ($phpcsFile->fixer->enabled === true) {
				$phpcsFile->fixer->addContent($inlineElse, ' ');
			}
		}
	}

}
