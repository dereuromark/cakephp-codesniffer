<?php

class VisibilityFail {

	var $passing;

	public $passingPublic = 'defined';

	protected $_underScoredStart = 'OK';

	private $__foo = 'double_underscored OK - but should not be used';

	var $_shouldBeProtected = 'protected - NOT OK';

	var $__shouldBePrivate = 'private - NOT OK and should not be used';

}