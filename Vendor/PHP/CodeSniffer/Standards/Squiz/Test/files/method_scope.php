<?php

class MethodScopeFail {

	public function aNormalOne() {
	}

	public static function aStaticOne() {
	}

	static public function aWronglyOrderedOne() {
	}

	function scopeMissing() {
	}

	protected function _meOk() {
	}

	function _meNotOk() {
	}

	function __supposedToBePrivate() {
	}

	function __construct() {
		// Do not touch me
	}

}