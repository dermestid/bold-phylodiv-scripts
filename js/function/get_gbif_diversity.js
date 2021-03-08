export default function get_gbif_diversity(tax, scheme, loc_str, c_update, c_complete) {
    if (get_gbif_diversity.cache === undefined)
        get_gbif_diversity.cache = {};
    if (get_gbif_diversity.cache[`${tax} ${scheme} ${loc_str}`] != null)
        return c_complete(get_gbif_diversity.cache[`${tax} ${scheme} ${loc_str}`]);

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
        console.log(event.data);
        if (event.data == "timeout") report += "Took too long. ";
        else if (event.data == "incorrect args") report += "Incorrect args were given. ";
        else if (event.data == "incorrect division scheme key") report += "Could not parse scheme key. ";
        report += "<br>";
        $("#result_container").prepend(report);
    };

    source.addEventListener("fail", fail_handler);

    source.addEventListener("done", event => {
        const time = (new Date()).toLocaleTimeString();
        let report = `${time}: `;
        if (event.data == 0) {
            source.removeEventListener("fail", fail_handler);
            source.close();
            report += "GBIF data complete. <br>";
            $("#result_container").prepend(report);
            c_complete(c_update());
        } else {
            report += "Updated GBIF data. <br>";
            // $("#result_container").prepend(report);
            const data = JSON.parse(event.data);
            get_gbif_diversity.cache[`${tax} ${loc_str}`] = data;
            c_update(data);
        }
    });

    source.stream();
}