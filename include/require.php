<?php

$CONFIG_DIR = $INCLUDE_DIR. 'config'. DIRECTORY_SEPARATOR;
$FUNCTION_DIR = $INCLUDE_DIR .'function'. DIRECTORY_SEPARATOR;
$CLASS_DIR = $INCLUDE_DIR . 'class'. DIRECTORY_SEPARATOR;
$TEMPLATE_DIR = $DIR. 'template'. DIRECTORY_SEPARATOR;
$NEXUS_DIR = $TEMPLATE_DIR. 'nexus'. DIRECTORY_SEPARATOR;

// Scripts required by phylodiv.php, here for brevity

require_once $CONFIG_DIR. 'setup.php';
require_once $CONFIG_DIR. 'constants.php';
require_once $FUNCTION_DIR. 'say.php';
require_once $FUNCTION_DIR. 'get_args.php';
require_once $FUNCTION_DIR. 'init.php';

require_once $FUNCTION_DIR. 'get_sequences.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $FUNCTION_DIR. 'subsample_and_align.php';
require_once $FUNCTION_DIR. 'both.php';
require_once $FUNCTION_DIR. 'total_sequence_count.php';
require_once $FUNCTION_DIR. 'get_tree_lengths.php';

?>
