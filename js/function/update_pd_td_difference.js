import quantile from "./quantile.js";
import regression_line from "./regression_line.js";

export default function update_pd_td_difference(map, plot) {
    // This function requires both PD and (a subset of) TD to be retrieved.
    // Since these are obtained by processes effectively acting in parallel,
    // there isn't a suitable context where both TD and PD exist together.
    // To solve this issue, this function isn't passed the TD and PD data,
    // but instead retrieves it from what's already plotted on the map.

    const td_data = map
        .select("g.td")
        .selectChildren()
        .data();

    const td = d => d.properties.diversity;

    // Get PD from map squares with IDs matching the TD nodes
    const pd = d => map
        .select("g.pd")
        .select(
            `#pd_${d.key
                .replace(/\./g, "\\.")
            }`)
        .datum()
        .properties.pd;

    const id = map
        .select("g.pd")
        .attr("id");

    const quantiles = 10;
    const q_diff = d => {
        const a = quantile(d, td_data, quantiles, pd);
        const b = quantile(d, td_data, quantiles, td);
        return [a, b, a - b];
    };

    let diff_features = td_data
        .map(d => {
            const qd = q_diff(d);
            const f = {
                type: "Feature",
                key: d.key,
                properties: {
                    pd: pd(d),
                    td: td(d),
                    pd_quantile: qd[0] + 1,
                    td_quantile: qd[1] + 1,
                    difference: qd[2],
                },
                text: `PD=${pd(d)} (${qd[0] + 1}/${quantiles})<br>TD=${td(d)} (${qd[1] + 1}/${quantiles})`,
                geometry: d.geometry
            };
            return f;
        });

    const diff = f => f.properties.difference;

    map.data(
        `diff_${id}`,
        "diff",
        diff_features,
        d => d.key,
        d => d ? d.key : this.id.substring(5),
        diff,
        d => d.text);

    plot.set_colours(diff_features, diff, d => d ? d.key : this.id.substring(5));
    plot.set_text(diff_features, f => f.text, d => d ? d.key : this.id.substring(5));

    // The third argument means "ignore data points for which TD is in the top quantile"
    // This is intended to get rid of outliers.
    // This may not always be appropriate, so in future make this a changeable option.
    const [slope, intercept, r_squared] = regression_line(
        diff_features,
        f => f.properties.td,
        f => f.properties.pd,
        f => (f.properties.td_quantile === quantiles)
    );
    plot.draw_line(slope, intercept, d3.min(td_data, td), d3.max(td_data, td));
}