<?php
// @sniffs MyCakePHP.PHP.IsNull

if ($x === null) {
}

if (null === $x) {
}

if (($x === null) === ($y === null)) {
}
