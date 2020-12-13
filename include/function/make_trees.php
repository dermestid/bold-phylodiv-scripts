<?php

// Calculates trees for input aligned subsamples of sequences.
// $data is a string in NEXUS format specifying sequences and sets of aligned sequences.
// Outputs a single NEXUS file of multiple TREE blocks to $tre_filename.
function make_trees($data, $tre_filename) {
	global $PAUP_PATH, $PAUP_COMMANDS_SETUP, $PAUP_COMMANDS_TREE, $PAUP_COMMANDS_END;
	global $TEMP_DIR, $LOG_DIR, $DELETE_TEMP_FILES;

	$DATA_BLOCK_REGEX = '/matrix\s*[^;]*;\s*end\s*;/is';

	if (!file_exists($PAUP_COMMANDS_SETUP)) { return false; }
	$paup_setup = file_get_contents($PAUP_COMMANDS_SETUP);

	$nexus_string = '#NEXUS' . PHP_EOL;
	if (substr($data, 0, strlen($nexus_string)) == $nexus_string) {
		$nexus = substr_replace($data, $nexus_string . $paup_setup . PHP_EOL, 0, strlen($nexus_string));
	} else {
		$nexus = $paup_setup . $data;
	}

	if (!file_exists($PAUP_COMMANDS_TREE)) { return false; }
	$paup_maketree = file_get_contents($PAUP_COMMANDS_TREE);

	$append_script = function ($match) use ($paup_maketree) { 
		return $match[0] .PHP_EOL. $paup_maketree .PHP_EOL; 
	};
	$nexus = preg_replace_callback($DATA_BLOCK_REGEX, $append_script, $nexus);
	
	if (!file_exists($PAUP_COMMANDS_END)) { return false; }
	$nexus .= file_get_contents($PAUP_COMMANDS_END);

	do {
		$nex_id = uniqid('paup');
		$nex_filename_base = $TEMP_DIR . $nex_id;
		$nex_filename = $nex_filename_base . '.nex';
	} while (file_exists($nex_filename));
	file_put_contents($nex_filename, $nexus);

	// Remove any old trees with the same filename
	if (file_exists($tre_filename)) {
		unlink($tre_filename);
	}

	// Run PAUP
	$command =  $PAUP_PATH . ' ' . $nex_filename .  " 1>" . $LOG_DIR . $nex_id .'.log';
	system($command);

	$tre_temp_name = $nex_filename_base . '.tre';
	if (file_exists($tre_temp_name)) {
		rename($tre_temp_name, $tre_filename);
	} else {
		return false;
	}

	if ($DELETE_TEMP_FILES) {
		foreach (glob($nex_filename_base .'.*') as $file) {
			unlink($file);
		}
	}
	return true;
}

?>