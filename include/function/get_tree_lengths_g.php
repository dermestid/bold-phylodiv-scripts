<?php

require_once '../include/config/paup.php'; // $PAUP_PATH, $PAUP_COMMANDS_SETUP, $PAUP_COMMANDS_TREE, $PAUP_COMMANDS_END

// given $alignments in NEXUS format, specifying a series of DATA blocks of aligned sequences,
// calls PAUP to generate a tree for each data block, and return their lengths as a generator.
function get_tree_lengths_g(string $alignments, bool $save_trees = false, string $log_file = "") {
	global $PAUP_PATH, $PAUP_COMMANDS_SETUP, $PAUP_COMMANDS_TREE, $PAUP_COMMANDS_END;

	$DATA_BLOCK_REGEX = '/matrix\s*[^;]*;\s*end\s*;/is';
    $BRANCH_SUM_REGEX = '/Sum\s+([-\d\.e]+)/i';

    // Make the input NEXUS from $alignments and the template scripts

    if (!file_exists($PAUP_COMMANDS_SETUP)) return false;
	$paup_setup = file_get_contents($PAUP_COMMANDS_SETUP);
	if ($log_file !== "")
		$paup_setup = str_replace(
            '[LOG]', 
            "log file={$log_file};", 
            $paup_setup);
    $nexus_string = '#NEXUS' . PHP_EOL;
	if (substr($alignments, 0, strlen($nexus_string)) == $nexus_string)
		$nexus = substr_replace($alignments, $nexus_string . $paup_setup . PHP_EOL, 0, strlen($nexus_string));
	else
		$nexus = $paup_setup . $alignments;

	if (!file_exists($PAUP_COMMANDS_TREE)) return false;
    $paup_maketree = file_get_contents($PAUP_COMMANDS_TREE);
    if ($save_trees) {
        $paup_maketree = str_replace(
            '[SAVETREES]', 
            'savetrees format=nexus root=yes brlen=yes append=yes;'.
            $paup_maketree);
    }

	$append_script = fn($match) => $match[0] .PHP_EOL. $paup_maketree .PHP_EOL;
	$nexus = preg_replace_callback($DATA_BLOCK_REGEX, $append_script, $nexus);
	
	if (!file_exists($PAUP_COMMANDS_END)) return false;
    $nexus .= file_get_contents($PAUP_COMMANDS_END);

    // Write commands to input file

    do {
		$nex_id = uniqid('paup');
		$nex_filename_base = $nex_id;
		$nex_filename = $nex_filename_base . '.nex';
    } while (file_exists($nex_filename));
    file_put_contents($nex_filename, $nexus);
    
    try{
        // Run PAUP
        $command =  $PAUP_PATH. ' ' . $nex_filename; // . ' -n';    
        $spec = [1 => ['pipe', 'w']];
        $proc = proc_open($command, $spec, $pipes);
        $paup_out = stream_get_contents($pipes[1]);
    } finally {
        unlink($nex_filename);
    }

    // Extract branch sums
    if(preg_match_all($BRANCH_SUM_REGEX, $paup_out, $branch_sums)) {
        // trees found
        foreach ($branch_sums[1] as $sum)
            yield floatval($sum);
    } else return false;
}

?>
