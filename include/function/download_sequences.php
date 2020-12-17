<?php

require_once $CONFIG_DIR. 'setup.php'; // $CLI
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $FUNCTION_DIR. 'get_sequence_file.php';
require_once $CONFIG_DIR. 'constants.php'; // $SETS_DATA_DELIMITER, $SEQUENCE_DATA_DELIMITER
require_once $CLASS_DIR. 'bold.php';
require_once $FUNCTION_DIR. 'make_fasta_header.php';
require_once $CLASS_DIR. 'status.php';
require_once $FUNCTION_DIR. 'say.php';

function download_sequences($taxon, $marker) {
    global $CLI, $ARGS;
    global $SETS_DATA_DELIMITER, $SEQUENCE_DATA_DELIMITER;
    global $DIVISION_SCHEME;

    $sequence_file = get_sequence_file($taxon);
    if (
        file_exists(Sequence_Data::get_file($taxon))
        || file_exists($sequence_file)
    ) {
        // Possibly another download in progress- don't touch them
        if ($CLI)
            $next_args = $ARGS;
        else
            $next_args = $_GET;
        Status::download_busy($next_args);
        exit;
    } else {
        $sequence_cache_handle = fopen($sequence_file, 'w');
        $sequence_index = 0;
    }

    $BOLD_URL_PREFIX = 'http://www.boldsystems.org/index.php/API_Public/combined';
    $SEQUENCE_SOURCE_FORMAT = 'tsv';
    $SOURCE_DELIMITER = "\t";

    // Open stream handle
    $bold_query = $BOLD_URL_PREFIX . '?'
    . 'format=' . $SEQUENCE_SOURCE_FORMAT 
    . '&marker=' . $marker 
    . '&taxon=' . $taxon;	
    $source_handle = fopen($bold_query, 'r');

    // Get source header for indexing columns
    $header = fgetcsv($source_handle, 0, $SOURCE_DELIMITER);
    $header_size = count($header);

    $sets = Sequence_Sets::open($taxon, $SETS_DATA_DELIMITER);
    $sequence_data_header = BOLD::SEQUENCE_DATA_FIELDS;
    array_unshift($sequence_data_header, 'sequence_index', 'file');
    $sequence_data = Sequence_Data::open($taxon, $SEQUENCE_DATA_DELIMITER, $sequence_data_header);

    // Step through stream line by line
    while ($entry = fgetcsv($source_handle, 0, $SOURCE_DELIMITER))
    {
        // Check for broken entry
        $entry_size = count($entry);
        if ($entry_size > $header_size) continue;

        // make associative entry
        $entry = array_pad($entry, $header_size, '');
        $entry = array_combine($header, $entry);

        // Check marker is the one we want to use
        if ($entry[BOLD::MARKER_CODE] != $marker) continue;

        // Get FASTA header
        $sequence_header = make_fasta_header($entry);
        if ($sequence_header === false) continue;

        // Get sequence and write to file after header
        if (($seq = $entry[BOLD::NUCLEOTIDES]) == '') continue;
        fwrite($sequence_cache_handle, $sequence_header . PHP_EOL);
        fwrite($sequence_cache_handle, $seq . PHP_EOL);

        // Store sequence in set for location
        $sets->update_set($taxon, $entry, $sequence_index, $DIVISION_SCHEME);

        // update the user for big downloads
        $sequence_index++;
        if ($sequence_index % 250 == 0)
            say_lastline("Saved {$sequence_index} sequences...");

        // Store the full location etc metadata for the sequence locally, 
        // so we can use a different geographical division scheme in future.
        $keep_fields = array();
        foreach (BOLD::SEQUENCE_DATA_FIELDS as $field)
            array_push($keep_fields, $entry[$field]);

        $sequence_data_entry = $keep_fields;
        array_unshift($sequence_data_entry, $sequence_index, $sequence_file);
        $sequence_data->write_entry($sequence_data_entry);
        
    } // end while loop
    fclose($source_handle);
    fclose($sequence_cache_handle);
    Sequence_Data::close($sequence_data);

    if ($sequence_index == 0) {
        Status::no_sequences($taxon);
        exit;
    }

    // Save  location data in a csv
    $sets->update_sequence_count($taxon, $sequence_index);
    $sets->write_updates();
}

?>
