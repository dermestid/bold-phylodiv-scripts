<?php

require_once '../include/config/saved_data.php'; // $SAVED_DATA_DELIMITER
require_once '../include/class/bold.php';
require_once '../include/function/taxon_file.php';
require_once '../include/class/data_file.php';
require_once '../include/function/get_data_g.php';

function retrieve_sequences_g(string $taxon, array $required_fields) {
    global $SAVED_DATA_DELIMITER;

    // check the header has the right fields and yield it
    $file = taxon_file($taxon, true);
    if ($file === false) return false;
    $handle = Data_File::open($file, $SAVED_DATA_DELIMITER, false);
    $header = $handle->get_header();
    Data_File::close($handle);
    if (array_intersect($required_fields, $header) !== $required_fields) return false;
    yield $header;

    // yield each line as ['sequence' => s, 'fields' => [f]\s]
    $seq_col = array_search(BOLD::NUCLEOTIDES, $header);
    $gen = get_data_g($header, false, $file, $SAVED_DATA_DELIMITER);
    foreach ($gen as $entry) {
        yield [
            'sequence' => $entry[$seq_col],
            'fields' => array_diff_key($entry, [$seq_col => 0])
        ];
    }
}

?>
