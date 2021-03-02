import page_setup_input from "./function/page_setup_input.js";
import source_choice from "./function/source_choice.js";
import make_map from "./function/make_map.js";
import make_plot from "./function/make_plot.js";
import get_pd from "./function/get_pd.js";
import add_pd_data from "./function/add_pd_data.js";
import get_gbif_diversity from "./function/get_gbif_diversity.js";
import get_locations_str from "./function/get_locations_str.js";
import add_td_data from "./function/add_td_data.js";
import label_points from "./function/label_points.js";

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

    const iterations_rt = 3;

    document.getElementById("get_pd").onsubmit = () => {
        const dl = (document.getElementById("download_sequences_choice").checked);
        const tax = dl ? $("#taxon").val() : $("#saved_taxa").val();
        const lon_grid = $("#lon_grid").val();
        const scheme_key_prefix = `EQUAL-AREA_${lon_grid}`;
        const subs = $("#subs").val();

        const map = make_map();
        const plot = make_plot();

        for (let i = 0; i < iterations_rt; i++) {
            for (let j = 0; j < iterations_rt; j++) {
                const fraction_lat = i / iterations_rt;
                const fraction_lon = j / iterations_rt;
                const fraction = (i * iterations_rt + j) / (iterations_rt * iterations_rt);
                const scheme_key = `${scheme_key_prefix}+${fraction_lat}+${fraction_lon}`;
                const colour = d3.interpolateSpectral(fraction);
                get_pd(dl, tax, scheme_key, subs, pd_data => {
                    const id = add_pd_data(pd_data, map, plot);
                    plot.set_points_colour(id, colour);
                    get_gbif_diversity(
                        tax,
                        scheme_key,
                        get_locations_str(pd_data),
                        td_datum => add_td_data([td_datum], id, map, plot),
                        td_data => label_points(id, pd_data, td_data, map, plot)
                    );
                });
            }
        }
        return false;
    };

    source_choice();

    $.getJSON(check_executables_script, function (data) {
        if (data.result === true) {
            page_setup_input();
        }
    });
});
