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
        .attr("fill", f => pick_colour(f, pd_data, d => d.properties.pd, id, 2))
        .attr("d", path)
        .text(f => `PD=${f.properties.pd}`)
        .on("mouseover", highlight)
        .on("mouseleave", highlight_off);

    const rect_size = 20
    const legend_offset_x = 45;
    const legend_offset_y = 250
    map.selectAll(".legend")
        .attr("visibility", "hidden");
    let legend_group = map.selectAll("g.pd_legend");
    if (legend_group.empty()) {
        legend_group = map.append("g")
            .attr("class", "legend pd_legend")
            .style("outline", "solid black");
        legend_group.append("rect")
            .attr("x", legend_offset_x)
            .attr("y", legend_offset_y)
            .attr("fill", "white")
            .attr("width", 100)
            .attr("height", (rect_size + 5) * pick_colour.colours.length);
    }

    legend_group
        .attr("visibility", "visible")
        .selectAll("g")
        .data(pick_colour.colours, (d, i) => d ? i : this.id.substring(7))
        .join(
            enter => {
                const g = enter.append("g")
                    .attr("id", (c, i) => `legend_${i}`);
                g.append("rect")
                    .attr("width", rect_size)
                    .attr("height", rect_size)
                    .attr("x", legend_offset_x)
                    .attr("y", (c, i) => legend_offset_y + i * (rect_size + 5))
                    .attr("fill", c => c);
                g.append("text")
                    .attr("id", (c, i) => `legend_text_${i}`)
                    .attr("x", legend_offset_x + rect_size * 1.2)
                    .attr("y", (c, i) => 5 + legend_offset_y + i * (rect_size + 5) + rect_size / 2)
                    .text((c, i) => pick_colour.interval(id, i))
                    .attr("text-anchor", "left")
                    .style("alignment-baseline", "middle");
                return g;
            },
            update => update
                .select("text")
                .text((c, i) => pick_colour.interval(id, i))
        );

    // Update plot
    plot.set_y(pd_data, d => d.properties.pd, new_data, d => d.properties.pd_ci);

    return id;
}