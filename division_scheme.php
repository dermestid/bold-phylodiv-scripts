<?php

// Returns a string representing the current geographical division scheme parameters
function division_scheme() {
    global $GEO_DIVISION_SCHEME, $GEO_DIVISION_SCHEMES;
    global $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;

    if ($GEO_DIVISION_SCHEME === $GEO_DIVISION_SCHEMES['COORDS']) {
        return 'COORDS_' . $LATITUDE_GRID_SIZE_DEG . 'x' . $LONGITUDE_GRID_SIZE_DEG;
    } 
    else if ($GEO_DIVISION_SCHEME === $GEO_DIVISION_SCHEMES['COUNTRY']) {
        return 'COUNTRY';
    }
    return '';
}

?>