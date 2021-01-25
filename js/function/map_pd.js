import pick_colour from "./pick_colour.js";

export default function map_pd(pd_fc, svg, path, continuation) {
    svg.selectAll(".data")
        .remove();
    svg.insert("g", "#borders")
        .attr("id", "pd")
        .attr("class", "data")
        .selectAll("path")
        .data(pd_fc.features)
        .enter()
        .append("path")
        .attr("fill", f => pick_colour(f, pd_fc, x => x.properties.pd))
        .attr("d", path);
    return continuation(pd_fc);
}