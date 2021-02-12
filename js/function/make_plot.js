import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";
import bar_path from "./bar_path.js";
import pick_colour from "./pick_colour.js";

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
    const limits = (s, acc, keep_outliers = true) => {
        const min_val = min(s, acc);
        const max_val = d3.max(s, acc);
        if (s.length <= 2 || keep_outliers) return [min_val, round(max_val)];
        let without_max = [...s];
        without_max.splice(d3.greatestIndex(s, acc), 1);
        const second_biggest = d3.max(without_max, acc);
        const max = (second_biggest * 1.5 < max_val) ? second_biggest : max_val;
        return [min_val, round(max)];
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
    const xax = d3
        .scaleLinear()
        .domain(lims.x)
        .range([0, width]);
    svg.append("g")
        .attr("id", "x_axis")
        .attr("transform", `translate(0, ${height})`)
        .call(d3.axisBottom(xax));
    const yax = d3
        .scaleLinear()
        .domain(lims.y)
        .range([height, 0]);
    svg.append("g")
        .attr("id", "y_axis")
        .call(d3.axisLeft(yax));

    // add data containers
    svg.append("g")
        .attr("id", "error_bars");
    svg.append("g")
        .attr("id", "points");
    svg.append("g")
        .attr("id", "line");

    svg.set_x = (xs, acc) => {
        // update axis
        const xax = svg.xax = d3
            .scaleLinear()
            .domain(limits(xs, acc, false))
            .range([0, width]);
        svg.select("#x_axis")
            .call(d3.axisBottom(xax));
        // update data points
        svg.select("g#points")
            .selectAll("circle")
            .data(xs, d => d ? d.key : this.id.substring(6))
            .join(
                enter => enter
                    .append("circle")
                    .attr("class", x => `highlightable key_${x.key}`)
                    .attr("id", x => `point_${x.key}`)
                    .attr("cx", x => xax(acc(x)))
                    .attr("cy", y => yax(0))
                    .on("mouseover", highlight)
                    .on("mouseleave", highlight_off)
                    .attr("r", 4),
                update => {
                    svg.select("g#error_bars")
                        .select(`path#ci_${update.key}`)
                        .attr("transform", x => `translate(${xax(acc(x))}, 0)`);
                    return update.attr("cx", x => xax(acc(x)));
                },
                exit => exit);
        return svg;
    };

    svg.set_y = (ys, acc, write_new, ci_acc) => {
        const error_bars = !write_new;
        // update axis
        const yax = svg.yax = d3
            .scaleLinear()
            .domain(limits(ys, acc))
            .range([height, 0]);
        svg.select("#y_axis")
            .call(d3.axisLeft(yax));
        // update data points
        svg.select("g#points")
            .selectAll("circle")
            .data(ys, d => d ? d.key : this.id.substring(6))
            .join(
                enter => write_new ?
                    enter.append("circle")
                        .attr("class", y => `highlightable key_${y.key}`)
                        .attr("id", y => `point_${y.key}`)
                        .attr("cx", x => xax(0))
                        .attr("cy", y => yax(acc(y)))
                        .on("mouseover", highlight)
                        .on("mouseleave", highlight_off)
                        .attr("r", 4)
                        .style("fill", "lightgray")
                    : enter,
                update => update
                    .attr("cy", y => yax(acc(y))));
        if (error_bars)
            svg.select("g#error_bars")
                .selectAll("path.ci")
                .data(ys, d => d ? d.key : this.id.substring(3))
                .join(
                    enter => enter
                        .append("path")
                        .attr("class", y => `ci highlightable key_${y.key}`)
                        .attr("id", y => `ci_${y.key}`)
                        .attr("stroke", "gray")
                        .attr("stroke-width", 0.5)
                        .attr("fill", "none")
                        .attr("pointer-events", "none")
                        .attr("d", y => bar_path(
                            ci_acc(y),
                            parseFloat(svg.select(`#point_${y.key}`).attr("cx")),
                            acc(y),
                            yax
                        )),
                    update => update
                        .attr("d", y => bar_path(
                            ci_acc(y),
                            parseFloat(svg.select(`#point_${y.key}`).attr("cx")),
                            acc(y),
                            yax
                        )));
        return svg;
    };

    svg.set_colours = (col_set, acc, matcher) =>
        svg.select("g#points")
            .selectAll("circle")
            .data(col_set, matcher)
            .style("fill", c => pick_colour(
                c,
                col_set,
                acc));

    svg.set_text = (text_set, acc, matcher) =>
        svg.select("g#points")
            .selectAll("circle")
            .data(text_set, matcher)
            .text(acc);

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
