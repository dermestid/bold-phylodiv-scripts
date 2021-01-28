export default function page_setup_input() {
    const get_saved_taxa_script = "script/saved_taxa.php";
    const saved_taxa_select = $("#saved_taxa");
    const saved_taxa_option = function (entry) {
        return `<option value="${entry}">${entry}</option>`;
    };

    $.getJSON(get_saved_taxa_script, function (taxa) {
        taxa.forEach(function (taxon) {
            saved_taxa_select.append(saved_taxa_option(taxon));
        });
    });
}