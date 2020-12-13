<?php

require_once $FUNCTIONS_DIR. 'say.php';

class Progress_Bar {

    private const START = '<';
    private const BLANK = ' ';
    private const INCREMENT = '-';
    private const END = '>';
    private const DO_PERCENTAGE = true;

    private int $max;
    private int $len;
    private float $val = 0;

    private function __construct(int $max_, int $len_) {

        $this->max = $max_;
        $this->len = $len_;

        echo("\r");
        echo(self::START);
    }

    // Not threadsafe
    public static function open(int $max_, int $len_ = 20) {
        global $output_blocked;

        if ($output_blocked > 0) { return false; }

        $output_blocked++;
        return new Progress_Bar($max_, $len_);
    }

    // Not threadsafe
    public static function close(Progress_Bar $b) {
        global $output_blocked;

        echo (PHP_EOL);
        $output_blocked--;
        unset($b);
    }

    // Not threadsafe if used on the same object. TODO: make this atomic
    public function update($increment) {

        echo("\r"); // Rewrite last line: depends on terminal behaviour, and hope that we didn't add a new line!
       
        $this->val += $increment / $this->max;
        $this->val = min($this->val, 1.0);

        $level = ceil($this->val * $this->len);
        $progress = str_repeat(self::INCREMENT, $level);
        $blank = str_repeat(self::BLANK, $this->len - $level);
        $out = self::START . $progress . $blank . self::END;
        
        if (self::DO_PERCENTAGE) {
            $out .= ' '.ceil(100*$this->val).'%';
        }

        echo($out);
    }
}

?>
