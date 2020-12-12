<?php

// Constants

$PAUP_COMMANDS_SETUP = $FUNCTIONS_DIR. 'paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = $FUNCTIONS_DIR. 'paup_commands_tree.txt';
$PAUP_COMMANDS_END = $FUNCTIONS_DIR. 'paup_commands_end.txt';

$MINIMUM_SUBSAMPLE_NUMBER = 3; // need at least 3 taxa to build trees
$MARKER = 'COI-5P';

$REPLICATES = 5;

$DELETE_TEMP_FILES = true;
$PRINT_OUTPUT = false;
$OUTPUT_RESULTS = true;
$OUTPUT_FILE = 'bold_phylodiv_results.csv';
$OUTPUT_FILE_DELIMITER = ',';

$TAXSETS_DATA_DELIMITER = ',';
$TAXSET_DELIMITER = ' ';
$SEQUENCE_DATA_DELIMITER = ',';

$TEMP_DIR = getcwd() .DIRECTORY_SEPARATOR. 'temp' .DIRECTORY_SEPARATOR; 
if (!is_dir($TEMP_DIR)) { mkdir($TEMP_DIR); }
$LOG_DIR = getcwd() .DIRECTORY_SEPARATOR. 'logs' .DIRECTORY_SEPARATOR;
if (!is_dir($LOG_DIR)) { mkdir($LOG_DIR); }
$SEQUENCES_DIR = getcwd() .DIRECTORY_SEPARATOR. 'sequences' .DIRECTORY_SEPARATOR;
if (!is_dir($SEQUENCES_DIR)) { mkdir($SEQUENCES_DIR); }


?>