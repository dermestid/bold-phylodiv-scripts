<?php
// adapted from code of user Peec / dell_petter at hotmail dot com
// at https://www.php.net/manual/en/function.exec.php#88704
class Process{
    private int $pid;
    private string $command;
    private string $outfile;

    public function __construct($command_, $outfile_){
        $this->command = $command_;
        $this->outfile = $outfile_;
        $this->run();
    }
    private function run(){
        global $WINDOWS;

        $spec = array(1 => array('file', $this->outfile, 'w'));

        if ($WINDOWS) {
            $proc = proc_open($this->command, $spec, $pipes);
        } else {
            // needs testing
            $command = 'nohup '.$this->command;
            $proc = proc_open($command, $spec, $pipes);
        }
        $this->pid = (proc_get_status($proc))['pid'];
    }

    // returns true iff the process is still running
    public function status(){
        global $WINDOWS;

        $op = array();

        if ($WINDOWS) {
            $command = "tasklist /FI \"PID eq {$this->pid}\"";
            exec($command, $op);
            if (isset($op[2])) { return true; }
        } else {
            // @author: Peec
            $command = 'ps -p '.$this->pid;
            exec($command,$op);
            if (isset($op[1]))return true;
        }
        return false;
    }

    public function stop(){
        global $WINDOWS;

        if ($WINDOWS) {
            $command = "taskkill /PID {$this->pid}";
            exec($command);
            return !($this->status());
        } else {
            // @author: Peec
            $command = 'kill '.$this->pid;
            exec($command);
            if ($this->status() == false)return true;
            else return false;
        }
    }
}
?>
