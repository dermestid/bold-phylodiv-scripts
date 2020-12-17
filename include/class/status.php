<?php

require_once $CONFIG_DIR. 'setup.php'; // $CLI
require_once $CONFIG_DIR. 'constants.php'; // $DOWNLOAD_PROGRESS_SCRIPT, $MAIN_SCRIPT

// groups functions for outputting status message or data,
// which will either be a simple message or a JSON object depending on whether this is CLI or web.
class Status {

    const FAIL = 'FAIL';
    const WORKING = 'WORKING';
    const DONE = 'DONE';

    public $status; // one of the above consts, which the client must interpret
    public string $next; // the script which the client is prompted to call/request next
    public array $next_args; // the args which the client is prompted to provide to $next
    public array $result;

    private function __construct(
        $status = self::FAIL,
        string $next = '',
        array $next_args = array(),
        array $result = array()
    ) {
        $this->status = $status;
        $this->next = $next;
        $this->next_args = $next_args;
        $this->result = $result;
    }

    public static function no_sequences(string $taxon) {
        global $CLI;

        if ($CLI) 
            echo "No sequences for {$taxon} found locally or on BOLD.".PHP_EOL;
        else
            echo json_encode(new Status());
    }

    public static function no_args($script) {
        global $CLI;

        if ($CLI)
            echo "No args given to script {$script}".PHP_EOL;
        else
            echo json_encode(new Status(self::FAIL, $script));
    }

    public static function downloading($args) {
        global $CLI, $DOWNLOAD_PROGRESS_SCRIPT;

        if ($CLI)
            echo "Still downloading...".PHP_EOL;
        else
            echo json_encode(
                new Status(
                    self::WORKING, 
                    $DOWNLOAD_PROGRESS_SCRIPT,
                    $args
                ));
    }

    public static function download_busy($args) {
        global $CLI, $MAIN_SCRIPT;

        if ($CLI)
            echo "Busy downloading, try again later".PHP_EOL;
        else
            echo json_encode(
                new Status(
                    self::WORKING,
                    $MAIN_SCRIPT,
                    $args
                ));
    }

    public static function download_done($args) {
        global $CLI, $MAIN_SCRIPT;

        if ($CLI)
            echo "Download done, restart {$MAIN_SCRIPT} to continue".PHP_EOL;
        else
            echo json_encode(
                new Status(
                    self::DONE,
                    $MAIN_SCRIPT,
                    $args
                ));
    }

    public static function done(array $result = array()) {
        global $CLI, $SAVE_RESULTS_CSV, $OUTPUT_FILE;

        if ($CLI)
            if ($SAVE_RESULTS_CSV)
                echo "Output result tree lengths to {$OUTPUT_FILE}.";
            else
                echo "Tree length calculation complete.";
        else {
            echo json_encode(
                new Status(
                    self::DONE,
                    '',
                    array(),
                    $result
                ));
        }
    }
}

?>
