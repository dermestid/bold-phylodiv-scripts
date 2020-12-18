<?php

require_once $CONFIG_DIR. 'setup.php'; // $CLI
require_once $FUNCTION_DIR. 'get_sequence_file.php';
require_once $CLASS_DIR. 'sequence_data.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $FUNCTION_DIR. 'get_bold_record_count.php';
require_once $CLASS_DIR. 'status.php';
require_once $FUNCTION_DIR. 'download_sequences.php';
require_once $CLASS_DIR. 'download.php';

// Look for downloaded sequences of $marker for $taxon. If no local sequence set file is found,
// download the sequences from BOLD, and store subsets of them by location.
// Return true if a local file of sequences was found; false if download was attempted.
// Expects to be called only from $MAIN_SCRIPT so that $ARGS is set correctly.
function get_sequences($taxon, $marker) {
    global $CLI, $DOWNLOAD_PROGRESS_SCRIPT;
    global $ARGS; 

    // Check for local files

    $sequence_file = get_sequence_file($taxon);
    $data_file = Sequence_Data::get_file($taxon);
    $sets_file = Sequence_Sets::get_file($taxon);
    if (
        file_exists($data_file)
        && file_exists($sequence_file)
        && file_exists($sets_file)
    ) {
        return true;
    } else if (
        file_exists(Sequence_Data::get_file($taxon))
        || file_exists($sequence_file)
    ) {
        // Possibly another download in progress- don't touch them
        if ($CLI)
            $next_args = $ARGS;
        else
            $next_args = $_GET;
        Status::download_busy($next_args);
        exit;
    }

    // Check for invalid taxon
    if (get_bold_record_count($taxon) === 0) {
        Status::no_sequences($taxon);
        exit;
    }
    
    // Delete outdated files
    if (file_exists($sets_file))
        unlink($sets_file);

    // Download sequences (and wait) now, or if on web, start in a new process and inform the client
    if ($CLI)
        download_sequences($taxon, $marker);
    else {
        $pid = Download::new($ARGS);
        $next_args = $_GET;
        $next_args['pid'] = $pid;
        Status::downloading($next_args);
        exit;
    }
        

    return false;

}

?>
