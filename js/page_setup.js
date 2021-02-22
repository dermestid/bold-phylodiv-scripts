import page_setup_input from "./function/page_setup_input.js";
import source_choice from "./function/source_choice.js";
import make_map from "./function/make_map.js";
import make_plot from "./function/make_plot.js";
import get_pd from "./function/get_pd.js";
import update_pd from "./function/update_pd.js";
import get_gbif_diversity from "./function/get_gbif_diversity.js";
import get_locations_str from "./function/get_locations_str.js";
import add_td_data from "./function/add_td_data.js";
import update_pd_td_difference from "./function/update_pd_td_difference.js";

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

    document.getElementById("get_pd").onsubmit = () => {
        const dl = (document.getElementById("download_sequences_choice").checked);
        const tax = dl ? $("#taxon").val() : $("#saved_taxa").val();
        const lon_grid = $("#lon_grid").val();
        const scheme_key = `EQUAL-AREA_${lon_grid}`;
        const subs = $("#subs").val();

        const map = make_map();
        const plot = make_plot();

        get_pd(dl, tax, scheme_key, subs, pd_data => {
            const id = update_pd(pd_data, map, plot, true);
            get_gbif_diversity(tax, scheme_key, get_locations_str(pd_data), td_datum => {
                add_td_data([td_datum], `td_${id}`, map, plot);
                update_pd_td_difference(map, plot);
            });
        }, pd_data => {
            update_pd(pd_data, map, plot, false);
            update_pd_td_difference(map, plot);
        });
        return false;
    };

    source_choice();

    $.getJSON(check_executables_script, function (data) {
        if (data.result === true) {
            page_setup_input();
        }
    });
});
