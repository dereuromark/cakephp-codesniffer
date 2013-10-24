<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.PHP.IsWritable

class Foo {

	public function aMethod() {
		$a = is_writeable($foo);
		if (($x = (is_writeable($foo)) !== $some)) {
		}
		$a = is_wrItaBle($foo);
	}

}

