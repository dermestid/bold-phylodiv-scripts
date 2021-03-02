import quantile from "./quantile.js";

export default function label_points(id, pd_data, td_data, map, plot) {

    const td = d => d.properties.td;
    const pd = d => {
        for (const pd_d of pd_data)
            if (pd_d.key === d.key)
                return pd_d.properties.pd;
    };

    const pds_sorted = pd_data.map(d => d.properties.pd).sort((a, b) => a - b);
    const tds_sorted = td_data.map(td).sort((a, b) => a - b);

    const quantiles = 10;
    const q_diff = d => {
        const a = quantile(pd(d), pds_sorted, quantiles);
        const b = quantile(td(d), tds_sorted, quantiles);
        return { pd: a, td: b, diff: (a - b) };
    };

    const quantile_data = td_data.map(d => {
        const q = q_diff(d);
        const datum = {
            type: "Feature",
            key: d.key,
            properties: {
                pd: pd(d),
                td: td(d),
                pd_quantile: q.pd + 1,
                td_quantile: q.td + 1,
                difference: q.diff,
            },
            text: `PD=${pd(d)} (${q.pd + 1}/${quantiles})<br>TD=${td(d)} (${q.td + 1}/${quantiles})`,
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