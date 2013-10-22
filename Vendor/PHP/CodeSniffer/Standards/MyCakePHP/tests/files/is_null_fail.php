<?php
// @expectedErrors 13
// @expectedCorrections 2
// @sniffs MyCakePHP.PHP.IsNull

$x = is_null($foo);

$x = is_null ($foo);

if (is_null($foo) && $something) {
}

if (is_null($foo) === is_null($bar)) {
}

if (is_null($foo) === true) {
}

if (is_null($foo) === false) {
}

$y = !is_null($foo);

if (!is_null($foo) || $something) {
}

if (!is_null($foo) === true) {
}

if (!is_null($foo) === false) {
}

// We skip autocorrection here, though
if (true === is_null($x)) {
}

// We skip autocorrection here, though
if (false === !is_null($x)) {
}