<?php

$x = $foo === null;

$x = $foo === null;

if ($foo === null && $something) {
}

if (is_null($foo) === is_null($bar)) {
}

if ($foo === null) {
}

if ($foo !== null) {
}

$y = $foo !== null;

if ($foo !== null || $something) {
}

if ($foo !== null) {
}

if ($foo === null) {
}

// We skip autocorrection here, though
if (true === is_null($x)) {
}

// We skip autocorrection here, though
if (false === !is_null($x)) {
}