<?php

// Script to initiate downloads
// Intended to be run as a separate process, with same args as main process

require_once __DIR__.'/../include/require_script.php';

require_once $FUNCTION_DIR. 'download_sequences.php';

get_args();

download_sequences($TAXON, $MARKER);

?>
