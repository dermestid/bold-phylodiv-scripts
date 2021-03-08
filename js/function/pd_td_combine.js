export default function pd_td_combine(pd_data, td_data) {

    const pd_td_data = td_data.map(d => {
        for (const pd_datum of pd_data)
            if (d.key === pd_datum.key) {
                // Deep copy of TD and PD properties, at least to properties first level (shallow copy of object properties)
                const combined = {
                    type: "Feature",
                    key: d.key,
                    properties: {},
                    geometry: d.geometry
                };
                for (const p in d.properties)
                    combined.properties[p] = d.properties[p];
                for (const p in pd_datum.properties)
                    combined.properties[p] = pd_datum.properties[p];
                return combined;
            }
    });
    return pd_td_data;
}