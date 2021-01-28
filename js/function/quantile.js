export default function quantile(input, set, quantiles, acc = i => i) {
    const x = acc(input);
    const data = set.map(acc).sort((a, b) => a - b);

    let levels = [];
    for (let i = 0; i < quantiles; i++)
        levels[i] = d3.quantileSorted(
            data,
            (i + 1) / quantiles);

    let i = 0;
    for (const level of levels) {
        if (x <= level) return i;
        i++;
    }
}