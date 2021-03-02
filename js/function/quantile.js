export default function quantile(x, data, quantiles) {

    for (let i = 0; i < quantiles; i++) {
        const level = d3.quantileSorted(
            data,
            (i + 1) / quantiles);
        if (x <= level) return i;
    }
}