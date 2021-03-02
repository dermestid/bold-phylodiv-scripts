<?php

require_once '../include/class/process.php';

// groups functions for asynchronously aligning
class Alignment {

    private Process $process;
    private string $infile;
    private string $logfile;
    private bool $keep_logs;

    public function __construct(string $infile, string $logfile = '') {
        global $CLUSTAL_PATH, $CLI;

        $rand_prefix = $CLI ? 'l' : hash(
            'crc32b', 
            $_SERVER['REMOTE_ADDR'].'_'.$_SERVER['REQUEST_TIME_FLOAT'].'_'.$_SERVER['REMOTE_PORT']);

        $this->infile = $infile;
        if ($logfile === '') {
            $this->keep_logs = false;
            do {
                $this->logfile = 'log/'.uniqid($rand_prefix).'.log';
            } while (file_exists($this->logfile));
        } else {
            $this->logfile = $logfile;
            $this->keep_logs = true;
        }
    
        $command = $CLUSTAL_PATH 
        . ' -INFILE=' . $infile 
        . ' -QUICKTREE -OUTORDER=INPUT -OUTPUT=NEXUS' ;
        $this->process = new Process($command, $this->logfile);
    }

    const STATUS_FAIL = 0;
    const STATUS_WORKING = 1;
    const STATUS_DONE = 2;

    // returns array [ STATUS, FILE ] 
    // where STATUS is one of the above consts, 
    // and FILE is the path to the alignment in NEXUS format
    public function get() {

        if ($this->process->status()) {
            return [self::STATUS_WORKING, ''];
        } else {
            // Check file has been output successfully
            $outfile = preg_replace('/\.fas/i', '.nxs', $this->infile);
            if (file_exists($outfile) && filesize($outfile)) {

                if (!$this->keep_logs) { unlink($this->logfile); }

                return [self::STATUS_DONE, $outfile];
            } else {
                // diagnose from log file
                $log = file_get_contents($this->logfile);
                if (preg_match('/Only 1 sequence/i', $log)) {
                    // throw an exception?
                    echo 'Only one sequence'.PHP_EOL;
                }
        
                return [self::STATUS_FAIL, ''];
            }
        }
    }

    public function get_input_file() {
        return $this->infile;
    }
}

?>

