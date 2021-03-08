import label_points from "./label_points.js";

export default function update_pd_data(pd_td_data, id, map, plot) {
    map.data(
        id,
        "pd",
        pd_td_data,
        d => d.key,
        d => d ? d.key : this.id.substring(3),
        d => d.properties.pd_mean);
    map.show("pd", id);

    map.legend("pd", id);

    // Update plot
    plot.set_y(id, pd_td_data, d => d.properties.pd_mean);
    plot.set_error_bars(
        id,
        pd_td_data,
        d => d.properties.pd_ci_lower,
        d => d.properties.pd_ci_upper,
        d => d.properties.td
    );
    label_points(
        id,
        pd_td_data,
        d => d.properties.pd_mean,
        d => d.properties.td,
        map,
        plot,
        d => d.properties.pd_se,
        d => [d.properties.pd_ci_lower, d.properties.pd_ci_upper]
    );
}
