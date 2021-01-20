<?php

require_once '../include/function/get_bold_record_count.php';
require_once '../include/config/global.php'; // $CLI
require_once '../include/config/saved_data.php'; // $KEEP_SEQUENCES, $SAVED_DATA_DELIM
require_once '../include/function/taxon_file.php';
require_once '../include/class/data_file.php';
require_once '../include/class/bold.php';
require_once '../include/function/write_to_index_file.php';

// Returns a generator which yields valid entries from BOLD for a given taxon
//
// $required_fields is an indexed array of titles of columns which must not be blank
// $output_fields is an indexed array of titles of columns which will be included in output
// (not including the sequence itself)
//
// yields the header of the source (providing the column titles and indexes), then
// yields associative array [sequence, fields] where
// sequence is the FASTA format sequence
// fields is a numeric array of the fields named by $output_fields, indexed by keys in the source header

function get_sequences_g(string $taxon, array $required_fields, array $output_fields) {
    global $CLI, $KEEP_SEQUENCES, $SAVED_DATA_DELIMITER;

    // first check that the taxon is valid as BOLD may return erroneous entries for blank
    if (!get_bold_record_count($taxon)) return;

    $BOLD_URL_PREFIX = 'http://www.boldsystems.org/index.php/API_Public/combined';
    $SEQUENCE_SOURCE_FORMAT = 'tsv';
    $SOURCE_DELIMITER = "\t";
    $MARKER = 'COI-5P';

    // Open stream handle
    $bold_query = $BOLD_URL_PREFIX . '?'
    . 'format=' . $SEQUENCE_SOURCE_FORMAT 
    . '&marker=' . $MARKER 
    . '&taxon=' . $taxon;	
    $source_handle = fopen($bold_query, 'r');

    try {
        // Get source header for indexing columns
        $header = fgetcsv($source_handle, 0, $SOURCE_DELIMITER);
        yield $header;
        $header_size = count($header);

        $marker_col = array_search(BOLD::MARKER_CODE, $header);
        $sequence_col = array_search(BOLD::NUCLEOTIDES, $header);
        $output_fields_keyed = array_intersect($header, $output_fields);
        $required_cols = array_keys(array_intersect($header, $required_fields));

        if ($CLI && $KEEP_SEQUENCES) {
            $out_file = taxon_file($taxon);
            if (!file_exists($out_file)) {
                $out_header = array_merge(
                    array_values($output_fields_keyed), [BOLD::NUCLEOTIDES]);
                $out_handle = Data_File::open(
                    $out_file,
                    $SAVED_DATA_DELIMITER,
                    true, // append
                    $out_header
                );
            }
        }

        // Step through stream line by line
        while ($entry = fgetcsv($source_handle, 0, $SOURCE_DELIMITER))
        {
            // Check for broken entry (will have extra or missing fields)
            if (count($entry) !== $header_size) continue;

            // Check marker is the one we want to use
            if ($entry[$marker_col] != $MARKER) continue;

            // Check that required fields are not blank
            foreach ($required_cols as $col) {
                if ($entry[$col] === '') continue 2; // continues the while loop
            }

            $saved_fields = array_intersect_key($entry, $output_fields_keyed);
            $output = [
                'sequence' => $entry[$sequence_col],
                'fields' => $saved_fields
            ];

            yield $output;  

            if (isset($out_handle)) 
                $out_handle->write_entry(
                    array_merge(
                        array_values($saved_fields), 
                        [$entry[$sequence_col]]));
  
        } // end while loop
    } finally {
        fclose($source_handle);
        if(isset($out_handle)){
            Data_File::close($out_handle);
            write_to_index_file($taxon, $out_file);
        }
    }
}

?>
