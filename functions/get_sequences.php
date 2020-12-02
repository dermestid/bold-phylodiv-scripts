<?php

require_once $FUNCTIONS_DIR. 'sequence_file.php';
require_once $FUNCTIONS_DIR. 'get_geo_division.php';
require_once $FUNCTIONS_DIR. 'taxsets_data_file.php';
require_once $FUNCTIONS_DIR. 'division_scheme.php';
require_once $FUNCTIONS_DIR. 'sequence_data_file.php';

// Look for downloaded sequences of $marker for $taxon. If no taxsets_data_file is found,
// download the sequences from BOLD, and store subsets of them by location in a new taxsets_data_file.
// Return { DOWNLOAD_ATTEMPTED:bool, PATH:string }
// where PATH is the path to the taxsets_data_file.
// If download failed, return { true, false }.
function get_sequences($taxon, $marker) {
    global $SEQUENCE_DATA_DELIMITER;
    global $TAXSETS_DATA_DELIMITER, $TAXSET_DELIMITER;
    global $DIVISION_SCHEME;

    $BOLD_URL_PREFIX = 'http://www.boldsystems.org/index.php/API_Public/combined';
    $SEQUENCE_SOURCE_FORMAT = 'tsv';
    $SOURCE_DELIMITER = "\t";

    $taxsets_data_file = taxsets_data_file($taxon);
    if (file_exists($taxsets_data_file)) {
        return array(false, $taxsets_data_file);
    }

    echo('Downloading sequences for ' . $taxon . '...' . PHP_EOL);

    // Open stream handle
    $bold_query = $BOLD_URL_PREFIX . '?'
    . 'format=' . $SEQUENCE_SOURCE_FORMAT 
    . '&marker=' . $marker 
    . '&taxon=' . $taxon;	
    $source_handle = fopen($bold_query, 'r');

    // Get source header for indexing columns
    $header = fgetcsv($source_handle, 0, $SOURCE_DELIMITER);
    $fields = array_flip($header);

    $sequence_file = sequence_file($taxon);
    $sequence_cache_handle = fopen($sequence_file, 'w');
    $sequence_index = 0;
    $sequence_sets = array();
    

    $sequence_data_file = sequence_data_file($taxon);
    $sequence_data_handle = fopen($sequence_data_file, 'w');
    $sequence_data_header = BOLD::SEQUENCE_DATA_FIELDS;
    array_unshift($sequence_data_header, 'sequence_index', 'file');
    fputcsv($sequence_data_handle, $sequence_data_header, $SEQUENCE_DATA_DELIMITER);

    // Step through stream line by line
    while ($entry = fgetcsv($source_handle, 0, $SOURCE_DELIMITER))
    {
        
        // make associative entry
        $entry = array_combine($header, $entry);

        // Check marker is the one we want to use
        if ($entry[BOLD::MARKER_CODE] != $marker) { continue; }

        // Make sequence header
        if (($id = $entry[BOLD::PROCESS_ID]) == '') { continue; }
        // Replace bad chars in id
        $id = preg_replace('/-/', '_', $id);
        if (($species = $entry[BOLD::SPECIES_NAME]) != '') {
            // replace bad chars in species name; use it and id
            $sequence_header = preg_replace('~[-/ ]~', '_', $species) . '|' . $id;
        } else if (($genus = $entry[BOLD::GENUS_NAME]) != '') {
            // use genus name and id
            $sequence_header = $genus . '_sp|' . $id;
        } else {
            // use id
            $sequence_header = $id;
        }
        $sequence_header = '>' . $sequence_header;

        // Get sequence and write to file after header
        if (($seq = $entry[BOLD::NUCLEOTIDES]) == '') { continue; }
        fwrite($sequence_cache_handle, $sequence_header . PHP_EOL);
        fwrite($sequence_cache_handle, $seq . PHP_EOL);

        try{
            // throws if entry doesn't have the right fields for $DIVISION_SCHEME
            $loc = Location::read($DIVISION_SCHEME, $entry);
        } catch (LocationException $e) {
            $loc = false;
        }
        if ($loc !== false) {
            // Keep location-specific record of sequences stored
            if(!isset($sequence_sets[$loc->key])) { 
                $sequence_sets[$loc->key] = array(
                    'sequence_count' => 0,
                    'taxset' => '',
                    'location' => $loc
                );
            }
            $sequence_sets[$loc->key]['sequence_count']++;

            // Store the index in a location-specific taxset
            if ($sequence_sets[$loc->key]['sequence_count'] > 1) { 
                $sequence_sets[$loc->key]['taxset'] .= $TAXSET_DELIMITER; 
            }
            $sequence_sets[$loc->key]['taxset'] .= $sequence_index;
        }

        // update the user for big downloads
        $sequence_index++;
        if ($sequence_index % 250 == 0) {
            echo("Saved {$sequence_index} sequences...". PHP_EOL);
        }

        // Store the full location etc metadata for the sequence locally, 
        // so we can use a different geographical division scheme in future.
        $keep_fields = array();
        foreach (BOLD::SEQUENCE_DATA_FIELDS as $field) {
            array_push($keep_fields, $entry[$field]);
        }
        $sequence_data = $keep_fields;
        array_unshift($sequence_data, $sequence_index, $sequence_file);
        fputcsv($sequence_data_handle, $sequence_data, $SEQUENCE_DATA_DELIMITER);
    } // end while loop
    fclose($source_handle);
    fclose($sequence_cache_handle);
    fclose($sequence_data_handle);

    if ($sequence_index == 0) {
        echo('No sequences were downloaded. Bad taxon name?' . PHP_EOL);
        return array(true, false);
    }

    // Save data in a csv

    $taxsets_handle = fopen($taxsets_data_file, 'w'); // truncates

    // make header
    $header = array(
        TAXSETS::TAXON,
        TAXSETS::DIVISION_SCHEME,
        TAXSETS::LOCATION,
        TAXSETS::COUNT,
        TAXSETS::FILE,
        TAXSETS::TOTAL_SEQUENCE_COUNT,
        TAXSETS::TAXSET
    );
    $header = array_merge($header, $DIVISION_SCHEME->saved_params);
    fputcsv($taxsets_handle, $header, $TAXSETS_DATA_DELIMITER);

    // Store entries
    foreach ($sequence_sets as $loc_key => $data)
    {
        $entry = array(
            $taxon,
            $DIVISION_SCHEME->key,
            $loc_key,
            $data['sequence_count'],
            $sequence_file,
            $sequence_index,
            $data['taxset']
        );
        $entry = array_merge($entry, $data['location']->data);
        fputcsv($taxsets_handle, $entry, $TAXSETS_DATA_DELIMITER);
    }
    fclose($taxsets_handle);

    return array(true, $taxsets_data_file);

} //  end function get_sequences

?>