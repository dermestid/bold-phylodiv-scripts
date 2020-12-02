<?php

require_once $FUNCTIONS_DIR. 'subsample_taxsets.php';
require_once $FUNCTIONS_DIR. 'align.php';
require_once $FUNCTIONS_DIR. 'taxsets_data_file.php';


// Picks subsamples of $subsample_size from groups of sequences listed in given data file, aligns them,
// and outputs a string in NEXUS format consisting of aligned sequences in separate DATA blocks.
// Outputs a warning if any of the subsampled sequences are missing from the file they should be in.
// The parameter &$taxset_locations is updated to include only the locations which have been sampled.
function subsample_and_align($subsample_size, $taxon, &$taxset_locations) {
    global $MINIMUM_SUBSAMPLE_NUMBER, $LOG_DIR;
    global $TAXSETS_DATA_DELIMITER, $TAXSET_DELIMITER;
    global $TEMP_DIR, $DELETE_TEMP_FILES;

    $CLUSTAL_LOG_SUFFIX = '_CLUSTALW.log';

    if ($subsample_size < $MINIMUM_SUBSAMPLE_NUMBER) { 
        $taxset_locations = array();
        return ''; 
    }

    // Get the columns from the data file
    $taxsets_data_handle = fopen(taxsets_data_file($taxon), 'r');
    $header = fgetcsv($taxsets_data_handle, 0, $TAXSETS_DATA_DELIMITER);
    $c_file = array_search(TAXSETS::FILE, $header);
    $c_taxset = array_search(TAXSETS::TAXSET, $header);
    $c_location = array_search(TAXSETS::LOCATION, $header);
    
    $nexus_string = '#NEXUS' . PHP_EOL;
    $file_offset = strlen($nexus_string);

    // Go through all entries
    while ($entry = fgetcsv($taxsets_data_handle, 0, $TAXSETS_DATA_DELIMITER)) {

        $sequence_file = $entry[$c_file];
        $taxset_str = $entry[$c_taxset];
        $location_key = $entry[$c_location];

        if (!array_key_exists($location_key, $taxset_locations)) { continue; }
        // Skip over taxsets smaller than the sample
        if (count(explode($TAXSET_DELIMITER, $taxset_str)) < $subsample_size) {
//          array_splice($taxset_locations, array_search($location_key, $taxset_locations), 1);
            unset($taxset_locations[$location_key]);
            continue;
        }

        // Do the subsampling
        [$subsample_id, $subsample_file, $subsample_taxset]
            = subsample_taxsets($subsample_size, $taxset_str, $TAXSET_DELIMITER, $sequence_file);
        if ($subsample_taxset === '') { 
            echo('Sequences missing from subsample ' . $subsample_id . PHP_EOL);
            continue; 
        }
        
        // Align the subsample
        $alignment_log_file = $LOG_DIR . $subsample_id . $CLUSTAL_LOG_SUFFIX;
        $subsample_file_aligned = align($subsample_file, $alignment_log_file);

        if ($subsample_file_aligned === '') {
            echo('Alignment failed for subsample ' . $subsample_id . PHP_EOL);
            continue;
        }

        $nexus_string .= file_get_contents($subsample_file_aligned, 0, NULL, $file_offset);

        if ($DELETE_TEMP_FILES)
        {
            foreach (glob($TEMP_DIR . $subsample_id .'.*') as $file) {
                // echo('Deleting '.$file.PHP_EOL);
                unlink($file);
            }
        }

    }

    return $nexus_string;
}

?>