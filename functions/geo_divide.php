<?php

require_once $FUNCTIONS_DIR. 'sequence_file.php';
require_once $FUNCTIONS_DIR. 'total_sequence_count.php';

require_once $FUNCTIONS_DIR. 'sequence_sets.php';
require_once $FUNCTIONS_DIR. 'sequence_data.php';

// Sorts the downloaded sequences for $taxon into taxsets depending on current $DIVISION_SCHEME,
// and adds them as entries to the sequence sets file, which is assumed to already exist with some entries.
// Doesn't do a check if they're already in there, and will duplicate entries if they are.
// Returns an array of the new geographical division names.
function geo_divide($taxon) {
    global $SEQUENCE_DATA_DELIMITER, $TAXSETS_DATA_DELIMITER;
    global $DIVISION_SCHEME;

    if(!(file_exists($data_file = Sequence_Sets::get_file($taxon)))) { 
        exit("Error: non-existent {$data_file} requested by geo_divide({$taxon})");
    }
    if(!(file_exists($sequence_file = sequence_file($taxon)))) {
        exit("Error: non-existent {$sequence_file} requested by geo_divide({$taxon})");
    }
    if(!(file_exists($sequence_data_file = Sequence_Data::get_file($taxon)))) {
        exit("Error: non-existent {$sequence_data_file} requested by geo_divide({$taxon})");
    }

    $sequence_index = 0;

    $sets = Sequence_Sets::open($taxon, $TAXSETS_DATA_DELIMITER);
    $sequence_data = Sequence_Data::open($taxon, $SEQUENCE_DATA_DELIMITER);
    $sequences_handle = fopen($sequence_file, 'r');

    while($line = fgets($sequences_handle)) {
        // only count header lines:
        if ((trim($line))[0] != '>') { continue; }

        $sequence_index++;
        
        $sequence_data_entry = $sequence_data->get_entry($sequence_file, $sequence_index);
        if (!$sequence_data_entry) { continue; }

        $sets->update_set($taxon, $sequence_data_entry, $sequence_index, $DIVISION_SCHEME);

        // Update the user as this may take a while
        if ($sequence_index % 500 == 0) {
            echo('Sorted ' . $sequence_index . ' into locations...'.PHP_EOL);
        }
    } // end while loop
    fclose($sequences_handle);
    Sequence_Data::close($sequence_data);

    if ($sequence_index == 0) {
        exit('Error: geo_divide('.$taxon.') requested empty sequence file ' . $sequence_file . PHP_EOL);
    }

    // Save data in a csv

    $total_sequence_count = total_sequence_count($taxon);
    $sets->update_sequence_count($taxon, $total_sequence_count);
    return $sets->write_updates('location');
}

?>