export default function add_td_data(data, id, map, plot) {

    // Update dataset
    if (add_td_data.cache === undefined)
        add_td_data.cache = {};
    if (add_td_data.cache[id] == undefined)
        add_td_data.cache[id] = data;
    else {
        for (const datum of data) {
            if (datum == undefined) { continue; }
            let found = false;
            for (const cached of add_td_data.cache[id])
                if (cached.key === datum.key) { found = true; break; }

            if (found === false) add_td_data.cache[id].push(datum);
        }
    }

    map.data(
        id,
        "td",
        add_td_data.cache[id],
        d => d.key,
        d => d ? d.key : this.id.substring(3),
        d => d.properties.td);

    // Update plot
    plot.set_x(id, add_td_data.cache[id], d => d.properties.td);

    return add_td_data.cache[id];
}
