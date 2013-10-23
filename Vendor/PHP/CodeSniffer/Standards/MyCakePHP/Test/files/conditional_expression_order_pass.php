<?php
// @sniffs MyCakePHP.ControlStructures.ConditionalExpressionOrder

if ($foo == false) {
} elseif ($foo == 3) {
} else if ($bar === null) {
}

if (($some = $this->thing()) !== false) {
}