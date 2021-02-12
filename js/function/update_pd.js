export default function update_pd(pd_data, map, plot, new_data) {
    const id = (Date.now() / 1000).toString(16).split(".").join("");

    map.data(
        id,
        "pd",
        pd_data,
        d => d.key,
        d => d ? d.key : this.id.substring(3),
        d => d.properties.pd);
    map.show("pd");

    map.legend("pd", id);

    // Update plot
    plot.set_y(pd_data, d => d.properties.pd, new_data, d => d.properties.pd_ci);

    return id;
}