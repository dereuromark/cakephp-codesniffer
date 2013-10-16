<?php
$x = is_null($foo);

$x = is_null ($foo);

if (is_null($foo)) {
	//bla
}

if (is_null($foo) === is_null($bar)) {
	//bla
}

if (is_null($foo) === true) {
	//bla
}

if (is_null($foo) === false) {
	//bla
}

$y = !is_null($foo);

if (!is_null($foo) || $something) {
	//bla
}