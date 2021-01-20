<?php

require_once '../include/config/saved_data.php'; // $SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER
require_once '../include/class/data_file.php';

function taxon_file(string $taxon, bool $must_exist = false) {
    global $SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER;

    // Search in index file
    $handle = Data_File::open($SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER, false);
    $header = $handle->get_header();
    $taxon_col = array_search('taxon', $header);
    $file_col = array_search('file', $header);
    while ($entry = $handle->read_entry()) {
        if ($entry[$taxon_col] === $taxon) return $entry[$file_col];
    }
    Data_File::close($handle);

    if ($must_exist) return false;

    // not found in index file: return new
    return "../data/saved/{$taxon}.csv";
}

?>
