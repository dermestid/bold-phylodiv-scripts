export default function page_setup_input() {
    const plot_div = $("#plot");
    const input_div = $("#input");
    const get_saved_taxa_script = "script/saved_taxa.php";
    const saved_taxa_select = $("#saved_taxa");
    const saved_taxa_option = function (entry) {
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