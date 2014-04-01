<?php

if ($foo == false){
}elseif ($bar === null) {
}

if(($foo = $this->thing()) === true) {
} elseif(array() == $this->thing()){
} elseif ($x !== 1){
}

if ($foo && false === mime_content_type($file->pwd())) { // This cannot be resolved right now
}

if ($foo < 2) { // Careful, < and > switch here
} elseif ($a > -5) { // Careful, negative char needs to be switched, too
}

if (-(3 + 3) < $foo) { // Don't touch that
}