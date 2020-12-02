<?php

require_once $FUNCTIONS_DIR. 'taxsets_data_file.php';
require_once $FUNCTIONS_DIR. 'division_scheme.php';

// Gets the names of stored location taxsets for a given taxon, where taxsets where formed using current settings.
// Looks in taxsets_data_file($taxon) for entries with division_scheme = $DIVISION_SCHEME,
// and returns an array of the values in column 'location'
function geo_divisions($taxon) {
    global $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;
    global $TAXSETS_DATA_DELIMITER;
    global $DIVISION_SCHEME;

    $data_file = taxsets_data_file($taxon);
    if (!file_exists($data_file)) { 
        exit("Error: non-existent {$data_file} requested by geo_divisions({$taxon})");
    }

    $handle = fopen($data_file, 'r');
    $header = fgetcsv($handle, 0, $TAXSETS_DATA_DELIMITER);
    $location_data_cols = array();
    foreach ($DIVISION_SCHEME->saved_params as $field) {
        array_push($location_data_cols, array_search($field, $header));
    }

    $c_division_scheme = array_search(TAXSETS::DIVISION_SCHEME, $header);
    $c_location_key = array_search(TAXSETS::LOCATION, $header);

    if ($c_division_scheme === false || $c_location_key === false)
    {
        echo('Bad data file format: missing column \'header\' or \'location\''.PHP_EOL);
        return array();
    }

    $divisions = array();

    while ($entry = fgetcsv($handle, 0, $TAXSETS_DATA_DELIMITER)) {

        if ($entry[$c_division_scheme] !== $DIVISION_SCHEME->key) { continue; }

        if (array_key_exists($c_location_key, $entry) && $entry[$c_location_key] != '') {
            $key = $entry[$c_location_key];
            $data = array();
            foreach ($location_data_cols as $col) {
                $data[$header[$col]] = $entry[$col];
            }
            $loc = Location::load(
                $DIVISION_SCHEME, 
                $data, 
                $entry[$c_location_key]);
            $divisions[$key] = $loc;
        }
    }

    return $divisions;
}

?>