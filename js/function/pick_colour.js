export default function pick_colour(f, set, accessor, id = "", rounding = 0, method = { deciles: true, clusters: false }) {
    if (pick_colour.scale_cache === undefined)
        pick_colour.scale_cache = {};

    pick_colour.interval = (id, i) => {
        if (i === 0)
            return `0 - ${pick_colour.scale_cache[id][i]}`;
        else if (i >= pick_colour.scale_cache[id].length)
            return `> ${pick_colour.scale_cache[id][pick_colour.scale_cache[id].length - 1]}`;
        else
            return `${pick_colour.scale_cache[id][i - 1]} - ${pick_colour.scale_cache[id][i]}`
    };

    const round = n => {
        if (rounding <= 0) return n;
        if (n === 0) return n;
        const abs_n = (n > 0) ? n : -n;

        let digits = 1 + Math.floor(Math.log10(abs_n));

        const rounded = parseFloat(
            (digits >= rounding) ?
                `${abs_n}`.substring(0, rounding).padEnd(digits, "0")
                : `${abs_n}`.substring(0, rounding + ((digits < 1) ? 2 : 1)));

        return (n > 0) ? rounded : -rounded;
    };

    if (pick_colour.colours === undefined)
        pick_colour.colours = ['#2c7bb6', '#abd9e9', '#e4efaf', '#fdae61', '#d7191c'];

    let scales = [];
    if (id !== "" && pick_colour.scale_cache[id] !== undefined) {
        scales = pick_colour.scale_cache[id];
    } else {
        if (method.clusters) {
            const data = set.map(accessor);
            scales = d3
                .scaleCluster()
                .domain(data)
                .range(pick_colour.colours)
                .clusters()
                .map(round);
        } else if (method.deciles) {
            const data = set.map(accessor).sort((a, b) => a - b);
            scales = [0.05, 0.3, 0.7, 0.95]
                .map(level => round(d3.quantileSorted(data, level)));
        }
        pick_colour.scale_cache[id] = scales;
    }

    const x = accessor(f);
    for (let i = 0; i < scales.length; i++) {
        if (x < scales[i]) {
            return pick_colour.colours[i];
        }
    }
    return pick_colour.colours[pick_colour.colours.length - 1];
}