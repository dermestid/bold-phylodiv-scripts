<?php

function make_geojson(array $result, array $fields) {
    $loc = $result['location']['data'];
    $properties = [];
    foreach($fields as $field)
        $properties[$field] = $result[$field];
    return [
        'type' => 'Feature',
        'key' => $result['location']['key'],
        'properties' => $properties,
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
