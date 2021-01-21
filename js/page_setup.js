import page_setup_input from "./function/page_setup_input.js";
import page_setup_plot from "./function/page_setup_plot.js";
import source_choice from "./function/source_choice.js";
import make_map from "./function/make_map.js";
import get_pd from "./function/get_pd.js";
import map_pd from "./function/map_pd.js";
import get_locations_str from "./function/get_locations_str.js";
import get_gbif_diversity from "./function/get_gbif_diversity.js";
import map_diversity from "./function/map_diversity.js";

$(document).ready(function () {
    var check_executables_script = "script/check_executables.php";

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

    var map = make_map("map");

    document.getElementById("get_pd").onsubmit = function () {
        var dl = (document.getElementById("download_sequences_choice").checked);
        var tax = dl ? $("#taxon").val() : $("#saved_taxa").val();
        var lat_grid = $("#lat_grid").val();
        var lon_grid = $("#lon_grid").val();
        var scheme_key = `COORD-GRID_${lat_grid}x${lon_grid}`;
        var subs = $("#subs").val();

        get_pd(dl, tax, scheme_key, subs, function (pd_data) {
            map_pd(pd_data, map);
            page_setup_plot();
            var loc_str = get_locations_str(pd_data);
            get_gbif_diversity(tax, scheme_key, loc_str, function (td_data) {
                map_diversity(td_data, map);
            });
        });
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
