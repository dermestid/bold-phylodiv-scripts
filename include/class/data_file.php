<?php

// Wraps frequently used processes for managing data files
class Data_File
{
    private $handle;
    private string $path;
    private string $delim;
    private array $header;

    private function __construct(string $path_, string $delim_) {
        $this->path = $path_;
        $this->delim = $delim_;
    }

    // Opens a data file for reading and writing
    // Creates it if it does not yet exist
    // Returns a Data_File object, or false if could not open $path_
    // $delim_ will be used as the data entry delimiter for this file
    // Any values in &$header_ that are not yet columns in the header of the file will be added
    // &$header_ will be updated to contain all the column names currently in the header
    public static function open(
        string $path_, 
        string $delim_, 
        array &$header_ = array(),
        bool $append = true
    ) {

        $is_new_file = !file_exists($path_);

        if ($is_new_file) {
            return self::open_new($path_, $delim_, $header_);
        } else {
            $data_file = new Data_File($path_, $delim_);
            $data_file->handle = fopen($path_, 'r+');
            if ($data_file->handle === false) { return false; }

            $new_header = fgetcsv($data_file->handle, 0, $data_file->delim);
            // Add any fields to the header which aren't yet present
            $new_fields = false;
            foreach ($header_ as $field) {
                if (!in_array($field, $new_header, true)) {
                    array_push($new_header, $field);
                    $new_fields = true;
                }
            }
            $header_ = $new_header;
            $data_file->header = $new_header;

            // Update the header if needed
            if ($new_fields) {
                $remainder = stream_get_contents($data_file->handle);
                rewind($data_file->handle);
                ftruncate($data_file->handle);
                fputcsv($data_file->handle, $data_file->header, $data_file->delim);
                fwrite($data_file->handle, $remainder);
            } else if ($append) {
                // Jump to end
                fseek($data_file->handle, 0, SEEK_END);
            } // else, start just after the header (dangerous for anything but reading)
            return $data_file;
        }
    }

    // Opens a data file for reading and writing, truncating it to empty if it already exists
    // Returns a Data_File object, or false if could not open $path_
    // $delim_ will be used as the data entry delimiter for this file
    // $header_ will be used as column names to write a fresh header
    public static function open_new(string $path_, string $delim_, array $header_) {
        $data_file = new Data_File($path_, $delim_);
        $data_file->handle = fopen($path_, 'w+');
        if ($data_file->handle === false) { return false; }

        $data_file->header = $header_;
        fputcsv($data_file->handle, $data_file->header, $data_file->delim);

        return $data_file;
    }

    // Closes the internal handle of a given data file
    public static function close(Data_file $data_file) {
        fclose($data_file->handle);
        unset($data_file);
    }

    public function read_entry() {
        return fgetcsv($this->handle, 0, $this->delim);
    }

    public function tell() {
        return ftell($this->handle);
    }

    public function go_to_start() {
        rewind($this->handle);
        // skip the header line
        fgets($this->handle);
    }

    // Adds a new entry to the file
    // Returns false if writing failed
    // $entry should be a numeric array of values to write, of equal length to $this->header
    public function write_entry(array $entry) {
        $this->write_entry_assoc(array_combine($this->header, $entry));
    }

    // Adds a new entry to the file
    // Returns false if writing failed
    // $entry should be an associative array of values to write
    // The new entry will have blanks for any columns which are missing from the keys of $entry,
    // and any values of $entry with keys not in $this->header will be ignored
    public function write_entry_assoc(array $entry) {
        // Make sure fields are in the expected order
        $entry_shuffle = function ($col) use ($entry) {
            return $entry[$col]; };
        $entry = array_map($entry_shuffle, $this->header);

        fputcsv($this->handle, $entry, $this->delim);
    }
}

?>