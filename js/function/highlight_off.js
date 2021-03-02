export default function highlight_off() {
    d3.selectAll(".highlightable")
        .attr("stroke", "none");
    d3.selectAll(".ci.highlightable")
        .attr("stroke", "gray")
        .attr("stroke-width", 0.5);
    d3.selectAll(`circle.highlightable`)
        .transition()
        .duration(200)
        .attr("r", 3);
    d3.select(".tooltip")
        .style("opacity", 0);
}