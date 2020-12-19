function draw_pd_map(results) {

    var pd_geojson = make_geojson(results);

    L.geoJSON(pd_geojson, {
        style: function (feature) {
            var style = {
                color: pd_colour(feature.properties.pd),
                weight: 2,
                fillOpacity: 0.85,
                stroke: false
            };
            return style;
        }
    }).addTo(map);
}

function handle_results(results) {
    // Make a table of results
    $("#result").html("<table id=\"result_table\"></table>");
    $("#result_table").prepend("<tr id=\"result_table_head\"></tr>");
    Object.keys(results[0]).forEach(function (item) {
        $("#result_table_head").append(`<th>${item}</th>`);
    });
    results.forEach(function (item, index) {
        $("#result_table").append(`<tr id="${index}"></tr>`);
        Object.keys(item).forEach(function (field) {
            if (field == "subsample_tree_length")
                item[field] += " " + pd_colour(item[field]);
            $(`#${index}`).append(`<td>${item[field]}</td>`);
        });
    });
}