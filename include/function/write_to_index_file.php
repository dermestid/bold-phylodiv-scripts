<?php

require_once '../include/config/saved_data.php'; // $SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER
require_once '../include/class/data_file.php';

function write_to_index_file($taxon, $file) {
    global $SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER;

    $handle = Data_File::open($SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER);
    $handle->write_entry([$taxon, $file]);
    Data_File::close($handle);
}

?>
