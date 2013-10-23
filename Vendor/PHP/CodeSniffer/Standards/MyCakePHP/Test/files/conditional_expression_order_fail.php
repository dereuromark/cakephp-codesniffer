<?php
// @expectedErrors 0
// @expectedCorrections 0
// @sniffs MyCakePHP.ControlStructures.ConditionalExpressionOrder

if (false == $foo){
}elseif (null ===$bar) {
}

if(true === ($foo = $this->thing())) {
} elseif(array() == $this->thing()){
} elseif (1!== $x){
}

if (2 > $foo) { // Careful, < and > switch here
} elseif (-5 < $a) { // Careful, negative char needs to be switched, too
}

if (-(3 + 3) < $foo) { // Don't touch that
}