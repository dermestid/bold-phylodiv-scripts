<?php

require_once $CLASS_DIR. 'sets.php';
require_once $FUNCTION_DIR. 'subsample_taxset.php';
require_once $FUNCTION_DIR. 'align.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $FUNCTION_DIR. 'say.php';
require_once $CLASS_DIR. 'progress_bar.php';


// Picks subsamples of $subsample_size from groups of sequences listed in given data file, aligns them,
// and outputs a string in NEXUS format consisting of aligned sequences in separate DATA blocks.
// Outputs a warning if any of the subsampled sequences are missing from the file they should be in.
// The parameter &$locations is updated to include only the locations which have been subsampled.
function subsample_and_align($subsample_size, $taxon, &$locations) {
    global $MINIMUM_SAMPLE_COUNT, $LOG_DIR;
    global $SETS_DATA_DELIMITER, $TAXSET_DELIMITER;
    global $TEMP_DIR, $DELETE_TEMP_FILES;

    $CLUSTAL_LOG_SUFFIX = '_CLUSTALW.log';

    if ($subsample_size < $MINIMUM_SAMPLE_COUNT) { 
        $locations = array();
        return ''; 
    }

    // Get the columns from the data file
    $sets_handle = fopen(Sequence_Sets::get_file($taxon), 'r');
    $header = fgetcsv($sets_handle, 0, $SETS_DATA_DELIMITER);
    $c_file = array_search(SETS::FILE, $header);
    $c_taxset = array_search(SETS::TAXSET, $header);
    $c_location = array_search(SETS::LOCATION, $header);
    
    $nexus_string = '#NEXUS' . PHP_EOL;
    $file_offset = strlen($nexus_string);

    $progress = Progress_Bar::open(count($locations));

    // Go through all entries
    while ($entry = fgetcsv($sets_handle, 0, $SETS_DATA_DELIMITER)) {

        $sequence_file = $entry[$c_file];
        $taxset_str = $entry[$c_taxset];
        $location_key = $entry[$c_location];

        if (!array_key_exists($location_key, $locations)) { continue; }

        $progress->update(1);

        // Skip over taxsets smaller than the sample
        // TODO: use the 'count' column instead
        if (count(explode($TAXSET_DELIMITER, $taxset_str)) < $subsample_size) {
            unset($locations[$location_key]);
            continue;
        }

        // Do the subsampling
        [$subsample_id, $subsample_file, $subsample_taxset]
            = subsample_taxset($subsample_size, $taxset_str, $TAXSET_DELIMITER, $sequence_file);
        if ($subsample_taxset === '') { 
            say("Sequences missing from subsample {$subsample_id}");
            continue; 
        }
        
        // Align the subsample
        $alignment_log_file = $LOG_DIR . $subsample_id . $CLUSTAL_LOG_SUFFIX;
        $subsample_file_aligned = align($subsample_file, $alignment_log_file);

        if ($subsample_file_aligned === '') {
            say("Alignment failed for subsample {$subsample_id}");
            continue;
        }

        $nexus_string .= file_get_contents($subsample_file_aligned, 0, NULL, $file_offset);

        if ($DELETE_TEMP_FILES)
        {
            foreach (glob($TEMP_DIR . $subsample_id .'.*') as $file) {
                unlink($file);
            }
        }

    }

    Progress_Bar::close($progress);

    return $nexus_string;
}

?>
