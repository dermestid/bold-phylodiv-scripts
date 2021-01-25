export default function pick_colour(f, fc, accessor) {

    if (pick_colour.colours === undefined)
        pick_colour.colours = ['#2c7bb6', '#abd9e9', '#e4efaf', '#fdae61', '#d7191c'];

    // cache the scale thresholds
    if (pick_colour.scales === undefined)
        pick_colour.scales = {};
    if (pick_colour.scales[fc.id] == null) {
        const data = fc.features.map(accessor).sort((a, b) => a - b);
        pick_colour.scales[fc.id] = d3
            .scaleCluster()
            .domain(data)
            .range(pick_colour.colours)
            .clusters();
    }

    const x = accessor(f);
    for (let i = 0; i < pick_colour.scales[fc.id].length; i++) {
        if (x < pick_colour.scales[fc.id][i]) {
            return pick_colour.colours[i];
        }
    }
    return pick_colour.colours[pick_colour.colours.length - 1];
}