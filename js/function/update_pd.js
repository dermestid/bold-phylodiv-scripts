import pick_colour from "./pick_colour.js";
import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";

export default function update_pd(pd_data, map, path, plot, new_data) {
    const id = (Date.now() / 1000).toString(16).split(".").join("");

    // Map data
    map.selectAll(".data")
        .attr("visibility", "hidden");
    let group = map.selectAll("g.pd");
    if (group.empty())
        group = map.insert("g", "#borders")
            .attr("class", "data pd")
            .attr("id", id);
    group
        .attr("visibility", "visible")
        .selectAll("path")
        .data(pd_data, d => d ? d.key : this.id.substring(3))
        .enter()
        .append("path")
        .attr("class", f => `highlightable key_${f.key}`)
        .attr("id", f => `pd_${f.key}`)
        .attr("fill", f => pick_colour(f, pd_data, d => d.properties.pd))
        .attr("d", path)
        .text(f => `PD=${f.properties.pd}`)
        .on("mouseover", highlight)
        .on("mouseleave", highlight_off);

    // Update plot
    plot.set_y(pd_data, d => d.properties.pd, new_data, d => d.properties.pd_ci);

    return id;
}