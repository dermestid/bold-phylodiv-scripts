<?php

require_once $FUNCTIONS_DIR. 'say.php';

function align($infile, $logfile) {
    global $CLUSTAL_PATH;
    
    say_verbose("Aligning sequences in {$infile}...");

	$command = $CLUSTAL_PATH 
	. ' -INFILE=' . $infile 
	. ' -QUICKTREE -OUTORDER=INPUT -OUTPUT=NEXUS' 
	. " 1>" . $logfile;
	system($command);

    // Check file has been output successfully
    $outfile = preg_replace('/\.fas/i', '.nxs', $infile);
    if (file_exists($outfile) && filesize($outfile)) {
        return $outfile;
    } else {
        // diagnose from log file
        $log = file_get_contents($logfile);
        if (preg_match('/Only 1 sequence/i', $log)) {
            say_verbose('Only one sequence given, cannot align.');
        }

        return '';
    }
}

?>