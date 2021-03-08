export default function collect_data() {
    const points = d3.select("#points")
        .selectAll("circle");
    if (points.empty()) return "";

    let output = "";

    const sep = ",";
    const line = "\n";

    const first = JSON.parse(points.text());
    for (const p in first) output += (p + sep);
    output += line;

    points.each(d => {
        const data = JSON.parse(d.text);
        for (const p in data) output += (data[p] + sep);
        output += line;
    });

    return output;
}