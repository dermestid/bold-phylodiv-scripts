<?php

require_once $CONFIG_DIR. 'setup.php'; // $TEMP_DIR, $WINDOWS, $CLI, $APP_NAME

// adapted from code of user Peec / dell_petter at hotmail dot com
// at https://www.php.net/manual/en/function.exec.php#88704
class Process{
    private int $pid;
    private string $command;
    private string $temp_out;

    public function __construct(string $command, $outfile = null){
        global $TEMP_DIR;

        do {
            $this->temp_out = $TEMP_DIR.uniqid('proc');
        } while(file_exists($this->temp_out));

        $this->command = $command;

        $this->run($outfile);
    }
    private function run($outfile = null){
        global $WINDOWS;

        if ($outfile === null)
            $outfile = $this->temp_out;
        
        $spec = array(1 => array('file', $outfile, 'w'));

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
        global $TEMP_DIR, $WINDOWS;

        // Clear the temp files
        self::clear_temp_proc();

        $op = array();
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
        global $CLI, $TEMP_DIR, $APP_NAME;

        if ($CLI)
            array_map('unlink', glob("{$TEMP_DIR}proc*"));
        else {
            // On web, we cannot glob filesystem with complete paths.
            // Get the current app-relative path from the request URI,
            // then get the relative path from here to the temp dir.
            $called_url = $_SERVER['REQUEST_URI'];
            $called_app_path = explode($APP_NAME, $called_url, 2)[1];
            $dir_depth = substr_count($called_app_path, '/') - 1;
            $temp_path = str_repeat('../', $dir_depth).'out/temp/';
            array_map('unlink', glob("{$temp_path}proc*"));
        } 
    }
}
?>
