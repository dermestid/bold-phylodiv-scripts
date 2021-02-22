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
    });

    svg.data = (id, class_name, dataset, key_acc, matcher, acc, text_fn = d => `${class_name}=${acc(d)}`) => {
        let group = svg.selectAll(`g.${class_name}`);
        if (group.empty())
            group = svg.insert("g", "#borders")
                .attr("class", `data ${class_name}`)
                .attr("id", id);
        group.attr("visibility", "hidden")
            .selectAll("path")
            .data(dataset, matcher)
            .join(
                enter => enter
                    .append("path")
                    .attr("class", d => `highlightable key_${key_acc(d)}`)
                    .attr("id", d => `${class_name}_${key_acc(d)}`)
                    .attr("fill", d => pick_colour(d, dataset, acc, id, 2))
                    .attr("d", path)
                    .text(text_fn)
                    .on("mouseover", highlight)
                    .on("mouseleave", highlight_off),
                update => update
                    .text(text_fn)
                    .attr("fill", d => pick_colour(d, dataset, acc, id, 2))
            );
    };

    svg.show = class_name => {
        svg.selectAll(".data")
            .attr("visibility", "hidden");
        svg.select(`g.data.${class_name}`)
            .attr("visibility", "visible");
    };

    const rect_size = 20
    const legend_offset_x = 45;
    const legend_offset_y = 250

    svg.legend = (class_name, id) => {
        svg.selectAll(".legend")
            .attr("visibility", "hidden");
        let legend_group = svg.selectAll(`g.${class_name}_legend`);
        if (legend_group.empty()) {
            legend_group = svg.append("g")
                .attr("class", `legend ${class_name}_legend`)
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
    }

    return svg;
}

