<?php

if (class_exists('Squiz_Sniffs_WhiteSpace_ControlStructureSpacingSniff', true) === false) {
	$error = 'Class Squiz_Sniffs_WhiteSpace_ControlStructureSpacingSniff not found';
	throw new PHP_CodeSniffer_Exception($error);
}

/**
 * MyCakePHP_Sniffs_WhiteSpace_ControlStructureSpacingSniff.
 *
 * Checks that control structures have the correct spacing around brackets.
 *
 * @author Mark Scherer
 * @license MIT
 */
class MyCakePHP_Sniffs_WhiteSpace_ControlStructureSpacingSniff extends Squiz_Sniffs_WhiteSpace_ControlStructureSpacingSniff {

	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
		'PHP'
	);

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token
	 *                                        in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();

		if (isset($tokens[$stackPtr]['scope_closer']) === false) {
			return;
		}

		$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if (!$next) {
			return;
		}

		// Take care of beginning
		if ($next - $stackPtr === 1) {
			$error = 'Expected 1 space after "%s"; 0 found';
			$data = array($tokens[$stackPtr]['content']);
			$fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpacingBeforeOpenBrace', $data);
			if ($fix === true && $phpcsFile->fixer->enabled === true) {
				$phpcsFile->fixer->addContent($stackPtr, ' ');
			}
		}

		// Take care of spacing between () and {
		//die(returns($tokens[$stackPtr]));

		if (isset($tokens[$stackPtr]['parenthesis_opener']) === true) {
			$parenOpener = $tokens[$stackPtr]['parenthesis_opener'];
			$parenCloser = $tokens[$stackPtr]['parenthesis_closer'];
			if ($tokens[($parenCloser + 1)]['code'] !== T_WHITESPACE) {
				$error = 'Expected 1 space after closing bracket; 0 found';
				$fix = $phpcsFile->addFixableError($error, $parenCloser, 'SpacingAfterClosingBrace');
				if ($fix === true && $phpcsFile->fixer->enabled === true) {
					$phpcsFile->fixer->addContent($parenCloser, ' ');
				}
			}
			/*
			if ($tokens[$parenOpener]['line'] === $tokens[$parenCloser]['line'] && $tokens[($parenCloser - 1)]['code'] === T_WHITESPACE) {
			$gap = strlen($tokens[($parenCloser - 1)]['content']);
			$error = 'Expected 0 spaces before closing bracket; %s found';
			$data = array($gap);
			$fix = $phpcsFile->addFixableError($error, ($parenCloser - 1), 'SpaceBeforeCloseBrace', $data);
			if ($fix === true && $phpcsFile->fixer->enabled === true) {
			//$phpcsFile->fixer->replaceToken(($parenCloser - 1), '   ');
			}
			}
			*/
		}

		$scopeOpener = $tokens[$stackPtr]['scope_opener'];
		$scopeCloser = $tokens[$stackPtr]['scope_closer'];

		$trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($scopeCloser + 1), null, true);

		if (in_array($tokens[$trailingContent]['code'], array(
			T_ELSE,
			T_ELSEIF,
			T_WHILE,
			T_DO))) {
			if ($trailingContent - $scopeCloser === 1) {
				$error = 'Expected 1 space between closing curly bracket and ELSE/ELSEIF; 0 found';
				$fix = $phpcsFile->addFixableError($error, $scopeCloser, 'SpacingAfterClosingCurlyBrace');
				if ($fix === true && $phpcsFile->fixer->enabled === true) {
					$phpcsFile->fixer->addContent($scopeCloser, ' ');
				}
			}
		}

		if ($tokens[$trailingContent]['code'] === T_ELSE && $tokens[$stackPtr]['code'] === T_IF) {
			// IF with ELSE.
			return;
		}

		if ($tokens[$trailingContent]['code'] === T_WHILE && $tokens[$stackPtr]['code'] === T_DO) {
			// DO with WHILE.
			return;
		}

		if ($tokens[$trailingContent]['code'] === T_COMMENT) {
			$trailingContent = $phpcsFile->findNext(T_WHITESPACE, ($trailingContent + 1), null, true);
		}

		// If this token is closing a CASE or DEFAULT, we don't need the
		// blank line after this control structure.
		if (isset($tokens[$trailingContent]['scope_condition']) === true) {
			$condition = $tokens[$trailingContent]['scope_condition'];
			if ($tokens[$condition]['code'] === T_CASE || $tokens[$condition]['code'] === T_DEFAULT) {
				return;
			}
		}

		if ($tokens[$trailingContent]['code'] === T_CLOSE_TAG) {
			// At the end of the script or embedded code.
			return;
		}

		if ($tokens[$trailingContent]['code'] === T_CLOSE_CURLY_BRACKET) {
			// Another control structure's closing brace.
			if (isset($tokens[$trailingContent]['scope_condition']) === true) {
				$owner = $tokens[$trailingContent]['scope_condition'];
				if ($tokens[$owner]['code'] === T_FUNCTION) {
					// The next content is the closing brace of a function
					// so normal function rules apply and we can ignore it.
					return;
				}
			}

			//TODO?

		} elseif ($tokens[$trailingContent]['line'] === ($tokens[$scopeCloser]['line'])) {
			if (!in_array($tokens[$trailingContent]['code'], array(
				T_ELSE,
				T_ELSEIF,
				T_WHILE,
				T_DO))) {
				$error = 'No blank line found after curly brace control structure';
				$fix = $phpcsFile->addFixableError($error, $scopeCloser, 'NoLineAfterClose');
				if ($fix === true && $phpcsFile->fixer->enabled === true) {
					$whitespaceBefore = $phpcsFile->findPrevious(T_WHITESPACE, ($scopeCloser - 1));
					$indentation = 0;
					if ($tokens[$whitespaceBefore]['line'] === $tokens[$scopeCloser]['line']) {
						$i = $whitespaceBefore;
						while ($tokens[$scopeCloser]['line'] === $tokens[$i]['line']) {
							$indentation++;
							$i--;
						}
						$indentation++;
					}
					$phpcsFile->fixer->addNewline($scopeCloser);
					if ($indentation) {
						$content = str_repeat("\t", $indentation) . $tokens[$trailingContent]['content'];
						$phpcsFile->fixer->replaceToken($trailingContent, $content);
					}
				}
			}
		}
	}

}
