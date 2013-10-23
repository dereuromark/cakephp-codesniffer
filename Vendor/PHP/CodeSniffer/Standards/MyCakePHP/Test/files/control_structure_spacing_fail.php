<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.WhiteSpace.ControlStructureSpacing

if ($foo){
	// ...
}elseif ($bar) {
	// ...
}else if($bar) {
	// ...
}else {
	// ...
}

if($foo) {
	// ...
} elseif($bar){
	// ...
} else{
	// ...
}

class Foo {

	public function aMethod() {
		if($foo) { $x = true;} elseif($bar){
			// ...
		}$someVariable = true;
	}

	public function anotherMethod() {
		if($foo)
		{
			$x = true;
		}
		elseif($bar)
		{
			// ...
		}
		else
		{
			// ...
		}
	}

}
