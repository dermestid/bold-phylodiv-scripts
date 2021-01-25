export default function make_map() {

    const width = 800;
    const height = 400;
    const svg = d3.select("body").append("svg")
        .attr("width", width)
        .attr("height", height);

    const projection = d3.geoEqualEarth()
        .translate([width / 2, height / 2])
        .scale([144])
        .rotate([-10, 0]);

    const path = d3.geoPath().projection(projection);

    svg.append("path")
        .attr("id", "outline")
        .attr("d", path({ type: "Sphere" }))
        .attr("fill", "none")
        .attr("stroke", "black");

    $.getJSON("countries-50m.json", world => {
        svg.insert("g", "#outline")
            .attr("id", "map")
            .selectAll('path')
            .data(topojson.feature(world, world.objects.land).features)
            .enter()
            .append('path')
            .attr("d", path)
            .attr("fill", "lightgray");
        svg.insert("g", "#outline")
            .attr("id", "borders")
            .append("path")
            .datum(topojson.mesh(world, world.objects.countries))
            .attr("fill", "none")
            .attr("stroke", "white")
            .attr("stroke-opacity", 0.7)
            .attr("stroke-linejoin", "round")
            .attr("d", path);
    });

    return [svg, path];
}

