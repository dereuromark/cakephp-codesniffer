<?php

/**
 * MyCakePHP_Sniffs_ControlStructures_InlineControlStructureSniff.
 *
 * Verifies that inline control statements are not present or properly replaced.
 *
 * @author    Mark Scherer
 * @link      MIT
 */
class MyCakePHP_Sniffs_ControlStructures_InlineControlStructureSniff implements PHP_CodeSniffer_Sniff {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
		'PHP',
		'JS',
		);

	/**
	 * If true, an error will be thrown; otherwise a warning.
	 *
	 * @var bool
	 */
	public $error = true;


	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_IF,
			T_ELSE,
			T_FOREACH,
			T_WHILE,
			T_DO,
			T_SWITCH,
			T_FOR,
			);

	}


	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		if (isset($tokens[$stackPtr]['scope_opener']) === true) {
			$scopeOpener = $tokens[$tokens[$stackPtr]['scope_opener']];
			if (!isset($tokens[$stackPtr]['parenthesis_closer'])) {
				return;
			}

			$parenthesisCloser = $tokens[$tokens[$stackPtr]['parenthesis_closer']];
			if ($scopeOpener['line'] === $parenthesisCloser['line']) {
				return;
			}

			//FIXME!
			$fix = $phpcsFile->addFixableError('Opening curly brace must open on the same line as parentheses closer.', $tokens[$stackPtr]['scope_opener'], 'NotAllowed');
			if ($fix === true && $phpcsFile->fixer->enabled === true) {
				$next = $phpcsFile->findNext(T_WHITESPACE, ($tokens[$stackPtr]['parenthesis_closer'] + 1), ($tokens[$stackPtr]['scope_opener'] - 1), true);
				$phpcsFile->fixer->beginChangeset();
				$phpcsFile->fixer->addContent($tokens[$stackPtr]['parenthesis_closer'], ' w');
				for ($i = $tokens[$stackPtr]['parenthesis_closer'] + 1; $i < $next; $i++) {
					$phpcsFile->fixer->replaceToken($i, '');
				}
				$phpcsFile->fixer->endChangeset();
			}
			return;
		}

		// Ignore the ELSE in ELSE IF. We'll process the IF part later.
		if (($tokens[$stackPtr]['code'] === T_ELSE) && ($tokens[($stackPtr + 2)]['code'] === T_IF)) {
			return;
		}

		if ($tokens[$stackPtr]['code'] === T_WHILE) {
			// This could be from a DO WHILE, which doesn't have an opening brace.
			$lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
			if ($tokens[$lastContent]['code'] === T_CLOSE_CURLY_BRACKET) {
				$brace = $tokens[$lastContent];
				if (isset($brace['scope_condition']) === true) {
					$condition = $tokens[$brace['scope_condition']];
					if ($condition['code'] === T_DO) {
						return;
					}
				}
			}
		}

		// This is a control structure without an opening brace,
		// so it is an inline statement.
		if ($this->error === true) {
			$fix = $phpcsFile->addFixableError('Inline control structures are not allowed', $stackPtr, 'NotAllowed');
		} else {
			$fix = $phpcsFile->addFixableWarning('Inline control structures are discouraged', $stackPtr, 'Discouraged');
		}

		if ($fix === true && $phpcsFile->fixer->enabled === true) {
			$phpcsFile->fixer->beginChangeset();
			if (isset($tokens[$stackPtr]['parenthesis_closer']) === true) {
				$closer = $tokens[$stackPtr]['parenthesis_closer'];
			} else {
				$closer = $stackPtr;
			}

			$phpcsFile->fixer->addContent($closer, ' { ');

			$semicolon = $phpcsFile->findNext(T_SEMICOLON, ($closer + 1));
			$next = $phpcsFile->findNext(T_WHITESPACE, ($closer + 1), ($semicolon + 1), true);

			// Account for a comment on the end of the line.
			for ($endLine = $semicolon; $endLine < $phpcsFile->numTokens; $endLine++) {
				if (isset($tokens[($endLine + 1)]) === false || $tokens[$endLine]['line'] !== $tokens[($endLine + 1)]['line']) {
					break;
				}
			}

			if ($tokens[$endLine]['code'] !== T_COMMENT) {
				$endLine = $semicolon;
			}

			// Put the closing one a line further down
			$indentation = str_repeat("\t", $tokens[$stackPtr]['level']);

			if ($next !== $semicolon) {
				if ($endLine !== $semicolon) {
					//$n = $phpcsFile->fixer->getTokenContent($next);
					//$phpcsFile->fixer->replaceToken($next, "\n" . str_repeat("\t", $tokens[$next]['level'] - 1) . $n);
					$phpcsFile->fixer->addContent($endLine, '}');
				} else {
					$n = $phpcsFile->fixer->getTokenContent($next);
					if ($tokens[$next]['line'] === $tokens[$stackPtr]['line']) {
						$phpcsFile->fixer->replaceToken($next, "\n" . str_repeat("\t", $tokens[$next]['level'] + 1) . $n);
					}
					$indentation = "\n" . $indentation;
					$phpcsFile->fixer->addContent($semicolon, $indentation . '}');
				}
			} else {
				if ($endLine !== $semicolon) {
					$phpcsFile->fixer->replaceToken($semicolon, '');
					$phpcsFile->fixer->addNewlineBefore($endLine);
					$phpcsFile->fixer->addContent($endLine, '}');
				} else {
					$phpcsFile->fixer->replaceToken($semicolon, '}');
				}
			}

			$phpcsFile->fixer->endChangeset();
		}
	}

}
