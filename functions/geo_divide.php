<?php

require_once $FUNCTIONS_DIR. 'taxsets_data_file.php';
require_once $FUNCTIONS_DIR. 'sequence_file.php';
require_once $FUNCTIONS_DIR. 'sequence_data_file.php';
require_once $FUNCTIONS_DIR. 'get_geo_division.php';
require_once $FUNCTIONS_DIR. 'total_sequence_count.php';

// Sorts the downloaded sequences for $taxon into taxsets depending on current $DIVISION_SCHEME,
// and adds them as entries to taxsets_data_file($taxon), which is assumed to already exist with some entries.
// Doesn't do a check if they're already in there, and will duplicate entries if they are.
// Returns an array of the new geographical division names.
function geo_divide($taxon) {
    global $SEQUENCE_DATA_DELIMITER, $TAXSETS_DATA_DELIMITER, $TAXSET_DELIMITER;
    global $DIVISION_SCHEME;

    if(!(file_exists($data_file = taxsets_data_file($taxon)))) { 
        exit("Error: non-existent {$data_file} requested by geo_divide({$taxon})");
    }
    if(!(file_exists($sequence_file = sequence_file($taxon)))) {
        exit("Error: non-existent {$sequence_file} requested by geo_divide({$taxon})");
    }
    if(!(file_exists($sequence_data_file = sequence_data_file($taxon)))) {
        exit("Error: non-existent {$sequence_data_file} requested by geo_divide({$taxon})");
    }

    $sequence_index = 0;
    $sequence_sets = array();

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

        // Make an associative entry
        $sequence_data_entry = array_combine($sequence_data_header, $sequence_data_entry);

        try{
            // throws if entry doesn't have the right fields for $DIVISION_SCHEME
            $loc = Location::read($DIVISION_SCHEME, $sequence_data_entry);
        } catch (LocationException $e) {
            $loc = false;
        }
        if ($loc !== false) {
            if(!isset($sequence_sets[$loc->key])) {
                $sequence_sets[$loc->key] = array(
                    'sequence_count' => 0,
                    'taxset' => '',
                    'location' => $loc
                ); 
            }
            $sequence_sets[$loc->key]['sequence_count']++;

            // Add the index to the taxset
            if ($sequence_sets[$loc->key]['sequence_count'] > 1) {
                $sequence_sets[$loc->key]['taxset'] .= $TAXSET_DELIMITER; 
            }
            $sequence_sets[$loc->key]['taxset'] .= $sequence_index;
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

    $total_sequence_count = total_sequence_count($taxon);

    // Get header
    $taxsets_handle = fopen($data_file, 'r+');
    $taxsets_header = fgetcsv($taxsets_handle, 0, $TAXSETS_DATA_DELIMITER);
    $write_header = false;
    if (!$taxsets_header || $taxsets_header[0] == '') {
        // No header: make a new one
        $taxsets_header = TAXSETS::FIELDS;
        $write_header = true;
    }

    // Add any fields to the header which aren't yet present
    foreach ($DIVISION_SCHEME->saved_params as $field) {
        $f_i = array_search($field, $taxsets_header, true);
        if ($f_i === false) {
            array_push($taxsets_header, $field);
            $write_header = true;
        }
    }
    
    // Update the header if needed
    if ($write_header) {
        $remainder = stream_get_contents($taxsets_handle);
        rewind($taxsets_handle);
        ftruncate($taxsets_handle);
        fputcsv($taxsets_handle, $taxsets_header, $TAXSETS_DATA_DELIMITER);
        fwrite($taxsets_handle, $remainder);
    } else {
        // Jump to end
        fseek($taxsets_handle, 0, SEEK_END);
    }

    
    // Add each new taxset
    foreach ($sequence_sets as $loc_key => $data)
    {
        $entry = array_combine(TAXSETS::FIELDS, array(
            $taxon,
            $total_sequence_count,
            $DIVISION_SCHEME->key,
            $loc_key,
            $data['sequence_count'],
            $sequence_file,
            $data['taxset']
        ));
        $entry = array_merge($entry, $data['location']->data);

        // Make sure fields are in the expected order
        $entry_shuffle = function ($col) use ($entry) {
            return $entry[$col]; };
        $entry = array_map($entry_shuffle, $taxsets_header);

        fputcsv($taxsets_handle, $entry, $TAXSETS_DATA_DELIMITER);
    }
    fclose($taxsets_handle);

    $get_location = function ($seq_data) { return $seq_data['location']; };
    return array_map($get_location, $sequence_sets);
}

?>