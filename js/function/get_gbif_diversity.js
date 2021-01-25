export default function get_gbif_diversity(tax, scheme, loc_str, continuation) {
    if (get_gbif_diversity.cache === undefined)
        get_gbif_diversity.cache = {};
    if (get_gbif_diversity.cache[`${tax} ${loc_str}`] != null)
        return continuation(get_gbif_diversity.cache[`${tax} ${loc_str}`]);

    const gbif_diversity_script = "script/get_gbif_diversity.php";
    const args = new URLSearchParams({
        taxon: tax,
        subset: true,
        division_scheme_key: scheme,
        locations: loc_str
    });

    let source = new SSE(gbif_diversity_script, { payload: args });

    const fail_handler = event => {
        source.removeEventListener("fail", fail_handler);
        source.close();
        const time = (new Date()).toLocaleTimeString();
        let report = `${time}: Get TD: Request failed. `;
        if (event.data == "timeout") report += "Took too long. ";
        else if (event.data == "incorrect args") report += "Incorrect args were given. ";
        else if (event.data == "incorrect division scheme key") report += "Could not parse scheme key. ";
        report += "<br>";
        $("#result_container").prepend(report);
    };

    source.addEventListener("fail", fail_handler);

    source.addEventListener("done", event => {
        source.removeEventListener("fail", fail_handler);
        source.close();
        const time = (new Date()).toLocaleTimeString();
        const report = `${time}: Done! <br>`;
        $("#result_container").prepend(report);
        const data = JSON.parse(event.data);
        get_gbif_diversity.cache[`${tax} ${loc_str}`] = data;
        continuation(data);
    });

    source.stream();
}