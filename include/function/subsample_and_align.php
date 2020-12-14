<?php

require_once $CLASS_DIR. 'sets.php';
require_once $FUNCTION_DIR. 'subsample_taxset.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $FUNCTION_DIR. 'say.php';
require_once $CLASS_DIR. 'progress_bar.php';
require_once $CLASS_DIR. 'alignment.php';


// Picks subsamples of $subsample_size from groups of sequences listed in given data file, aligns them,
// and outputs a string in NEXUS format consisting of aligned sequences in separate DATA blocks.
// Outputs a warning if any of the subsampled sequences are missing from the file they should be in.
// The parameter &$locations is updated to include only the locations which have been subsampled,
// in the order that they appear in the output string.
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
    $c_count = array_search(SETS::COUNT, $header);

    $alignments = array();

    // Go through all entries
    while ($entry = fgetcsv($sets_handle, 0, $SETS_DATA_DELIMITER)) {

        $sequence_file = $entry[$c_file];
        $taxset_str = $entry[$c_taxset];
        $location_key = $entry[$c_location];

        if (!array_key_exists($location_key, $locations)) { continue; }

        // Skip over taxsets smaller than the sample
        if ($entry[$c_count] < $subsample_size) {
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
        $logfile = $LOG_DIR. $subsample_id . $CLUSTAL_LOG_SUFFIX;
        $alignments[$location_key] = new Alignment($subsample_file, $logfile);
    }

    // Wait for completion of alignment tasks

    $progress = Progress_Bar::open(count($alignments));
    $locations_new = array();
    $nexus_string = '#NEXUS'.PHP_EOL;
    $file_offset = strlen($nexus_string);
    do {
        $alignment_completed = true;
        foreach ($alignments as $loc => $alignment) {

            [$status, $file] = $alignment->get();

            if ($status === Alignment::STATUS_WORKING) {
                $alignment_completed = false;
            } else if ($status === Alignment::STATUS_DONE) {
                $nexus_string .= file_get_contents($file, 0, NULL, $file_offset);
                $locations_new[$loc] = $locations[$loc];
                if ($DELETE_TEMP_FILES)
                {
                    unlink($file);
                    unlink(str_replace('nxs', 'dnd', $file));
                    unlink($alignment->get_input_file());
                }
                $progress->update(1);
                unset($alignment);
            } else {
                say('Alignment failed for subsample in '.$loc);
                $progress->update(1);
                unset($alignment);
            }
        }
    } while(!$alignment_completed);

    Progress_Bar::close($progress);

    $locations = $locations_new;

    return $nexus_string;
}

?>
