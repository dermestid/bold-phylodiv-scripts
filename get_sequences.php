<?php

require_once __DIR__ .DIRECTORY_SEPARATOR. 'sequence_file.php';
require_once __DIR__ .DIRECTORY_SEPARATOR. 'get_geo_division.php';
require_once __DIR__ .DIRECTORY_SEPARATOR. 'taxsets_data_file.php';
require_once __DIR__ .DIRECTORY_SEPARATOR. 'division_scheme.php';
require_once __DIR__ .DIRECTORY_SEPARATOR. 'sequence_data_file.php';

// Look for downloaded sequences of $marker for $taxon. If no taxsets_data_file is found,
// download the sequences from BOLD, and store subsets of them by location in a new taxsets_data_file.
// Return { DOWNLOAD_ATTEMPTED:bool, PATH:string }
// where PATH is the path to the taxsets_data_file.
// If download failed, return { true, false }.
function get_sequences($taxon, $marker) {
    global $SEQUENCE_DATA_DELIMITER;
    global $TAXSETS_DATA_DELIMITER, $TAXSET_DELIMITER;

    $BOLD_URL_PREFIX = 'http://www.boldsystems.org/index.php/API_Public/combined';
    $SEQUENCE_SOURCE_FORMAT = 'tsv';
    $SOURCE_DELIMITER = "\t";

    // this array defines the columns which we want to keep locally as data about a sequence's location etc.
    // Make sure to check the output from BOLD if there are any format changes; 
    // otherwise, there will be fields missing from the saved data.
    $KEEP_SOURCE_FIELDS = array(
        'institution_storing',
        'collection_event_id',
        'collectiondate_start',
        'collectiondate_end',
        'collectiontime',
        'collection_note',
        'site_code',
        'sampling_protocol',
        'habitat',
        'notes',
        'lat',
        'lon',
        'coord_source',
        'coord_accuracy',
        'elev',
        'depth',
        'elev_accuracy',
        'depth_accuracy',
        'country',
        'province_state',
        'region',
        'sector',
        'exactsite'
    );

    $taxsets_data_file = taxsets_data_file($taxon);
    if (file_exists($taxsets_data_file)) {
        return array(false, $taxsets_data_file);
    }

    echo('Downloading sequences for ' . $taxon . PHP_EOL);

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
    $sequence_counts = array();
    $sequence_count_total = 0;
    $taxsets = array();

    $sequence_data_file = sequence_data_file($taxon);
    $sequence_data_handle = fopen($sequence_data_file, 'w');
    $sequence_data_header = $KEEP_SOURCE_FIELDS;
    array_unshift($sequence_data_header, 'sequence_index', 'file');
    fputcsv($sequence_data_handle, $sequence_data_header, $SEQUENCE_DATA_DELIMITER);

    // Step through stream line by line
    while ($entry = fgetcsv($source_handle, 0, $SOURCE_DELIMITER))
    {
        // Check marker is the one we want to use
        if ($entry[$fields['markercode']] != $marker) { continue; }

        // Make sequence header
        if (($id = $entry[$fields['processid']]) == '') { continue; }
        $id = preg_replace('/-/', '_', $id);
        if (($species = $entry[$fields['species_name']]) != '') {
            $sequence_header = preg_replace('~[-/ ]~', '_', $species) . '|' . $id;
        } else if (($genus = $entry[$fields['genus_name']]) != '') {
            $sequence_header = $genus . '_sp|' . $id;
        } else {
            $sequence_header = $id;
        }
        $sequence_header = '>' . $sequence_header;

        // Get sequence and write to file after header
        if (($seq = $entry[$fields['nucleotides']]) == '') { continue; }
        fwrite($sequence_cache_handle, $sequence_header . PHP_EOL);
        fwrite($sequence_cache_handle, $seq . PHP_EOL);

        // Get the location of the sequence according to current geographical division scheme
        $loc = get_geo_division($entry, $header);
        if ($loc) { 
            // Keep location-specific record of sequences stored
            if(!isset($sequence_counts[$loc])) { $sequence_counts[$loc] = 0; }
            $sequence_counts[$loc]++;

            // Store the index in a location-specific taxset
            if(!isset($taxsets[$loc])) { $taxsets[$loc] = ''; }
            if ($sequence_counts[$loc] > 1) { $taxsets[$loc] .= $TAXSET_DELIMITER; }
            $taxsets[$loc] .= $sequence_count_total;
        }

        // update the user for big downloads
        $sequence_count_total++;
        if ($sequence_count_total % 250 == 0) {
            echo("Saved " . $sequence_count_total . ' sequences...' . PHP_EOL);
        }

        // Store the full location etc metadata for the sequence locally, 
        // so we can use a different geographical division scheme in future.
        $entry_subset = function ($col) use ($fields, $entry) {
            if (key_exists($col, $fields)) { return $entry[$fields[$col]]; }
            else { return ''; } };
        $sequence_data = array_map($entry_subset, $KEEP_SOURCE_FIELDS);
        array_unshift($sequence_data, $sequence_count_total, $sequence_file);
        fputcsv($sequence_data_handle, $sequence_data, $SEQUENCE_DATA_DELIMITER);
    } // end while loop
    fclose($source_handle);
    fclose($sequence_cache_handle);
    fclose($sequence_data_handle);

    if ($sequence_count_total == 0) {
        echo('No sequences were downloaded. Bad taxon name?' . PHP_EOL);
        return array(true, false);
    }

    // Save data in a csv

    $taxsets_handle = fopen($taxsets_data_file, 'w'); // truncates

    // make header
    $header = array(
        field::TAXON,
        field::DIVISION_SCHEME,
        field::LOCATION,
        field::COUNT,
        field::FILE,
        field::TAXSET
    );
    fputcsv($taxsets_handle, $header, $TAXSETS_DATA_DELIMITER);

    // Store entries
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
        fputcsv($taxsets_handle, $entry, $TAXSETS_DATA_DELIMITER);
    }
    fclose($taxsets_handle);

    return array(true, $taxsets_data_file);

} //  end function get_sequences

?>