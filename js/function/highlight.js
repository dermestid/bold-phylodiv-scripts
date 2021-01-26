import highlight_off from "./highlight_off.js"

export default function highlight() {

    const classes = d3.select(this).attr("class").split(" ");

    highlight_off();

    for (const c of classes)
        if (c.indexOf("key") === 0)
            d3.selectAll(`.${c}`)
                .raise()
                .transition()
                .duration(100)
                .attr("r", 7)
                .attr("stroke-width", 1)
                .style("stroke", "black");
}