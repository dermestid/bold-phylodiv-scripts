<?php

require_once '../include/config/global.php'; // $WINDOWS, $CLI, $APP_NAME

// adapted from code of user Peec / dell_petter at hotmail dot com
// at https://www.php.net/manual/en/function.exec.php#88704
class Process{
    const TEMP_DIR = 'temp/';

    private int $pid;
    private string $command;
    private string $temp_out;

    public function __construct(string $command, string $outfile = ''){

        do {
            $this->temp_out = self::TEMP_DIR . uniqid('proc');
        } while(file_exists($this->temp_out));

        $this->command = $command;

        $this->run($outfile);
    }
    private function run(string $outfile = ''){
        global $WINDOWS;

        if ($outfile === '')
            $outfile = $this->temp_out;
        
        $spec = [1 => ['file', $outfile, 'w']];

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
        return self::get_status($this->pid);
    }

    public static function get_status($pid) {
        global $WINDOWS;

        // Clear the temp files
        self::clear_temp_proc();

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

    private static function clear_temp_proc() {
        array_map('unlink', glob(self::TEMP_DIR."proc*"));
    }
}
?>
