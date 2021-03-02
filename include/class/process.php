<?php

require_once '../include/config/global.php'; // $WINDOWS, $CLI

// adapted from code of user Peec / dell_petter at hotmail dot com
// at https://www.php.net/manual/en/function.exec.php#88704
class Process{
    const TEMP_DIR = 'temp/';

    private int $pid;
    private string $command;
    private string $outfile;

    public function __construct(string $command, string $outfile = ''){
        global $CLI;

        if ($outfile === '') {
            $rand_prefix = $CLI ? 'proc' : hash(
                'crc32b', 
                $_SERVER['REMOTE_ADDR'].'_'.$_SERVER['REQUEST_TIME_FLOAT'].'_'.$_SERVER['REMOTE_PORT']);
            do {
                $this->outfile = self::TEMP_DIR . uniqid($rand_prefix);
            } while(file_exists($this->outfile));
        } else {
            $this->outfile = $outfile;
        }

        $this->command = $command;

        $this->run();
    }
    private function run(){
        global $WINDOWS;
        
        $spec = [1 => ['file', $this->outfile, 'w']];

        if ($WINDOWS) {
            $proc = proc_open('start /b '.$this->command, $spec, $pipes);
            $parent_pid = (proc_get_status($proc))['pid'];
            $get_pid = 
                array_filter(
                    explode(" ", 
                        shell_exec(
                            "wmic process get parentprocessid,processid | find \"$parent_pid\"")));  
            array_pop($get_pid);
            $this->pid = end($get_pid);
        } else {
            // needs testing
            $command = 'nohup '.$this->command . ' &';
            $proc = proc_open($command, $spec, $pipes);
            $this->pid = (proc_get_status($proc))['pid'] + 1;
        }
    }

    public function pid() {
        return $this->pid;
    }

    // returns true iff the process is still running
    public function status(){
        $status = self::get_status($this->pid);
        // if ($status === false) unlink($this->outfile);
        return $status;
    }

    public static function get_status($pid) {
        global $WINDOWS;

        $op = [];
        if ($WINDOWS) {
            $command = "tasklist /FI \"PID eq {$pid}\"";
            exec($command, $op);
            if (isset($op[2])) { return true; }
        } else {
            // @author: Peec
            $command = 'ps -p '.$pid;
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
