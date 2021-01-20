<?php

// echo JSON { "result":true } if PAUP and CLUSTAL executables are found
// otherwise echo {"result":false }

require_once '../include/class/executable.php';

if (    Executable::exists(Executable::CLUSTAL) 
    &&  Executable::exists(Executable::PAUP)
) {
    echo json_encode(["result" => true]);
} else {
    echo json_encode(["result" => false]);
}

?>
