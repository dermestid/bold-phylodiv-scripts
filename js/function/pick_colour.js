export default function pick_colour(f, set, accessor) {

    if (pick_colour.colours === undefined)
        pick_colour.colours = ['#2c7bb6', '#abd9e9', '#e4efaf', '#fdae61', '#d7191c'];

    const data = set.map(accessor).sort((a, b) => a - b);
    const scales = d3
        .scaleCluster()
        .domain(data)
        .range(pick_colour.colours)
        .clusters();

    const x = accessor(f);
    for (let i = 0; i < scales.length; i++) {
        if (x < scales[i]) {
            return pick_colour.colours[i];
        }
    }
    return pick_colour.colours[pick_colour.colours.length - 1];
}