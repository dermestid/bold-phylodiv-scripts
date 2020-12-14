<?php

require_once $FUNCTION_DIR. 'say.php';
require_once $CLASS_DIR. 'process.php';

// groups functions for asynchronously aligning
class Alignment {

    private Process $process;
    private string $infile;
    private string $logfile;

    public function __construct(string $infile_, string $logfile_) {
        global $CLUSTAL_PATH;
        
        $this->infile = $infile_;
        $this->logfile = $logfile_;
        say_verbose("Aligning sequences in {$infile_}...");
    
        $command = $CLUSTAL_PATH 
        . ' -INFILE=' . $infile_ 
        . ' -QUICKTREE -OUTORDER=INPUT -OUTPUT=NEXUS' ;
        $this->process = new Process($command, $logfile_);
    }

    const STATUS_FAIL = 0;
    const STATUS_WORKING = 1;
    const STATUS_DONE = 2;

    // returns array [ STATUS, FILE ] 
    // where STATUS is one of the above consts, 
    // and FILE is the path to the alignment in NEXUS format
    public function get() {

        if ($this->process->status()) {
            return array(self::STATUS_WORKING, '');
        } else {
            // Check file has been output successfully
            $outfile = preg_replace('/\.fas/i', '.nxs', $this->infile);
            if (file_exists($outfile) && filesize($outfile)) {
                return array(self::STATUS_DONE, $outfile);
            } else {
                // diagnose from log file
                $log = file_get_contents($this->logfile);
                if (preg_match('/Only 1 sequence/i', $log)) {
                    say_verbose('Only one sequence given, cannot align.');
                }
        
                return array(self::STATUS_FAIL, '');
            }
        }
    }

    public function get_input_file() {
        return $this->infile;
    }
}

?>

