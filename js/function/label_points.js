import coord_rect_area from "./coord_rect_area.js";
import quantile from "./quantile.js";

export default function label_points(id, pd_td_data, pd_acc, td_acc, map, plot, se_acc, ci_acc) {

    const pds_sorted = pd_td_data.map(pd_acc).sort((a, b) => a - b);
    const tds_sorted = pd_td_data.map(td_acc).sort((a, b) => a - b);

    const quantiles = 10;
    const q_diff = d => {
        const a = quantile(pd_acc(d), pds_sorted, quantiles);
        const b = quantile(td_acc(d), tds_sorted, quantiles);
        return { pd: a, td: b, diff: (a - b) };
    };

    const pd_count_acc = d => d.properties.pd_observations;
    const td_count_acc = d => d.properties.td_observations;
    const mean_lat_acc = d => d.properties.mean_coord.lat;
    const mean_lon_acc = d => d.properties.mean_coord.lon;

    const summary_json = (d, q) => {
        let summary = {
            id: id,
            pd: pd_acc(d),
            pd_count: pd_count_acc(d),
            td: td_acc(d),
            td_count: td_count_acc(d),
            pd_quantile: q.pd + 1,
            td_quantile: q.td + 1,
            quantiles: quantiles,
            difference: q.diff,
            mean_lat: mean_lat_acc(d),
            mean_lon: mean_lon_acc(d)
        };
        if (se_acc !== undefined)
            summary.pd_se = se_acc(d);
        if (ci_acc !== undefined) {
            summary.pd_ci_lower = ci_acc(d)[0];
            summary.pd_ci_upper = ci_acc(d)[1];
        }
        const geo = coord_rect_area(d.geometry.coordinates);
        for (const p in geo) summary[p] = geo[p];
        return JSON.stringify(summary);
    };

    const quantile_data = pd_td_data.map(d => {
        const q = q_diff(d);
        const datum = {
            type: "Feature",
            key: d.key,
            properties: {
                pd: pd_acc(d),
                td: td_acc(d),
                pd_quantile: q.pd + 1,
                td_quantile: q.td + 1,
                difference: q.diff,
            },
            text: summary_json(d, q),
            geometry: d.geometry
        };
        return datum;
    });

    const diff = f => f.properties.difference;

    map.data(
        id,
        "diff",
        quantile_data,
        d => d.key,
        d => d ? d.key : this.id.substring(5),
        diff,
        d => d.text);

    plot.set_text(id, quantile_data, f => f.text, d => d ? d.key : this.id.substring(5));
}