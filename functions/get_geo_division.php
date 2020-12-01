<?php

// Returns a string encoding the location of the specimen represented by array $entry.
// Expects $entry to be of same size as $fields, 
// and $fields should be an array of strings describing the fields of $entry (ie, the table header).
// The strings required in $fields depend on the current $GEO_DIVISION_SCHEME.
// The output of this function for given input varies with division_scheme().
function get_geo_division($entry, $fields) {
    global $GEO_DIVISION_SCHEME, $GEO_DIVISION_SCHEMES;
    global $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;

    $col = array_flip($fields);

    // Check location is present: either coordinates or country
    $country = $lat = $lon = '';
    if ($GEO_DIVISION_SCHEME === $GEO_DIVISION_SCHEMES['COORDS']) {
        if (($lat = $entry[$col['lat']]) == '') { return false; }
        if (($lon = $entry[$col['lon']]) == '') { return false; }

        $lat_a = floor($lat / $LATITUDE_GRID_SIZE_DEG) * $LATITUDE_GRID_SIZE_DEG;
		$lat_b = $lat_a + $LATITUDE_GRID_SIZE_DEG;
		$grid_lat = $lat_a . 'to' . $lat_b;
		$lon_a = floor($lon / $LONGITUDE_GRID_SIZE_DEG) * $LONGITUDE_GRID_SIZE_DEG;
		$lon_b = $lon_a + $LONGITUDE_GRID_SIZE_DEG;
		$grid_lon = $lon_a . 'to' . $lon_b;
		return 'lat_' . $grid_lat . '_lon_' . $grid_lon;
    } else if ($GEO_DIVISION_SCHEME === $GEO_DIVISION_SCHEMES['COUNTRY']) {
        if (($country = $entry[$col['country']]) == '') { return false; }
        return $country;
    } else {
        return false;
    }
}

?>


