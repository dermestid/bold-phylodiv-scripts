<?php

require_once '../include/class/data_file.php';
require_once '../include/config/saved_data.php'; // $SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER

// Queries data file or database for columns
// returns a generator which yields values of form [field1=>value, field2=>value, ... ]
// for each row in the database/file, where fieldN are values in $fields
function get_data_g(array $fields, bool $associative = false, $file = null, $delim = null) {
    global $SAVED_DATA_INDEX, $SAVED_DATA_INDEX_DELIMITER;
    $file ??= $SAVED_DATA_INDEX;
    $delim ??= $SAVED_DATA_INDEX_DELIMITER;

    $handle = open_data($file, $delim);
    if ($handle === false) return false;
    try {
        yield from next_data_g($handle, $fields, $associative);
    } finally {
        close_data($handle);
    }
}

// implementation detail

function open_data($file, $delim) {
    return Data_File::open($file, $delim, false);
}

function next_data_g($handle, array $fields, bool $associative) {
    $entry = $handle->read_entry();
    while ($entry) {
        $vals = array_map(
            fn($field) =>$entry[array_search($field, $handle->get_header())], 
            $fields);
        if ($associative) $vals = array_combine($fields, $vals);
        yield $vals;
        $entry = $handle->read_entry();
    } 
}

function close_data($handle) {
    Data_File::close($handle);
}
    

?>
