export default function add_pd_data(pd_data, map, plot) {
    const id = "i" + (Date.now() / 1000).toString(16).split(".").join("");

    map.data(
        id,
        "pd",
        pd_data,
        d => d.key,
        d => d ? d.key : this.id.substring(3),
        d => d.properties.pd);
    map.show("pd", id);

    map.legend("pd", id);

    // Update plot
    plot.set_y(id, pd_data, d => d.properties.pd);

    return id;
}
