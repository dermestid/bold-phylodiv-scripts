<?php

require_once $CLASS_DIR. 'verbosity.php';

$output_blocked = 0;
$at_eol = false;

function say_line($message) {
    global $output_blocked, $at_eol;

    if ($output_blocked === 0) {
        if ($at_eol) {
            echo(PHP_EOL);
            $at_eol = false;
        }
        if ($message != '') { echo($message.PHP_EOL); }
    }
}

function say($message) {
    global $VERBOSITY;

	if ($VERBOSITY > VERBOSITY::NONE) {
        say_line($message);
    }
}

function say_lastline($message) {
    global $VERBOSITY, $output_blocked, $at_eol;

    if ($VERBOSITY > VERBOSITY::NONE) {
        echo("\r".$message);
        $at_eol = true;
    }
}

function say_verbose($message) {
    global $VERBOSITY;

    if ($VERBOSITY === VERBOSITY::FULL) {
        say_line($message);
    }
}

?>
