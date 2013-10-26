<?php
// @sniffs Squiz.Commenting.DocCommentAlignment

	/**
	 * one level too much
	 */
class DocCommentAlignmentSniffTestClass {

	/**
	 *@return string
	 */
	public static function testReturn() {
	}

/**
 * one level too less
 *
 *  @return something
 */
	public static function testX() {
	}

	/**
	* one space too less
	*
	* @return something
	*/
	public static function testSpace() {
	}

		/**
		 * one level too much
		 *
		 * @return void
		 */
	public static function testY() {
	}

	/**
	 * careful with suddently missing asterix
	   bla
	 * @return void
	 */
	public static function testZ() {
	}

/**
 * careful with suddently missing asterix
   bla
 * @return void
 */
	public static function testMore() {
	}

}
