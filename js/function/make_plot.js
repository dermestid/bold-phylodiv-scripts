import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";
import bar_path from "./bar_path.js";
import pick_colour from "./pick_colour.js";

export default function make_plot(lims = { x: [0, 100], y: [0, 1] }) {

    const wb = 400;
    const hb = 350;
    const margin = { top: 10, right: 30, bottom: 30, left: 60 };

    const width = wb - (margin.left + margin.right);
    const height = hb - (margin.top + margin.bottom);

    const round = v => {
        const o = 10 ** Math.floor(Math.log10(v));
        return o * Math.ceil(v / o);
    };
    const min = (s, acc) => {
        const m = d3.min(s, acc);
        return (m >= 0) ? 0 : -(round(-m));
    };
    const limits = (s, acc) => [min(s, acc), round(d3.max(s, acc))];

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

    // add data point containers
    svg.append("g")
        .attr("id", "error_bars");
    svg.append("g")
        .attr("id", "points");

    svg.set_x = (xs, acc) => {
        // update axis
        const xax = d3
            .scaleLinear()
            .domain(limits(xs, acc))
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
        const yax = d3
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
        const enter_bars = d => d
            .append("path")
            .attr("class", y => `ci highlightable key_${y.key}`)
            .attr("id", y => `ci_${y.key}`)
            .attr("stroke", "gray")
            .attr("stroke-width", 0.5)
            .attr("fill", "none")
            .attr("pointer-events", "none")
            .attr("d", y => bar_path(
                ci_acc(y),
                parseFloat(d3.select(`#point_${y.key}`).attr("cx")),
                acc(y),
                yax
            ));
        if (error_bars)
            svg.select("g#error_bars")
                .selectAll("path.ci")
                .data(ys, d => d ? d.key : this.id.substring(3))
                .join(
                    enter_bars,
                    update => update
                        .attr("d", y => bar_path(
                            ci_acc(y),
                            parseFloat(d3.select(`#point_${y.key}`).attr("cx")),
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

    return svg;
}