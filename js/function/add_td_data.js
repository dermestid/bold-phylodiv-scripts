export default function add_td_data(data, id, map, plot) {

    // Update dataset
    if (add_td_data.cache === undefined)
        add_td_data.cache = {};
    if (add_td_data.cache[id] === undefined)
        add_td_data.cache[id] = data;
    else {
        for (const datum of data) {
            let found = false;
            for (const cached of add_td_data.cache[id])
                if (cached.key === datum.key) found = true;
            if (!found)
                add_td_data.cache[id].push(datum);
        }
    }

    map.data(
        id,
        "td",
        add_td_data.cache[id],
        d => d.key,
        d => d ? d.key : this.id.substring(3),
        d => d.properties.diversity);

    // Update plot
    plot.set_x(add_td_data.cache[id], d => d.properties.diversity);
}