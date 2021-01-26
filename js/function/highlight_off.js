export default function highlight_off() {
    d3.selectAll(".highlightable")
        .transition()
        .duration(200)
        .attr("r", 4)
        .style("stroke", "none");
}