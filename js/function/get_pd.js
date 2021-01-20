export default function get_pd(dl, tax, scheme_key, subs, continuation) {
    if (get_pd.cache === undefined)
        get_pd.cache = {};
    if (get_pd.cache[`${tax} ${scheme_key} ${subs}`] != null)
        return continuation(get_pd.cache[`${tax} ${scheme_key} ${subs}`]);

    var pd_script = "script/get_pd.php";

    var args = {
        do_download: dl,
        taxon: tax,
        division_scheme_key: scheme_key,
        subsample_size: subs
    };
    var query_string = new URLSearchParams(args);
    var url = `${pd_script}?${query_string}`;

    let source = new EventSource(url);

    source.addEventListener("working", event => {
        var time = (new Date()).toLocaleTimeString();
        var data = JSON.parse(event.data);
        var report = `${time}: Current task: ${data.task}. `;
        if (data.task == "sampling") report += `Sequences read: ${data.sequences}`;
        report += "<br>";
        $("#result_container").prepend(report);
    });

    source.addEventListener("fail", event => {
        var time = (new Date()).toLocaleTimeString();
        var report = `${time}: Request failed. `;
        if (event.data == "timeout") report += "Took too long.";
        report += "<br>";
        $("#result_container").prepend(report);
        source.close();
    });

    source.addEventListener("done", event => {
        var time = (new Date()).toLocaleTimeString();
        var report = `${time}: Done! <br>`;
        $("#result_container").prepend(report);
        source.close();
        var data = JSON.parse(event.data);
        get_pd.cache[`${tax} ${scheme_key} ${subs}`] = data;
        continuation(data);
    });
}