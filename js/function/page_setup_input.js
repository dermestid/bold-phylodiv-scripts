export default function page_setup_input() {
    var plot_div = $("#plot");
    var input_div = $("#input");
    var get_saved_taxa_script = "script/saved_taxa.php";
    var saved_taxa_select = $("#saved_taxa");
    var saved_taxa_option = function (entry) {
        return `<option value="${entry}">${entry}</option>`;
    };

    plot_div.hide();
    input_div.show();

    $.getJSON(get_saved_taxa_script, function (taxa) {
        taxa.forEach(function (taxon) {
            saved_taxa_select.append(saved_taxa_option(taxon));
        });
    });
}