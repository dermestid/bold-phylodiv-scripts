import highlight_off from "./highlight_off.js"

export default function highlight() {

    const classes = d3.select(this).attr("class").split(" ");

    highlight_off();

    for (const c of classes) {
        const s = c.replace(/\./g, "\\.");

        if (s.indexOf("group") === 0) {

            const data_type = 'pd'; // Change this when there's a form to alter whether PD or TD is on map

            const data_id = s.substring(6);
            const group = d3.selectAll(`.${data_id}`);
            group.raise();
            group.selectAll(`circle.highlightable.${s}`)
                .transition()
                .duration(200)
                .attr("r", 4);

            d3.selectAll(".data")
                .attr("visibility", "hidden");
            d3.selectAll(".legend")
                .attr("visibility", "hidden");
            // .selectAll("g")
            // .attr("visibility", "hidden");
            d3.selectAll(`g.${data_type}.${data_id}`)
                .attr("visibility", "visible");
            // .selectAll("g")
            // .attr("visibility", "visible");

        } else if (s.indexOf("key") === 0) {
            d3.selectAll(`.highlightable.${s}`)
                .raise()
                .attr("stroke-width", 1)
                .attr("stroke", "black");
            d3.selectAll(`.ci.highlightable.${s}`)
                .attr("stroke-width", "2px");

            const circle = d3.select(`circle.highlightable.${s}`);
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
}