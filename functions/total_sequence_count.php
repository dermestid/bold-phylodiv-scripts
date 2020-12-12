<?php 

require_once $FUNCTIONS_DIR. 'sequence_sets.php';
require_once $FUNCTIONS_DIR. 'taxsets.php';

// lookup total_sequence_count field in the data file
function total_sequence_count($taxon) {
    global $TAXSETS_DATA_DELIMITER;

    $sets = Sequence_Sets::open($taxon, $TAXSETS_DATA_DELIMITER);
    $count = $sets->get_entry($taxon, TAXSETS::TOTAL_SEQUENCE_COUNT);
    
    if ($count === false) {
        // count up all the sequences in the file
        // TODO
        exit("Unimplemented total_sequence_count({$taxon}) requested.");
    } else {
        return intval($count);
    }
}

?>
