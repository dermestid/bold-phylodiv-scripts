<?php

require_once $CONFIG_DIR. 'setup.php'; // $CLI
require_once $CONFIG_DIR. 'constants.php'; // $DOWNLOAD_PROGRESS_SCRIPT, $MAIN_SCRIPT

// groups functions for outputting status message or data,
// which will either be a simple message or a JSON object depending on whether this is CLI or web.
class Status {

    const FAIL = 'FAIL';
    const WORKING = 'WORKING';
    const DONE = 'DONE';

    public string $message;
    public $status; // one of the above consts, which the client must interpret
    public string $next; // the script which the client is prompted to call/request next
    public array $next_args; // the args which the client is prompted to provide to $next, associative
    public array $result;

    private function __construct(
        string $message = '',
        $status = self::FAIL,
        string $next = '',
        array $next_args = array(),
        array $result = array()
    ) {
        $this->message = $message;
        $this->status = $status;
        $this->next = $next;
        $this->next_args = $next_args;
        $this->result = $result;
    }

    public static function no_sequences(string $taxon) {
        global $CLI;

        $message = "no sequences for {$taxon} found locally or on BOLD";

        if ($CLI) 
            echo $message.PHP_EOL;
        else
            echo json_encode(new Status($message));
    }

    public static function arg_missing(string $arg) {
        global $CLI;

        $message = "{$arg} missing";

        if ($CLI)
            echo $message.PHP_EOL;
        else
            echo json_encode(new Status($message));
    }

    public static function no_args($script) {
        global $CLI;

        $message = "no args given to script {$script}";

        if ($CLI)
            echo $message.PHP_EOL;
        else
            echo json_encode(new Status($message));
    }

    public static function downloading($args) {
        global $CLI, $DOWNLOAD_PROGRESS_SCRIPT;

        $message = "download in progress";
        
        if ($CLI)
            echo $message.PHP_EOL;
        else
            echo json_encode(
                new Status(
                    $message,
                    self::WORKING, 
                    $DOWNLOAD_PROGRESS_SCRIPT,
                    $args
                ));
    }

    public static function download_busy($args) {
        global $CLI, $MAIN_SCRIPT;

        $message = "busy downloading, try again later";

        if ($CLI)
            echo $message.PHP_EOL;
        else
            echo json_encode(
                new Status(
                    $message,
                    self::WORKING,
                    $MAIN_SCRIPT,
                    $args
                ));
    }

    public static function download_done($args) {
        global $CLI, $MAIN_SCRIPT;

        $message = "download done";

        if ($CLI)
            echo $message.PHP_EOL;
        else
            echo json_encode(
                new Status(
                    $message,
                    self::DONE,
                    $MAIN_SCRIPT,
                    $args
                ));
    }

    public static function done(array $result = array()) {
        global $CLI, $SAVE_RESULTS_CSV, $OUTPUT_FILE;

        $message = "tree length calculation complete";

        if ($CLI)
            if ($SAVE_RESULTS_CSV)
                echo "Output result tree lengths to {$OUTPUT_FILE}.".PHP_EOL;
            else
                echo $message.PHP_EOL;
        else {
            echo json_encode(
                new Status(
                    $message,
                    self::DONE,
                    '',
                    array(),
                    $result
                ));
        }
    }
}

?>
