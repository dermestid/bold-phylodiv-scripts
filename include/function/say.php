<?php

require_once $CLASS_DIR. 'verbosity.php';

$output_blocked = 0;

function say_line($message) {
    global $output_blocked;

    if ($output_blocked === 0) {
        echo($message.PHP_EOL);
    }
}

function say($message) {
    global $VERBOSITY;

	if ($VERBOSITY > VERBOSITY::NONE) {
        say_line($message);
    }
}

function say_verbose($message) {
    global $VERBOSITY;

    if ($VERBOSITY === VERBOSITY::FULL) {
        say_line($message);
    }
}

?>
