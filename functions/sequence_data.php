<?php

require_once $FUNCTIONS_DIR. 'data_file.php';

class Sequence_Data
{
    private Data_File $data_file;

    private array $header = array();
    private int $i_file;
    private int $i_sequence_index;

    private function __construct() {} // factory construct only

    public static function get_file(string $taxon) {
        global $SEQUENCES_DIR;
    
        return $SEQUENCES_DIR . $taxon . '_seq_data.csv';
    }

    public static function open(string $taxon, string $delim, $header_ = array()) {
        $sd = new Sequence_Data();
        $path = self::get_file($taxon);

        $sd->header = $header_;
        $sd->data_file = Data_File::open($path, $delim, $sd->header, false); // does not skip to end
        $sd->i_file = array_search('file', $sd->header, true);
        $sd->i_sequence_index = array_search('sequence_index', $sd->header, true);

        return $sd;
    }

    public static function close(Sequence_Data $sd) {
        Data_File::close($sd->data_file);
        unset($sd);
    }

    public function get_entry(string $seq_file, int $seq_index) {
        $read_whole_file = false;
        $index = 0;
        do {
            $entry = $this->data_file->read_entry();
            if ($entry) {
                if ($entry[$this->i_file] === $seq_file) {
                    $index = intval($entry[$this->i_sequence_index]);
                } else {
                    // echo ("wrong file: {$entry[$this->i_file]}".PHP_EOL);
                    // continue;
                }
            } else { // eof
                if ($read_whole_file) { break; }
                else {
                    // loop round to the beginning, once
                    $read_whole_file = true;
                    $this->data_file->go_to_start();
                }
            }
        } while ($index !== $seq_index);

        if ($entry) {
            return array_combine($this->header, $entry);
        } else {
            return false;
        }
    } // end function get_entry

    public function write_entry(array $entry) {
        $this->data_file->write_entry($entry);
    }
}

?>
