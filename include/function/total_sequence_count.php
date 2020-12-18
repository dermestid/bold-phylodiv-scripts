<?php 

require_once $CLASS_DIR. 'sequence_sets.php';
require_once $CLASS_DIR. 'sets.php';

// lookup total_sequence_count field in the data file
function total_sequence_count($taxon) {
    global $SETS_DATA_DELIMITER;

    $sets = Sequence_Sets::open($taxon, $SETS_DATA_DELIMITER);
    $count = $sets->get_entry($taxon, SETS::TOTAL_SEQUENCE_COUNT);
    
    if ($count === false) {
        // count up all the sequences in the file
        // TODO
        exit("Error: Unimplemented total_sequence_count({$taxon}) requested.");
    } else {
        return intval($count);
    }
}

?>
