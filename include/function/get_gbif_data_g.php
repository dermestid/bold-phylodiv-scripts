<?php

// Generator which yields [ 'count' => count, 'total' => total ] 
// for each species in gbif in a given taxon within a given lat/lon rect
//
// expects args 
// $taxon: string, 
// $rect: [ 'lat_min' =>  w, 'lat_max' => x, 'lon_min' => y, 'lon_max' => z], wxyz: float

function get_gbif_data_g(string $taxon_key, $rect) {
    $GBIF_OCCURENCE_URL_PREFIX = 'http://api.gbif.org/v1/occurrence/search';
    $GBIF_OCCURENCE_FACET = 'speciesKey';
    $GBIF_REQUEST_NUMBER = 1000;

    $decimal_latitude = floatval($rect['lat_min']).','.floatval($rect['lat_max']);
    $decimal_longitude = floatval($rect['lon_min']).','.floatval($rect['lon_max']);

    // Get gbif species and occurence counts in chunks of $GBIF_REQUEST_NUMBER
    $offset = 0;
    $unread_gbif_data = true;
    do {
        $gbif_url = $GBIF_OCCURENCE_URL_PREFIX . '?'
        . 'taxonKey=' . $taxon_key
        . '&facet=' . $GBIF_OCCURENCE_FACET
        . '&decimalLatitude=' . $decimal_latitude
        . '&decimalLongitude=' . $decimal_longitude
        . '&limit=0'
        . '&facetOffset=' . $offset
        . '&facetLimit=' . $GBIF_REQUEST_NUMBER;
        $gbif_data_json = file_get_contents($gbif_url);

        if ($gbif_data_json === false) return false;
        $gbif_data = json_decode($gbif_data_json, true);
        if ($gbif_data === null) return false;

        if (!isset($gbif_data['count'])) return false;
        $total_occurences ??= intval($gbif_data['count']);
        if (!isset($gbif_data['facets'][0]['counts'])) return false;
        $count_data = $gbif_data['facets'][0]['counts'];

        foreach($count_data as $sp_record)
            yield ['count' => $sp_record['count'], 'total' => $total_occurences];

        if (count($count_data) <= $total_occurences)
            $unread_gbif_data = false;
        else
            $offset += $GBIF_REQUEST_NUMBER;
    } while ($unread_gbif_data);
}

?>
