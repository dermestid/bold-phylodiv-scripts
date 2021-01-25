export default function quantile(x, fc, quantiles, accessor, rounding = 0) {
    const round = x => (rounding === 0) ? x : Math.floor(x / rounding) * rounding;

    // cache the dataset quantiles
    if (quantile.levels === undefined)
        quantile.levels = {};
    if (quantile.levels[fc.id] == null)
        quantile.levels[fc.id] = [];
    if (quantile.levels[fc.id].length === 0) {
        const data = fc.features.map(accessor).sort((a, b) => a - b);
        let prev = 0;
        for (let i = 0; i < quantiles; i++) {
            quantile.levels[fc.id][i] = round(d3.quantileSorted(
                data,
                (i + 1) / quantiles));
            if ((i > 0) && (prev >= quantile.levels[fc.id][i]))
                quantile.levels[fc.id][i] = prev + rounding;
            prev = quantile.levels[fc.id][i];
        }
    }

    let i = 0;
    for (const level of quantile.levels[fc.id]) {
        if (x <= level) return i;
        i++;
    }
}