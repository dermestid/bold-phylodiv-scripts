<?php

function get_sequence_file($taxon) {
    global $SEQUENCES_DIR;

    return $SEQUENCES_DIR . $taxon . '_sequences.fas';
}

?>