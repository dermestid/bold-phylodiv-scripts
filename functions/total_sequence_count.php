<?php 

include_once $FUNCTIONS_DIR. 'taxsets_data_file.php';

// lookup total_sequence_count field in taxsets_data_file($taxon)
function total_sequence_count($taxon) {
    global $TAXSETS_DATA_DELIMITER;

    $data_file = taxsets_data_file($taxon);
    $data_handle = fopen($data_file, 'r');

    $header = fgetcsv($data_handle, 0, $TAXSETS_DATA_DELIMITER);
    $fields = array_flip($header);
    while($entry = fgetcsv($data_handle, 0, $TAXSETS_DATA_DELIMITER)) {
        $entry_taxon = $entry[$fields[TAXSETS::TAXON]];
        if ($entry_taxon != $taxon) { continue; }

        $entry_count = $entry[$fields[TAXSETS::TOTAL_SEQUENCE_COUNT]];
        if ($entry_count == '') { return 0; }

        return intval($entry_count);
    }

    return 0;
}

?>