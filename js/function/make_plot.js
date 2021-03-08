import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";
import bar_path from "./bar_path.js";

export default function make_plot(lims = { x: [0, 100], y: [0, 1] }) {

    const wb = 600;
    const hb = 350;
    const margin = { top: 10, right: 30, bottom: 30, left: 60 };

    const width = wb - (margin.left + margin.right);
    const height = hb - (margin.top + margin.bottom);

    const round = x => {
        const o = 10 ** Math.floor(Math.log10(x));
        const r = o * Math.ceil(x / o);
        if (0.75 * r > x) return 0.75 * r;
        else return r;
    };
    const min = (s, acc) => {
        const m = d3.min(s, acc);
        return (m >= 0) ? 0 : -(round(-m));
    };

    d3.select("body")
        .selectAll(".plot")
        .remove();
    let div = d3
        .select("body")
        .append("div")
        .attr("class", "plot")
        .style("position", "relative");
    let svg = div
        .append("svg")
        .attr("class", "plot")
        .attr("width", wb)
        .attr("height", hb)
        .append("g")
        .attr("id", "plot_area")
        .attr("transform", `translate(${margin.left}, ${margin.top})`);

    svg.max_x = 0;
    svg.max_y = 0;
    svg.limits = (s, acc, keep_outliers = true) => {
        const min_val = min(s, acc);
        const max_val = d3.max(s, acc);
        if (s.length <= 2 || keep_outliers) return [min_val, round(max_val)];
        let without_max = [...s];
        without_max.splice(d3.greatestIndex(s, acc), 1);
        const second_biggest = d3.max(without_max, acc);
        const max = (second_biggest * 1.5 < max_val) ? second_biggest : max_val;
        return [min_val, round(max)];
    };

    div.append("div")
        .attr("class", "tooltip")
        .style("background-color", "white")
        .style("border", "solid")
        .style("border-width", "1px")
        .style("border-radius", "5px")
        .style("padding", "10px")
        .style("position", "absolute")
        .style("left", "50px")
        .style("top", "50px")
        .style("pointer-events", "none")
        .style("opacity", 0);

    // add axes
    svg.xax = d3
        .scaleLinear()
        .domain(lims.x)
        .range([0, width]);
    svg.append("g")
        .attr("id", "x_axis")
        .attr("transform", `translate(0, ${height})`)
        .call(d3.axisBottom(svg.xax));
    svg.yax = d3
        .scaleLinear()
        .domain(lims.y)
        .range([height, 0]);
    svg.append("g")
        .attr("id", "y_axis")
        .call(d3.axisLeft(svg.yax));

    // add data containers
    svg.append("g")
        .attr("id", "points");
    svg.append("g")
        .attr("id", "line");

    svg.set_x = (data_id, xs, acc) => {
        // update axis
        const [x_min, x_max] = svg.limits(xs, acc);
        svg.max_x = Math.max(x_max, svg.max_x);
        const yax = svg.yax;
        const xax = svg.xax = d3
            .scaleLinear()
            .domain([x_min, svg.max_x])
            .range([0, width]);
        svg.select("#x_axis")
            .call(d3.axisBottom(xax));

        // get container
        let group = svg.select("g#points")
            .select(`g.points.${data_id}`);
        if (group.empty())
            group = svg.select("g#points")
                .append("g")
                .attr("class", `points ${data_id}`);

        // update data points
        group.selectAll("circle")
            .data(xs, d => d ? d.key : this.id.substring(6))
            .join(
                enter => enter
                    .append("circle")
                    .attr("class", x => `highlightable group_${data_id} key_${x.key}`)
                    .attr("id", x => `point_${x.key}`)
                    .attr("cx", x => xax(acc(x)))
                    .attr("cy", y => yax(0))
                    .on("mouseover", highlight)
                    .on("mouseleave", highlight_off)
                    .attr("r", 3),
                update => update
                    .attr("cx", x => xax(acc(x))),
                exit => exit);
        return svg;
    };

    svg.set_y = (data_id, ys, acc) => {
        // update axis
        const [y_min, y_max] = svg.limits(ys, acc);
        svg.max_y = Math.max(y_max, svg.max_y);
        const xax = svg.xax;
        const yax = svg.yax = d3
            .scaleLinear()
            .domain([y_min, svg.max_y])
            .range([height, 0]);
        svg.select("#y_axis")
            .call(d3.axisLeft(yax));

        // get container
        let group = svg.select("g#points")
            .select(`g.points.${data_id}`);
        if (group.empty())
            group = svg.select("g#points")
                .append("g")
                .attr("class", `points ${data_id}`);

        // update data points
        group.selectAll("circle")
            .data(ys, d => d ? d.key : this.id.substring(6))
            .join(
                enter => enter
                    .append("circle")
                    .attr("class", y => `highlightable group_${data_id} key_${y.key}`)
                    .attr("id", y => `point_${y.key}`)
                    .attr("cx", x => xax(0))
                    .attr("cy", y => yax(acc(y)))
                    .on("mouseover", highlight)
                    .on("mouseleave", highlight_off)
                    .attr("r", 3)
                    .style("fill", "lightgray"),
                update => update
                    .attr("cy", y => yax(acc(y))),
                exit => exit);
        return svg;
    };

    svg.set_error_bars = (data_id, dataset, ci_acc_lower, ci_acc_upper, x_acc) => {
        const xax = svg.xax;
        const yax = svg.yax;

        // get container (at bottom of points container)
        let group = svg.select("g#points")
            .select(`g.points.${data_id}`)
            .select("g.error_bars");
        if (group.empty())
            group = svg.select("g#points")
                .select(`g.points.${data_id}`)
                .insert("g", "circle")
                .attr("class", "error_bars");

        // update error bars
        group.selectAll("path.ci")
            .data(dataset, d => d ? d.key : this.id.substring(3))
            .join(
                enter => enter
                    .append("path")
                    .attr("class", "ci")
                    .attr("id", d => `ci_${d.key}`)
                    .attr("stroke", "gray")
                    .attr("stroke-width", 0.5)
                    .attr("fill", "none")
                    .attr("pointer-events", "none")
                    .attr("d", d => bar_path(
                        xax(x_acc(d)),
                        yax(ci_acc_lower(d)),
                        yax(ci_acc_upper(d))
                    )),
                update => update
                    .attr("d", d => bar_path(
                        xax(x_acc(d)),
                        yax(ci_acc_lower(d)),
                        yax(ci_acc_upper(d))
                    ))
            );
        return svg;
    };

    svg.set_points_colour = (data_id, colour) =>
        svg.select("g#points")
            .select(`g.points.${data_id}`)
            .selectAll("circle")
            .style("fill", colour);

    svg.set_text = (data_id, text_set, acc, matcher) => {
        let exit = svg.select("g#points")
            .select(`g.points.${data_id}`)
            .selectAll("circle")
            .data(text_set, matcher)
            .text(acc)
            .exit();
        exit.each(() => console.log(`removal of node in ${data_id}`));
        return exit.remove();
    };

    svg.draw_line = (slope, intercept, x_min, x_max) =>
        svg.select("g#line")
            .selectAll("line")
            .data([arguments])
            .join(
                enter => enter
                    .append("line")
                    .attr("stroke", "darkslategray")
                    .attr("stroke-dasharray", "4")
                    .attr("stroke-width", 2)
                    .attr("pointer-events", "none")
                    .attr("x1", svg.xax(x_min))
                    .attr("y1", svg.yax(x_min * slope + intercept))
                    .attr("x2", svg.xax(x_max))
                    .attr("y2", svg.yax(x_max * slope + intercept)),
                update => update
                    .call(
                        update => update
                            .transition(
                                svg.transition()
                                    .duration(500))
                            .attr("x1", svg.xax(x_min))
                            .attr("y1", svg.yax(x_min * slope + intercept))
                            .attr("x2", svg.xax(x_max))
                            .attr("y2", svg.yax(x_max * slope + intercept))));


    return svg;
}
