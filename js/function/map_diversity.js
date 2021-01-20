import map_colours from "./map_colours.js";

export default function map_diversity(results, map) {

    L.geoJSON(results, {
        style: function (feature) {
            var style = {
                color: map_colours()[Math.min(feature.properties.diversity, 4)],
                weight: 2,
                fillOpacity: 0.85,
                stroke: false
            };
            return style;
        }
    }).addTo(map);
}