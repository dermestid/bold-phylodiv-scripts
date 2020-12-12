<?php

require_once $FUNCTIONS_DIR. 'default_args.php';

function get_args() {
    global $argc, $argv;
    global $ARGS;

    if(!isset($argc)) { exit("argc and argv disabled"); }
    if($argc + 1 > count($ARGS)) { exit("Wrong number of arguments given"); }
    if(!($i = $argc - 1)) { exit("No arguments given"); }

    while($i > 0) {
        $ARGS[$i - 1] = $argv[$i];
        // echo('set '. $args[$i] . ' to ' . $argv[$i] . PHP_EOL);
        $i--;
    }
}

?>
