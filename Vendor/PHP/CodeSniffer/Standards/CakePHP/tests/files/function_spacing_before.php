<?php

$apples = 'oranges';

function beakman() {
	echo 'is awesome!';
}

function derEuroMark() {
	echo 'is more awesome!';
}

function derEuroBeakman() {
	echo 'is the best!';
}

class Foo {

	public $var = 'var';

	public function someMethod() {
		echo 'I am public';
	}

	function anotherOne() {
		echo 'I am nothing';
	}

	protected function _aThird() {
		echo 'I am protected';
	}

	/**
	 * @return void
	 */
	public function luckyMe() {
		echo 'Doc Blocks are OK';
	}

	public function one() {
		echo 'One';
	}

	public $other = 'other';
	/**
	 * @return void
	 */

	public function two() {
		echo 'Two';
	}

}