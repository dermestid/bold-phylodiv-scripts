<?php

function stream_exit(string $message, bool $CLI) {
    if ($CLI) exit($message);
    else {
        echo "event: fail\n";
        echo "data: {$message}\n\n";
        ob_flush();
        flush();
        exit;
    }
}

?>
