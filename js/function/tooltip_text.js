export default function tooltip_text(json) {
    // Tightly coupled to summary_json() in label_points() in label_points.js

    const data = JSON.parse(json);
    const text =
        `PD=${data.pd} (${data.pd_quantile}/${data.quantiles})
    <br> TD=${data.td} (${data.td_quantile}/${data.quantiles})`;
    return text;
}