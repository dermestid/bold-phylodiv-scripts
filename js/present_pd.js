function present_pd() {

    var args = {
        taxon: $("#taxon").val(),
        subs: $("#subs").val(),
        lat_grid: $("#lat_grid").val(),
        lon_grid: $("#lon_grid").val(),
    };

    get_tree_lengths(args, function (data) {
        draw_pd_map(data.result);
    });
}