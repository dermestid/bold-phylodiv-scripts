import pick_colour from "./pick_colour.js";
import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";

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
        .attr("class", f => `highlightable key_${f.key}`)
        .attr("id", f => `pd_${f.key}`)
        .attr("fill", f => pick_colour(f, pd_fc, x => x.properties.pd))
        .attr("d", path)
        .on("mouseover", highlight)
        .on("mouseleave", highlight_off);
    return continuation(pd_fc);
}