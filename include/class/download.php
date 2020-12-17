<?php

require_once $CONFIG_DIR. 'constants.php'; // $DOWNLOAD_SCRIPT
require_once $CLASS_DIR. 'process.php';
require_once $CLASS_DIR. 'status.php';

class Download {

    public static function new($main_args) {
        global $DOWNLOAD_SCRIPT;

        // Start the download script in a new process

        $args = implode(' ', $main_args);
        $command = "php {$DOWNLOAD_SCRIPT} {$args}";
        $proc = new Process($command);
        $pid = $proc->pid();

        return $pid;
    }

    public static function status($pid) {
        return Process::get_status($pid);
    }
}

?>
