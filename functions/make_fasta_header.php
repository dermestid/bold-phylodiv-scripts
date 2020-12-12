<?php

require_once $FUNCTIONS_DIR. 'bold.php';

function make_fasta_header(array $seq_data) {

    if (($id = $seq_data[BOLD::PROCESS_ID]) == '') { 
        return false; 
    }

    $BAD_CHARS = array('-','/',' ');
    $underscores = array_fill(0, count($BAD_CHARS), '_');

    // Replace bad chars in id
    $id = str_replace($BAD_CHARS, $underscores, $id);

    if (($species = $seq_data[BOLD::SPECIES_NAME]) != '') {
        // replace bad chars in species name; use it and id
        $sequence_header = str_replace($BAD_CHARS, $underscores, $species) . '|' . $id;
    } else if (($genus = $seq_data[BOLD::GENUS_NAME]) != '') {
        // use genus name and id
        $sequence_header = $genus . '_sp|' . $id;
    } else {
        // use id
        $sequence_header = $id;
    }

    return '>' . $sequence_header;
}

?>
