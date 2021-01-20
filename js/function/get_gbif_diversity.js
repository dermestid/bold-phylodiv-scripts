export default function get_gbif_diversity(tax, scheme, continuation) {
    if (get_gbif_diversity.cache === undefined)
        get_gbif_diversity.cache = {};
    if (get_gbif_diversity.cache[`${tax} ${scheme}`] != null)
        return continuation(get_gbif_diversity.cache[`${tax} ${scheme}`]);

    var gbif_diversity_script = "script/get_gbif_diversity.php";
    var args = {
        taxon: tax,
        division_scheme_key: scheme
    };
    var query_string = new URLSearchParams(args);
    var url = `${gbif_diversity_script}?${query_string}`;

    let source = new EventSource(url);

    source.addEventListener("fail", event => {
        alert("fail!");
        var time = (new Date()).toLocaleTimeString();
        var report = `${time}: Request failed. `;
        if (event.data == "timeout") report += "Took too long.";
        report += "<br>";
        $("#result_container").prepend(report);
        source.close();
    });

    source.addEventListener("done", event => {
        alert("done!");
        var time = (new Date()).toLocaleTimeString();
        var report = `${time}: Done! <br>`;
        $("#result_container").prepend(report);
        source.close();
        var data = JSON.parse(event.data);
        get_gbif_diversity.cache[`${tax} ${scheme}`] = data;
        continuation(data);
    });
}