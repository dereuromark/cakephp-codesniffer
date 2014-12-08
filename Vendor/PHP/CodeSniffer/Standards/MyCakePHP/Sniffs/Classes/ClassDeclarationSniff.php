<?php
if (class_exists('PSR2_Sniffs_Classes_ClassDeclarationSniff', true) === false) {
    $error = 'Class PSR2_Sniffs_Classes_ClassDeclarationSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * MyCakePHP_Sniffs_Classes_ClassDeclarationSniff.
 *
 * Checks the declaration of the class and its inheritance is correct.
 *
 * @author Mark Scherer
 * @license MIT
 */
class MyCakePHP_Sniffs_Classes_ClassDeclarationSniff extends PSR2_Sniffs_Classes_ClassDeclarationSniff {

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                         in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
        // We want all the errors from the PEAR standard, plus some of our own.
        //parent::process($phpcsFile, $stackPtr);
        $this->processPear($phpcsFile, $stackPtr);
        $this->processOpen($phpcsFile, $stackPtr);
        $this->processClose($phpcsFile, $stackPtr);

    }

		/**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param integer              $stackPtr  The position of the current token in the
     *                                        stack passed in $tokens.
     *
     * @return void
     */
    public function processPear(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens    = $phpcsFile->getTokens();
        $errorData = array($tokens[$stackPtr]['content']);

        if (isset($tokens[$stackPtr]['scope_opener']) === false) {
            $error = 'Possible parse error: %s missing opening or closing brace';
            $phpcsFile->addWarning($error, $stackPtr, 'MissingBrace', $errorData);
            return;
        }

        $curlyBrace  = $tokens[$stackPtr]['scope_opener'];
        $lastContent = $phpcsFile->findPrevious(T_WHITESPACE, ($curlyBrace - 1), $stackPtr, true);
        $classLine   = $tokens[$lastContent]['line'];
        $braceLine   = $tokens[$curlyBrace]['line'];
        if ($braceLine > ($classLine + 1)) {
            $error = 'Opening brace of a %s must be on the line following the %s declaration; found %s line(s)';
            $data  = array(
                      $tokens[$stackPtr]['content'],
                      $tokens[$stackPtr]['content'],
                      ($braceLine - $classLine - 1),
                     );
            $phpcsFile->addError($error, $curlyBrace, 'OpenBraceWrongLine', $data);
            return;
        } elseif ($braceLine !== $classLine) {
            $error = 'Opening brace of a %s must be on the same line after the definition';
            $fix   = $phpcsFile->addWarning($error, $curlyBrace, 'OpenBraceSameLine', $errorData);
            if (false && $fix === true && $phpcsFile->fixer->enabled === true) {
                $phpcsFile->fixer->beginChangeset();
                if ($tokens[($curlyBrace - 1)]['code'] === T_WHITESPACE) {
                    $phpcsFile->fixer->replaceToken(($curlyBrace - 1), '');
                }
                $phpcsFile->fixer->endChangeset();
            }

            return;
        }

				/*
        if ($tokens[($curlyBrace + 1)]['content'] !== $phpcsFile->eolChar) {
            $error = 'Opening %s brace must be on a line by itself';
            $fix   = $phpcsFile->addFixableError($error, $curlyBrace, 'OpenBraceNotAlone', $errorData);
            if ($fix === true && $phpcsFile->fixer->enabled === true) {
                $phpcsFile->fixer->addNewline($curlyBrace);
            }
        }
        */
    }

    /**
     * Processes the closing section of a class declaration.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    public function processClose(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Check that the closing brace comes right after the code body.
        $closeBrace  = $tokens[$stackPtr]['scope_closer'];
        $prevContent = $phpcsFile->findPrevious(T_WHITESPACE, ($closeBrace - 1), null, true);
        if ($prevContent !== $tokens[$stackPtr]['scope_opener']
            && $tokens[$prevContent]['line'] > ($tokens[$closeBrace]['line'] - 2)
        ) {
            $error = 'The closing brace for the %s must go on the second next line after the body (one newline in between)';
            $data  = array($tokens[$stackPtr]['content']);
            //$fix  = $phpcsFile->addFixableError($error, $closeBrace, 'CloseBraceAfterBody', $data);
						$fix  = $phpcsFile->addWarning($error, $closeBrace, 'CloseBraceAfterBody', $data);

            if (false && $fix === true && $phpcsFile->fixer->enabled === true) {
                $phpcsFile->fixer->beginChangeset();
                /*
                for ($i = ($prevContent + 1); $i < $closeBrace; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }

                if (strpos($tokens[$prevContent]['content'], $phpcsFile->eolChar) === false) {
                    $phpcsFile->fixer->replaceToken($closeBrace, $phpcsFile->eolChar.$tokens[$closeBrace]['content']);
                }
                */

                $phpcsFile->fixer->addNewline($closeBrace);

                $phpcsFile->fixer->endChangeset();
            }
        }

        // Check the closing brace is on it's own line, but allow
        // for comments like "//end class".
        $nextContent = $phpcsFile->findNext(T_COMMENT, ($closeBrace + 1), null, true);
        if ($tokens[$nextContent]['content'] !== $phpcsFile->eolChar
            && $tokens[$nextContent]['line'] === $tokens[$closeBrace]['line']
        ) {
            $type  = strtolower($tokens[$stackPtr]['content']);
            $error = 'Closing %s brace must be on a line by itself';
            $data  = array($tokens[$stackPtr]['content']);
            $phpcsFile->addError($error, $closeBrace, 'CloseBraceSameLine', $data);
        }

    }

}
