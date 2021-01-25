import page_setup_input from "./function/page_setup_input.js";
import page_setup_plot from "./function/page_setup_plot.js";
import source_choice from "./function/source_choice.js";
import make_map from "./function/make_map.js";
import get_pd from "./function/get_pd.js";
import map_pd from "./function/map_pd.js";
import get_locations_str from "./function/get_locations_str.js";
import get_gbif_diversity from "./function/get_gbif_diversity.js";
import map_difference from "./function/map_difference.js";
import make_plot from "./function/make_plot.js";

$(document).ready(function () {
    const check_executables_script = "script/check_executables.php";

    document.getElementsByName("sequence_source").forEach(function (elt) {
        elt.onchange = function () { source_choice(); };
    });

    document.getElementsByName("sample_type").forEach(function (elt) {
        elt.onchange = function () {
            if (document.getElementById("rarefy_choice").checked) {
                $("#subs").prop("disabled", true);
            } else {
                $("#subs").prop("disabled", false);
            }
        };
    });

    const [svg, path] = make_map();

    document.getElementById("get_pd").onsubmit = function () {
        const dl = (document.getElementById("download_sequences_choice").checked);
        const tax = dl ? $("#taxon").val() : $("#saved_taxa").val();
        const lat_grid = $("#lat_grid").val();
        const lon_grid = $("#lon_grid").val();
        const scheme_key = `COORD-GRID_${lat_grid}x${lon_grid}`;
        const subs = $("#subs").val();

        get_pd(dl, tax, scheme_key, subs, pd_data =>
            map_pd({
                type: "FeatureCollection",
                id: (Date.now() / 1000).toString(16).split(".").join(""),
                features: pd_data
            }, svg, path, pd_fc =>
                get_gbif_diversity(tax, scheme_key, get_locations_str(pd_data), td_data => {
                    const diff = map_difference(pd_fc, {
                        type: "FeatureCollection",
                        id: (Date.now() / 1000).toString(16).split(".").join(""),
                        features: td_data
                    }, x => x.properties.pd, y => y.properties.diversity, svg, path);
                    make_plot(
                        td_data,
                        pd_data,
                        x => x.properties.diversity,
                        y => y.properties.pd,
                        diff,
                        f => f.properties.difference);
                })));
        return false;
    };

    source_choice();

    $.getJSON(check_executables_script, function (data) {
        if (data.result === true) {
            page_setup_input();
        } else if (data.result === false) {
            page_setup_plot();
        }
    });
});
