<?php

// expects an associative array
// for CLI, the values of $args will be populated with values of $argc excluding the first
// else, $args is replaced by filtered GET/POST input,
// treating the initial values of $args as the $options argument to filter_input_array
// (see php manual)
//
// $offset is an offset to the CLI args array, only relevant in CLI
//
// returns false if could not get all the args requested, otherwise true
function get_args(array &$args, int $offset = 0) {
    global $argc, $argv;

    $CLI = (stripos(PHP_SAPI, 'cli') === 0);

    if ($CLI) {
        if ($argc <= $offset + count($args)) return false;
        $i = $offset + 1;
        foreach ($args as &$arg) {
            $arg = $argv[$i];
            $i++;
        }
        return true;
    } else {
        // Tries GET, then POST if GET returned falsey
        $args = filter_input_array(INPUT_GET, $args) ?: filter_input_array(INPUT_POST, $args);
        return ($args !== false);
    }
}

?>
