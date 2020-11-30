<?php

// Calculates trees for input aligned samples of sequences.
// $data is a string in NEXUS format specifying sequences and taxsets of aligned sequences.
// $taxset_names is an array of strings which name taxsets set in $data.
// Outputs a single NEXUS file of multiple TREE blocks to $tre_filename.
function make_trees($data, $tree_names, $tre_filename) {
	global $PAUP_PATH, $PAUP_COMMANDS_SETUP, $PAUP_COMMANDS_TREE, $PAUP_COMMANDS_END;
	global $TEMP_DIR, $LOG_DIR;

	$TREE_DECLARATION_REGEX = "/tree\s+'[^']*'\s*=/i";
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

	// Run PAUP
	$command =  $PAUP_PATH . ' ' . $nex_filename .  " 1>" . $LOG_DIR . $nex_id .'.log';
	system($command);

	$tre_temp_name = $nex_filename_base . '.tre';
	if (file_exists($tre_temp_name)) {
		rename($tre_temp_name, $tre_filename);

		// Rename the trees
		$tree_list = file_get_contents($tre_filename);
		$i = 0;
		$rename_tree = function ($s) use ($tree_names, &$i) { 
			$decl = "tree '".$tree_names[$i]."' =";
			$i++;
			return $decl; };
		$tree_list = preg_replace_callback($TREE_DECLARATION_REGEX, $rename_tree, $tree_list);
		file_put_contents($tre_filename, $tree_list);
	}

	// cleanup
}

?>