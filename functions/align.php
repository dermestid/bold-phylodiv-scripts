<?php

function align($infile, $logfile) {
    global $CLUSTAL_PATH;
    
    echo('Aligning sequences in ' . $infile . '...' . PHP_EOL);

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
            echo('Only one sequence given, cannot align' . PHP_EOL);
        }

        return '';
    }
}

?>