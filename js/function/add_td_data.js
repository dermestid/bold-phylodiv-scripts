import pick_colour from "./pick_colour.js";
import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";

export default function add_td_data(data, id, map, path, plot) {

    // Update dataset
    if (add_td_data.cache === undefined)
        add_td_data.cache = {};
    if (add_td_data.cache[id] === undefined)
        add_td_data.cache[id] = data;
    else {
        for (const datum of data) {
            let found = false;
            for (const cached of add_td_data.cache[id])
                if (cached.key === datum.key) found = true;
            if (!found)
                add_td_data.cache[id].push(datum);
        }
    }

    // Map data
    map.selectAll(".data")
        .attr("visibility", "hidden");
    let group = map.selectAll("g.td");
    if (group.empty())
        group = map.insert("g", "#borders")
            .attr("class", "data td")
            .attr("id", id);
    group
        .attr("visibility", "visible")
        .selectAll("path")
        .data(
            add_td_data.cache[id],
            d => d ? d.key : this.id.substring(3)
        ).enter()
        .append("path")
        .attr("class", f => `highlightable key_${f.key}`)
        .attr("id", f => `td_${f.key}`)
        .attr("fill", f => pick_colour(f, data, d => d.properties.diversity, id, 2))
        .attr("d", path)
        .text(f => `TD=${f.properties.diversity}`)
        .on("mouseover", highlight)
        .on("mouseleave", highlight_off);

    // Update plot
    plot.set_x(add_td_data.cache[id], d => d.properties.diversity);
}