<?php

require_once $FUNCTION_DIR. 'get_sequence_file.php';
require_once $FUNCTION_DIR. 'make_fasta_header.php';
require_once $CLASS_DIR. 'sequence_data.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $CLASS_DIR. 'bold.php';
require_once $FUNCTION_DIR. 'say.php';

// Look for downloaded sequences of $marker for $taxon. If no local sequence set file is found,
// download the sequences from BOLD, and store subsets of them by location.
// Return { SUCCESS:bool, DOWNLOAD_ATTEMPTED:bool }
// If download failed, return { false, true }.
function get_sequences($taxon, $marker) {
    global $SEQUENCE_DATA_DELIMITER, $SETS_DATA_DELIMITER;
    global $DIVISION_SCHEME;

    $BOLD_URL_PREFIX = 'http://www.boldsystems.org/index.php/API_Public/combined';
    $SEQUENCE_SOURCE_FORMAT = 'tsv';
    $SOURCE_DELIMITER = "\t";

    $sequence_file = get_sequence_file($taxon);
    $data_file = Sequence_Data::get_file($taxon);
    $sets_file = Sequence_Sets::get_file($taxon);
    if (
        file_exists($data_file)
        && file_exists($sequence_file)
        && file_exists($sets_file)
        // The third condition above can be removed only once total_sequence_count is completed
    ) {
        return array(true, false);
    } else if (file_exists($data_file)) {
        exit ("File mismatch: metadata file {$data_file} but no {$sequence_file}");
    } else if (file_exists($sequence_file)) {
        exit ("File mismatch: sequence file {$sequence_file} but no {$data_file}");
    } else if (file_exists($sets_file)) {
        // Delete it, since it's outdated
        unlink($sets_file);
    }

    say("Downloading sequences for {$taxon}...");

    // Open stream handle
    $bold_query = $BOLD_URL_PREFIX . '?'
    . 'format=' . $SEQUENCE_SOURCE_FORMAT 
    . '&marker=' . $marker 
    . '&taxon=' . $taxon;	
    $source_handle = fopen($bold_query, 'r');

    // Get source header for indexing columns
    $header = fgetcsv($source_handle, 0, $SOURCE_DELIMITER);
    $header_size = count($header);

    $sequence_cache_handle = fopen($sequence_file, 'w');
    $sequence_index = 0;

    $sets = Sequence_Sets::open($taxon, $SETS_DATA_DELIMITER);
    $sequence_data_header = BOLD::SEQUENCE_DATA_FIELDS;
    array_unshift($sequence_data_header, 'sequence_index', 'file');
    $sequence_data = Sequence_Data::open($taxon, $SEQUENCE_DATA_DELIMITER, $sequence_data_header);

    // Step through stream line by line
    while ($entry = fgetcsv($source_handle, 0, $SOURCE_DELIMITER))
    {
        // Check for broken entry
        $entry_size = count($entry);
        if ($entry_size > $header_size) { 
            // say("Bad entry with size {$entry_size}");
            continue;
        }
        // make associative entry
        $entry = array_pad($entry, $header_size, '');
        $entry = array_combine($header, $entry);

        // Check marker is the one we want to use
        if ($entry[BOLD::MARKER_CODE] != $marker) { continue; }

        // Get FASTA header
        $sequence_header = make_fasta_header($entry);
        if ($sequence_header === false) { continue; }

        // Get sequence and write to file after header
        if (($seq = $entry[BOLD::NUCLEOTIDES]) == '') { continue; }
        fwrite($sequence_cache_handle, $sequence_header . PHP_EOL);
        fwrite($sequence_cache_handle, $seq . PHP_EOL);

        // Store sequence in set for location
        $sets->update_set($taxon, $entry, $sequence_index, $DIVISION_SCHEME);

        // update the user for big downloads
        $sequence_index++;
        if ($sequence_index % 250 == 0) {
            say_lastline("Saved {$sequence_index} sequences...");
        }

        // Store the full location etc metadata for the sequence locally, 
        // so we can use a different geographical division scheme in future.
        $keep_fields = array();
        foreach (BOLD::SEQUENCE_DATA_FIELDS as $field) {
            array_push($keep_fields, $entry[$field]);
        }
        $sequence_data_entry = $keep_fields;
        array_unshift($sequence_data_entry, $sequence_index, $sequence_file);
        $sequence_data->write_entry($sequence_data_entry);
    } // end while loop
    fclose($source_handle);
    fclose($sequence_cache_handle);
    Sequence_Data::close($sequence_data);

    if ($sequence_index == 0) {
        say('No sequences were downloaded. Bad taxon name?');
        return array(false, true);
    } else {
        $sets->update_sequence_count($taxon, $sequence_index);
    }

    // Save  location data in a csv
    $sets->write_updates();

    return array(true, true);

} //  end function get_sequences

?>