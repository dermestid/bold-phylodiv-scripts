<?php

function sequence_data_file($taxon) {
    global $SEQUENCES_DIR;

    return $SEQUENCES_DIR . $taxon . '_seq_data.csv';

}

?>