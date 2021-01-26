import pick_colour from "./pick_colour.js";
import highlight from "./highlight.js";
import highlight_off from "./highlight_off.js";

export default function make_plot(xs, ys, x_acc, y_acc, col_fc, col_acc) {

    if (xs.length > ys.length) throw "different lengths";

    const x_ = i => x_acc(xs[i]);
    ys.search = x => {
        for (const y of ys)
            if (y.key === x.key) return y;
        throw "not found";
    };
    const y_ = i => y_acc(ys.search(xs[i]));

    const wb = 460;
    const hb = 400;
    const margin = { top: 10, right: 30, bottom: 30, left: 60 };
    const round = v => {
        const o = 10 ** Math.floor(Math.log10(v));
        return o * Math.ceil(v / o);
    };
    const min = (s, ax) => {
        const m = d3.min(s, ax);
        return (m >= 0) ? 0 : -(round(-m));
    };

    const xlim = [
        min(xs, x_acc),
        round(d3.max(xs, x_acc))
    ];
    const ylim = [
        min(ys, y_acc),
        round(d3.max(ys, y_acc))
    ];
    const width = wb - (margin.left + margin.right);
    const height = hb - (margin.top + margin.bottom);

    d3.select("body")
        .selectAll("#plot")
        .remove();
    let svg = d3
        .select("body")
        .append("svg")
        .attr("id", "plot")
        .attr("width", wb)
        .attr("height", hb)
        .append("g")
        .attr("id", "plot_area")
        .attr("transform", `translate(${margin.left}, ${margin.top})`);

    // add axes
    let x = d3
        .scaleLinear()
        .domain(xlim)
        .range([0, width]);
    svg.append("g")
        .attr("id", "x_axis")
        .attr("transform", `translate(0, ${height})`)
        .call(d3.axisBottom(x));
    let y = d3
        .scaleLinear()
        .domain(ylim)
        .range([height, 0]);
    svg.append("g")
        .attr("id", "y_axis")
        .call(d3.axisLeft(y));

    // add data points
    svg.append("g")
        .attr("id", "points")
        .selectAll("dot")
        .data(Array.from({ length: xs.length }, (_, i) => i))
        .enter()
        .append("circle")
        .attr("class", i => `highlightable key_${xs[i].key}`)
        .attr("cx", i => x(x_(i)))
        .attr("cy", i => y(y_(i)))
        .attr("id", i => `point_${xs[i].key}`)
        .attr("r", 4)
        .style("fill", i => pick_colour(
            col_fc.features[i],
            col_fc,
            col_acc))
        .on("mouseover", highlight)
        .on("mouseleave", highlight_off);
    svg.append("g")
        .attr("id", "top_points")
}