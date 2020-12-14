<?php

// Subamples $subsample_size sequences from a given taxset string of sequence indices.
// $taxset_str should be indices at which sequences are found in FASTA format in $sequence_file.
// These indices start at 1, and are separated by $taxset_delimiter.
// Returns an array of form { SUBSAMPLE_ID_NO, SUBSAMPLE_FILE_PATH, SUBSAMPLE_TAXSET_STRING },
// or { '', '', '' } if any sequences in the subsample are missing from $sequence_file.
// If there are less than $subsample_size taxa in $taxset_str, then it will be used without sampling to fetch sequences.
function subsample_taxset($subsample_size, $taxset_str, $taxset_delimiter, $sequence_file) {
    global $TEMP_DIR;
    
    $taxset = explode($taxset_delimiter, $taxset_str);

    // Use the taxset as the subsample if it's too small; otherwise, pick random taxa from it
	if(count($taxset) <= $subsample_size) {
        $subsample = $taxset;
        $subsample_str = $taxset_str;
        $subsample_size = count($taxset);
    } else {
        $subsample = array_rand(array_flip($taxset), $subsample_size);
        $subsample_str = implode($taxset_delimiter, $subsample);
    }

    // Get handle to a new, arbitrarily-named temp file for storing the subsample sequences
    do {
        $subsample_id = uniqid('s');
        $subsample_file = $TEMP_DIR . $subsample_id . '.fas';
    } while (file_exists($subsample_file));
	$subsamples_handle = fopen($subsample_file, 'w');

    $i = 0; // note: taxset indices start at 1, not 0. So increment this BEFORE checking whether to subsample.
    $subsampled_count = 0;
    $sequences_handle = fopen($sequence_file, 'r');

    // Go through the sequences and copy lines at subsampled indices
	while($line = fgets($sequences_handle)) {

		if((trim($line)[0] == '>')){
			$header = true;
			$i++;
        } else { $header = false; }

		if(in_array($i, $subsample)) {
            fwrite($subsamples_handle, $line);
		}
	}
	fclose($subsamples_handle);
	fclose($sequences_handle);

    if ($i < $subsample_size) {
        // something was missing!
        exit('missing sequences in sample');
        return array('','','');
    }

    return array($subsample_id, $subsample_file, $subsample_str);
}

?>