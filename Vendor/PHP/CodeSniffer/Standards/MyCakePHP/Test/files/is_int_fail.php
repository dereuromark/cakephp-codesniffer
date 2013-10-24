<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.PHP.IsInt

class Foo {

	public function aMethod() {
		$a = is_integer($y);
		if (($x = is_INT($y)) !== $some) {
		}
	}

}

