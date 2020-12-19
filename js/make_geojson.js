function make_geojson(data) {

    var geojson = data.map(function (item) {
        var feature = {
            "type": "Feature",
            "properties": {
                "pd": item.subsample_tree_length
            },
            "geometry": {
                "type": "Polygon",
                "coordinates": [[
                    [item.lon_min, item.lat_max],
                    [item.lon_max, item.lat_max],
                    [item.lon_max, item.lat_min],
                    [item.lon_min, item.lat_min]
                ]]
            }
        };
        return feature;
    });

    return geojson;
}