import highlight_off from "./highlight_off.js"

export default function highlight() {

    const classes = d3.select(this).attr("class").split(" ");

    highlight_off();

    for (const c of classes)
        if (c.indexOf("key") === 0) {
            d3.selectAll(`.highlightable.${c}`)
                .raise()
                .attr("stroke-width", 1)
                .attr("stroke", "black");
            d3.selectAll(`.ci.highlightable.${c}`)
                .attr("stroke-width", "2px");

            const circle = d3.select(`circle.highlightable.${c}`);
            circle.transition()
                .duration(200)
                .attr("r", 7);

            const tx = 75 + parseInt(circle.attr("cx"));
            const ty = 30 + parseInt(circle.attr("cy"));
            d3.select(".tooltip")
                .style("opacity", 1)
                .style("left", `${tx}px`)
                .style("top", `${ty}px`)
                .raise()
                .html(circle.text());
        }

}