<?php

require_once __DIR__.DIRECTORY_SEPARATOR. 'taxsets_data_file.php';
require_once __DIR__.DIRECTORY_SEPARATOR. 'sequence_file.php';
require_once __DIR__.DIRECTORY_SEPARATOR. 'sequence_data_file.php';
require_once __DIR__.DIRECTORY_SEPARATOR. 'get_geo_division.php';

// Sorts the downloaded sequences for $taxon into taxsets depending on current division_scheme(),
// and adds them as entries to taxsets_data_file($taxon), which is assumed to already exist with some entries.
// Doesn't do a check if they're already in there, and will duplicate entries if they are.
// Returns an array of the new geographical division names.
function geo_divide($taxon) {
    global $SEQUENCE_DATA_DELIMITER, $TAXSETS_DATA_DELIMITER, $TAXSET_DELIMITER;

    if(!(file_exists($data_file = taxsets_data_file($taxon)))) { 
        exit('Error: non-existent ' .$data_file. ' requested by geo_divide(' .$taxon. ')');
    }
    if(!(file_exists($sequence_file = sequence_file($taxon)))) {
        exit('Error: non-existent ' .$sequence_file. ' requested by geo_divide(' .$taxon. ')');
    }
    if(!(file_exists($sequence_data_file = sequence_data_file($taxon)))) {
        exit('Error: non-existent ' .$sequence_data_file. ' requested by geo_divide(' .$taxon. ')');
    }

    $sequence_index = 0;
    $sequence_counts = array();
    $taxsets = array();

    $sequences_handle = fopen($sequence_file, 'r');
    $sequence_data_handle = fopen($sequence_data_file, 'r');
    $sequence_data_header = fgetcsv($sequence_data_handle, 0, $SEQUENCE_DATA_DELIMITER);
    $fields = array_flip($sequence_data_header);
    while($line = fgets($sequences_handle)) {
        // only count header lines:
        if ((trim($line))[0] != '>') { continue; }

        $sequence_index++;

        // Get the corresponding entry in $sequence_data_file (should most likely be the current line)
        $read_whole_file = false;
        $seq_data_index = 0;
        do {
            $sequence_data_entry = fgetcsv($sequence_data_handle, 0, $SEQUENCE_DATA_DELIMITER);
            if ($sequence_data_entry) {
                if ($sequence_data_entry[$fields['file']] != $sequence_file) {
                    // echo ('wrong file'.PHP_EOL);
                    // continue; 
                } else {
                    $seq_data_index = $sequence_data_entry[$fields['sequence_index']];
                }
            } else {
                if ($read_whole_file) { break; }
                else {
                    // loop round to the beginning
                    $read_whole_file = true;
                    rewind($sequence_data_handle);
                    // skip the header line
                    fgets($sequence_data_handle);
                }
            }
        } while ($seq_data_index != $sequence_index);
        
        if (!$sequence_data_entry) { continue; }

        $loc = get_geo_division($sequence_data_entry, $sequence_data_header);
        if ($loc) {
            // Keep location-specific record of sequences stored
            if(!isset($sequence_counts[$loc])) { $sequence_counts[$loc] = 0; }
            $sequence_counts[$loc]++;

            // Store the index in a location-specific taxset
            if(!isset($taxsets[$loc])) { $taxsets[$loc] = ''; }
            if ($sequence_counts[$loc] > 1) { $taxsets[$loc] .= $TAXSET_DELIMITER; }
            $taxsets[$loc] .= $sequence_index;
        }

        // Update the user as this may take a while
        if ($sequence_index % 500 == 0) {
            echo('Sorted ' . $sequence_index . ' into locations...'.PHP_EOL);
        }
    } // end while loop
    fclose($sequences_handle);
    fclose($sequence_data_handle);

    if ($sequence_index == 0) {
        exit('Error: geo_divide('.$taxon.') requested empty sequence file ' . $sequence_file . PHP_EOL);
    }

    // Save data in a csv

    // Get header
    $taxsets_handle = fopen($data_file, 'r+');
    $taxsets_header = fgetcsv($taxsets_handle, 0, $TAXSETS_DATA_DELIMITER);

    // Jump to end
    fseek($taxsets_handle, 0, SEEK_END);
    
    // Add each new taxset
    foreach ($sequence_counts as $loc => $count)
    {
        $entry = array(
            $taxon,
            division_scheme(),
            $loc,
            $count,
            $sequence_file,
            $taxsets[$loc]
        );

        // Make sure fields are in the expected order
        $entry_fields = array_flip(array(
            field::TAXON,
            field::DIVISION_SCHEME,
            field::LOCATION,
            field::COUNT,
            field::FILE,
            field::TAXSET
        ));
        $entry_shuffle = function ($col) use ($entry, $entry_fields) {
            return $entry[$entry_fields[$col]]; };
        $entry = array_map($entry_shuffle, $taxsets_header);

        fputcsv($taxsets_handle, $entry, $TAXSETS_DATA_DELIMITER);
    }
    fclose($taxsets_handle);

    return array_keys($taxsets);
}

?>