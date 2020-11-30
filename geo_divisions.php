<?php

require_once __DIR__ .DIRECTORY_SEPARATOR. 'taxsets_data_file.php';
require_once __DIR__ .DIRECTORY_SEPARATOR. 'division_scheme.php';

// Gets the names of stored location taxsets for a given taxon, where taxsets where formed using current settings.
// Looks in taxsets_data_file($taxon) for entries with division_scheme = division_scheme(),
// and returns an array of the values in column 'location'
function geo_divisions($taxon) {
    global $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;
    global $TAXSETS_DATA_DELIMITER;

    $data_file = taxsets_data_file($taxon);
    if (!file_exists($data_file)) { 
        exit('Error: non-existent ' .$data_file. ' requested by geo_divisions(' .$taxon. ')');
    }

    $handle = fopen($data_file, 'r');
    $header = fgetcsv($handle, 0, $TAXSETS_DATA_DELIMITER);
    $fields = array_flip($header);

    if (!(in_array(field::DIVISION_SCHEME, $header) && in_array(field::LOCATION, $header)))
    {
        echo ('Bad data file format: missing column \'header\' or \'location\''.PHP_EOL);
        return array();
    }

    $divisions = array();

    while ($entry = fgetcsv($handle, 0, $TAXSETS_DATA_DELIMITER)) {

        if ($entry[$fields[field::DIVISION_SCHEME]] != division_scheme()) { continue; }

        if ($loc = $entry[$fields[field::LOCATION]]) {
            array_push($divisions, $loc);
        }
    }

    return $divisions;
}

?>