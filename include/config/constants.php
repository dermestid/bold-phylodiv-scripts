<?php

require_once $CLASS_DIR. 'verbosity.php';

// Constants

$PAUP_COMMANDS_SETUP = $NEXUS_DIR. 'paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = $NEXUS_DIR. 'paup_commands_tree.txt';
$PAUP_COMMANDS_END = $NEXUS_DIR. 'paup_commands_end.txt';

$MINIMUM_SAMPLE_COUNT = 3; // need at least 3 taxa to build trees
$MARKER = 'COI-5P';

if ($CLI) {
    $VERBOSITY = VERBOSITY::SOME;
} else {
    $VERBOSITY = VERBOSITY::NONE;
}

$SAVE_TREES = false;
$DELETE_TEMP_FILES = true;
$KEEP_LOGS = false;
$OUTPUT_RESULTS = true;
$OUTPUT_FILE = $OUT_DIR. 'results.csv';
$OUTPUT_FILE_DELIMITER = ',';

$SETS_DATA_DELIMITER = ',';
$TAXSET_DELIMITER = ' ';
$SEQUENCE_DATA_DELIMITER = ',';

?>
