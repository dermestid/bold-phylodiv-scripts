export default function highlight_off() {
    d3.selectAll(".highlightable")
        .style("stroke", "none");
    d3.selectAll(".ci.highlightable")
        .style("stroke", "gray")
        .style("stroke-width", 0.5);
    d3.selectAll(`circle.highlightable`)
        .transition()
        .duration(200)
        .attr("r", 4);
    d3.select(".tooltip")
        .style("opacity", 0);
}