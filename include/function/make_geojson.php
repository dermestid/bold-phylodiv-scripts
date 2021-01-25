<?php

function make_geojson(array $result, string $field) {
    $loc = $result['location']['data'];
    return [
        'type' => 'Feature',
        'properties' => [$field => $result[$field]],
        'geometry' => [
            'type' => 'Polygon',
            'coordinates' => [[
                [$loc['lon_min'], $loc['lat_max']],
                [$loc['lon_max'], $loc['lat_max']],
                [$loc['lon_max'], $loc['lat_min']],
                [$loc['lon_min'], $loc['lat_min']],
                [$loc['lon_min'], $loc['lat_max']]]]]];
}

?>
