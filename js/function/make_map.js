import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";
import pick_colour from "./pick_colour.js";

export default function make_map() {

    const width = 800;
    const height = 390;

    d3.select("body")
        .selectAll(".map")
        .remove();
    const svg = d3
        .select("body")
        .append("svg")
        .attr("class", "map")
        .attr("width", width)
        .attr("height", height);

    const projection = d3
        .geoEqualEarth()
        // .geoCylindricalEqualArea()
        // .parallel(37)
        .translate([width / 2, height / 2])
        .scale([144])
        .rotate([-10, 0]);
    const path = d3
        .geoPath()
        .projection(projection);

    svg.append("path")
        .attr("id", "outline")
        .attr("d", path({ type: "Sphere" }))
        .attr("fill", "none")
        .attr("stroke", "black");

    $.getJSON("countries-50m.json", world => {
        svg.insert("g", "#outline")
            .attr("id", "land")
            .selectAll("path")
            .data(topojson.feature(world, world.objects.land).features)
            .enter()
            .append("path")
            .attr("d", path)
            .attr("fill", "lightgray");
        svg.insert("g", "#outline")
            .attr("id", "borders")
            .append("path")
            .datum(topojson.mesh(world, world.objects.countries))
            .attr("fill", "none")
            .attr("stroke", "white")
            .attr("stroke-opacity", 0.7)
            .attr("stroke-linejoin", "round")
            .attr("d", path)
            .attr("pointer-events", "none");

        svg.insert("g", "#borders")
            .attr("id", "data");
    });

    svg.append("g")
        .attr("id", "legend");
    const legend_container = svg.select("g#legend");

    svg.data = (data_id, data_type, dataset, key_acc, matcher, acc, text_fn = d => `${data_type}=${acc(d)}`) => {
        const id_escaped = data_id.replace(/\./g, "\\.");
        let group = svg.select(`g.data.${data_type}.${id_escaped}`);
        if (group.empty())
            group = svg.select("#data")
                .append("g")
                .attr("class", `data ${data_type} ${data_id}`);
        group.attr("visibility", "hidden")
            .selectAll("path")
            .data(dataset, matcher)
            .join(
                enter => enter
                    .append("path")
                    .attr("class", d => `highlightable group_${data_id} key_${key_acc(d)}`)
                    .attr("id", d => `${data_type}_${key_acc(d)}`)
                    .attr("fill", d => pick_colour(d, dataset, acc, data_id, 2))
                    .attr("d", path)
                    .text(text_fn)
                    .on("mouseover", highlight)
                    .on("mouseleave", highlight_off),
                update => update
                    .text(text_fn)
                    .attr("fill", d => pick_colour(d, dataset, acc, data_id, 2))
            );
    };

    svg.show = (data_type, data_id) => {
        const id_escaped = data_id.replace(/\./g, "\\.");
        svg.selectAll(".data")
            .attr("visibility", "hidden");
        svg.select(`g.data.${data_type}.${id_escaped}`)
            .attr("visibility", "visible");
    };

    const rect_size = 20
    const legend_offset_x = 45;
    const legend_offset_y = 250

    svg.legend = (data_type, data_id) => {
        legend_container.select("g#legend")
            .selectAll("g")
            .attr("visibility", "hidden");
        let legend_group = legend_container
            .selectAll(`g.${data_type}.${data_id}`);
        if (legend_group.empty()) {
            legend_group = legend_container
                .append("g")
                .attr("class", `legend ${data_type} ${data_id}`)
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
                        .text((c, i) => pick_colour.interval(data_id, i))
                        .attr("text-anchor", "left")
                        .style("alignment-baseline", "middle");
                    return g;
                },
                update => update
                    .select("text")
                    .text((c, i) => pick_colour.interval(data_id, i))
            );
    }

    return svg;
}

