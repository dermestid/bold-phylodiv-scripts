<?php

require_once $CONFIG_DIR. 'constants.php'; // $OUTPUT_FILE, $OUTPUT_FILE_DELIMITER
require_once $CLASS_DIR. 'data_file.php';
require_once $CLASS_DIR. 'division_scheme.php';
require_once $CLASS_DIR. 'location.php';
require_once $FUNCTION_DIR. 'total_sequence_count.php';

// Wraps Data_File functionality with additions for the output file
class Tree_Lengths 
{
    private Data_File $data_file;
    private string $path;
    private array $header;

    private function __construct(array $header) {
        global $OUTPUT_FILE, $OUTPUT_FILE_DELIMITER;

        $this->path = $OUTPUT_FILE;
        $this->header = $header;
        $this->data_file = Data_File::open(
            $this->path,
            $OUTPUT_FILE_DELIMITER,
            $this->header,
            true // always append
        );
    }
    public static function open(Division_Scheme $division_scheme) {
        $header = array(
            'taxon',
            'marker',
            'total_sequence_count',
            'division_scheme',
            'location_key',
            'subsample_size',
            'subsample_tree_length'
        );
        $header = array_merge($header, $division_scheme->saved_params);
        return new Tree_Lengths($header);
    }

    public static function make_entry(
        string $taxon,
        string $marker,
        Location $loc,
        int $subsamples,
        float $tree_len
    ) {
        $entry = array(
            'taxon' => $taxon,
            'marker' => $marker,
            'total_sequence_count' => total_sequence_count($taxon),
            'division_scheme' => $loc->scheme->key,
            'location_key' => $loc->key,
            'subsample_size' => $subsamples,
            'subsample_tree_length' => $tree_len
        );
        foreach ($loc->data as $field => $value) {
			$entry[$field] = $value;
        }
        return $entry;
    }

    public function write_entry(array $entry) {

        $this->data_file->write_entry_assoc($entry);
    }

}

?>
