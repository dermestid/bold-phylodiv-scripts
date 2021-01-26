import pick_colour from "./pick_colour.js";
import quantile from "./quantile.js";
import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";

export default function map_difference(fc_1, fc_2, acc_1, acc_2, svg, path) {
    const n_colours = pick_colour.colours.length;

    // Get subset of fc_1 that occur in fc_2, in the same order as in fc_2
    let subset = [];
    for (const g of fc_2.features)
        for (const f of fc_1.features)
            if (g.key === f.key) { subset.push(f); break; }
    if (subset.length !== fc_2.features.length) throw "fc_1 not superset of fc_2";

    const q_diff = (f, i) =>
        quantile(acc_1(f), fc_1, n_colours, acc_1)
        - quantile(acc_2(fc_2.features[i]), fc_2, n_colours, acc_2);

    const fc_diff = {
        type: "FeatureCollection",
        key: `${fc_1.key}-${fc_2.key}`,
        features: subset.map((f, i) => ({
            type: "Feature",
            key: f.key,
            properties: { difference: q_diff(f, i) },
            geometry: f.geometry
        }))
    };

    const diff = f => f.properties.difference;

    const max = d3.max(fc_diff.features, f => Math.abs(diff(f)));

    svg.selectAll(".data")
        .remove();
    svg.insert("g", "#borders")
        .attr("id", "diff")
        .attr("class", "data")
        .selectAll("path")
        .data(fc_diff.features)
        .enter()
        .append("path")
        .attr("class", f => `highlightable key_${f.key}`)
        .attr("id", f => `diff_${f.key}`)
        .attr("fill", f =>
            pick_colour(f, fc_diff, diff))
        .attr("fill-opacity", f =>
            0.5 * (1 + Math.abs(diff(f)) / max))
        .attr("d", path)
        .on("mouseover", highlight)
        .on("mouseleave", highlight_off);

    return [fc_diff, subset];
}