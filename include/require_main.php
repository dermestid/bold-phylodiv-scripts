<?php

require_once 'require.php';

// Scripts required by $MAIN_SCRIPT

require_once $CONFIG_DIR. 'setup.php';
require_once $CONFIG_DIR. 'constants.php';
require_once $FUNCTION_DIR. 'say.php';
require_once $FUNCTION_DIR. 'get_args.php';

require_once $FUNCTION_DIR. 'get_sequences.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $FUNCTION_DIR. 'subsample_and_align.php';
require_once $FUNCTION_DIR. 'both.php';
require_once $FUNCTION_DIR. 'get_tree_lengths.php';
require_once $CLASS_DIR. 'tree_lengths.php';

?>
