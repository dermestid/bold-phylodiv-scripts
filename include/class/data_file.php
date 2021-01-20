<?php

// Wraps frequently used processes for managing data files
class Data_File
{
    private $handle;
    private string $path;
    private string $delim;
    private array $header;

    private function __construct(string $path, string $delim) {
        $this->path = $path;
        $this->delim = $delim;
    }

    // Opens a data file for reading and writing
    // Creates it if it does not yet exist
    // Returns a Data_File object, or false if could not open $path
    // $delim will be used as the data entry delimiter for this file
    // Any values in &$header that are not yet columns in the header of the file will be added
    // &$header will be updated to contain all the column names currently in the header
    public static function open(
        string $path, 
        string $delim, 
        bool $append = true,
        array &$header = []
    ) {

        $is_new_file = !file_exists($path);

        if ($is_new_file) {
            return self::open_new($path, $delim, $header);
        } else {
            $data_file = new Data_File($path, $delim);
            $data_file->handle = fopen($path, 'r+');
            if ($data_file->handle === false) { return false; }

            $new_header = fgetcsv($data_file->handle, 0, $data_file->delim);
            // Add any fields to the header which aren't yet present
            $new_fields = false;
            foreach ($header as $field) {
                if (!in_array($field, $new_header, true)) {
                    $new_header[] = $field;
                    $new_fields = true;
                }
            }
            $header = $new_header;
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
    // Returns a Data_File object, or false if could not open $path
    // $delim will be used as the data entry delimiter for this file
    // $header will be used as column names to write a fresh header
    public static function open_new(string $path, string $delim, array $header) {
        $data_file = new Data_File($path, $delim);
        $data_file->handle = fopen($path, 'w+');
        if ($data_file->handle === false) { return false; }

        $data_file->header = $header;
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

    public function get_header() {
        return $this->header;
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
    // $entry should be an indexed array of values to write, of equal length to $this->header
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
        $entry = array_map(fn ($col) => $entry[$col], $this->header);

        fputcsv($this->handle, $entry, $this->delim);
    }
}

?>