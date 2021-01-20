<?php

// expects an associative array
// for CLI, the values of $args will be populated with values of $argc excluding the first
// else, $args is replaced by filtered GET input,
// treating the initial values of $args as the $options argument to filter_input_array
// (see php manual)
//
// returns false if could not get all the args requested, otherwise true
function get_args(array &$args) {
    global $argc, $argv;

    $CLI = (stripos(PHP_SAPI, 'cli') === 0);

    if ($CLI) {
        $cli_arg = 1;
        foreach ($args as &$value) {
            if ($cli_arg >= $argc) return false;
            $value = $argv[$cli_arg];
            $cli_arg++;
        }
    } else {
        $args = filter_input_array(INPUT_GET, $args);
    }
    return ($args !== false);
}

?>
