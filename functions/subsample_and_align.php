<?php

require_once $FUNCTIONS_DIR. 'subsample_taxsets.php';
require_once $FUNCTIONS_DIR. 'align.php';
require_once $FUNCTIONS_DIR. 'taxsets_data_file.php';


// Picks subsamples of $subsample_size from groups of sequences listed in given data file, aligns them,
// and outputs a string in NEXUS format consisting of aligned sequences in separate DATA blocks.
// Outputs a warning if any of the subsampled sequences are missing from the file they should be in,
// or if any samples are of size 1 and so cannot be aligned.
function subsample_and_align($subsample_size, $taxon, $taxset_locations) {
    global $MINIMUM_SUBSAMPLE_NUMBER, $LOG_DIR;
	global $TAXSETS_DATA_DELIMITER, $TAXSET_DELIMITER;

    $CLUSTAL_LOG_SUFFIX = '_CLUSTALW.log';

    if ($subsample_size < $MINIMUM_SUBSAMPLE_NUMBER) { return ''; }

    // Get the columns from the data file
    $taxsets_data_handle = fopen(taxsets_data_file($taxon), 'r');
    $col = array_flip(fgetcsv($taxsets_data_handle, 0, $TAXSETS_DATA_DELIMITER));

    $nexus_string = '#NEXUS' . PHP_EOL;
    $file_offset = strlen($nexus_string);

    $taxon_number = 0;
    $taxset_names = array();

    // Go through all entries
    while ($entry = fgetcsv($taxsets_data_handle, 0, $TAXSETS_DATA_DELIMITER)) {

        $sequence_file = $entry[$col[field::FILE]];
        $taxset_str = $entry[$col[field::TAXSET]];
        $location = $entry[$col[field::LOCATION]];

        if (!in_array($location, $taxset_locations)) { continue; }

        if (count(explode($TAXSET_DELIMITER, $taxset_str)) < $MINIMUM_SUBSAMPLE_NUMBER) {
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

        continue;

        // Add a taxset string for the sample
        // Since we're storing multiple taxsets in the same file, add an offset of $taxon_number
        $taxset = explode($TAXSET_DELIMITER, $subsample_taxset);
        $current_subsample_size = count($taxset);
        $taxset = range($taxon_number + 1, $taxon_number + $current_subsample_size);
        $subsample_taxset = implode($TAXSET_DELIMITER, $taxset);

        $nexus_string .= 
'
BEGIN ASSUMPTIONS;
    taxset ' . $subsample_id . ' = ' . $subsample_taxset . ';
end;
';

        $taxon_number += count($taxset);
        array_push($taxset_names, $subsample_id);
    }

    return $nexus_string;
}

?>