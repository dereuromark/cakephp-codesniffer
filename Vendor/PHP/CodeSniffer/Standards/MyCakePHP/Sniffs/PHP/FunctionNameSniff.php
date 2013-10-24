<?php

/**
 * MyCakePHP_Sniffs_PHP_TypeCastingSniff
 *
 * This sniff takes care of internal PHP functions and their naming (lowercase).
 * This can be auto-corrected.
 * It also warns about user land functions that do not follow camelBacked naming scheme.
 *
 * PHP version 5
 *
 * @category  PHP
 * @author    Mark Scherer <dereuromark@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 * @version   1.0
 */
class MyCakePHP_Sniffs_PHP_FunctionNameSniff implements PHP_CodeSniffer_Sniff {

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

		// Make sure we only sniff on real internal PHP functions
		$previousToken = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);
		if (in_array($tokens[$previousToken]['code'], array(T_NEW, T_DOUBLE_COLON, T_OBJECT_OPERATOR, T_FUNCTION))) {
			return;
		}

		$nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
		if ($tokens[$nextToken]['code'] !== T_OPEN_PARENTHESIS) {
			return;
		}

		// Now it can only be an internal or a user function
		$internalFunctions = $this->_getInternalFunctions();

		$content = $tokens[$stackPtr]['content'];
		$key = mb_strtolower($content);

		if (in_array($key, $internalFunctions)) {
			if ($key !== $content) {
				$error = 'Please use ' . $key . ' instead of ' . $content . '.';
				$phpcsFile->addFixableError($error, $stackPtr, 'NotAllowed');
				if ($phpcsFile->fixer->enabled === true) {
					$phpcsFile->fixer->replaceToken($stackPtr, $key);
				}
			}
			return;
		}

		// Check user function names - they should not contain _
		// Ignore underscore prefixes
		while (strpos($key, '_') === 0) {
			$key = substr($key, 1);
		}
		if (strrpos($key, '_') !== false) {
			$error = 'Please use camelBack style for function name ' . $content . '.';
			$phpcsFile->addWarning($error, $stackPtr, 'Warning');
		}
	}

	/**
	 * MyCakePHP_Sniffs_PHP_FunctionNameSniff::_getFunctions()
	 *
	 * @return array Internal PHP functions
	 */
	protected function _getInternalFunctions() {
		$functions = get_defined_functions();
		sort($functions['internal']);
		return $functions['internal'];
	}

}
