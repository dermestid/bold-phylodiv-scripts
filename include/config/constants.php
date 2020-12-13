<?php

require_once $CLASS_DIR. 'verbosity.php';

// Constants

$PAUP_COMMANDS_SETUP = $NEXUS_DIR. 'paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = $NEXUS_DIR. 'paup_commands_tree.txt';
$PAUP_COMMANDS_END = $NEXUS_DIR. 'paup_commands_end.txt';

$MINIMUM_SAMPLE_COUNT = 3; // need at least 3 taxa to build trees
$MARKER = 'COI-5P';

$REPLICATES = 5;
$VERBOSITY = Verbosity::SOME;

$DELETE_TEMP_FILES = true;
$PRINT_OUTPUT = false;
$OUTPUT_RESULTS = true;
$OUTPUT_FILE = 'bold_phylodiv_results.csv';
$OUTPUT_FILE_DELIMITER = ',';

$SETS_DATA_DELIMITER = ',';
$TAXSET_DELIMITER = ' ';
$SEQUENCE_DATA_DELIMITER = ',';

$TEMP_DIR = getcwd() .DIRECTORY_SEPARATOR. 'temp' .DIRECTORY_SEPARATOR; 
if (!is_dir($TEMP_DIR)) { mkdir($TEMP_DIR); }
$LOG_DIR = getcwd() .DIRECTORY_SEPARATOR. 'logs' .DIRECTORY_SEPARATOR;
if (!is_dir($LOG_DIR)) { mkdir($LOG_DIR); }
$SEQUENCES_DIR = getcwd() .DIRECTORY_SEPARATOR. 'sequences' .DIRECTORY_SEPARATOR;
if (!is_dir($SEQUENCES_DIR)) { mkdir($SEQUENCES_DIR); }


?>