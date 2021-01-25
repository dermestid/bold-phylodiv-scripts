export default function get_pd(dl, tax, scheme_key, subs, continuation) {
    if (get_pd.cache === undefined)
        get_pd.cache = {};
    if (get_pd.cache[`${tax} ${scheme_key} ${subs}`] != null)
        return continuation(get_pd.cache[`${tax} ${scheme_key} ${subs}`]);

    const pd_script = "script/get_pd.php";

    const args = {
        do_download: dl,
        taxon: tax,
        division_scheme_key: scheme_key,
        subsample_size: subs
    };
    const query_string = new URLSearchParams(args);
    const url = `${pd_script}?${query_string}`;

    let source = new SSE(url);

    source.addEventListener("working", event => {
        const time = (new Date()).toLocaleTimeString();
        const data = JSON.parse(event.data);
        let report = `${time}: Current task: ${data.task}. `;
        if (data.task == "sampling") report += `Sequences read: ${data.sequences}`;
        report += "<br>";
        $("#result_container").prepend(report);
    });

    const fail_handler = event => {
        source.removeEventListener("fail", fail_handler);
        source.close();
        const time = (new Date()).toLocaleTimeString();
        let report = `${time}: Get PD: Request failed. `;
        if (event.data == "timeout") report += "Took too long.";
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
        get_pd.cache[`${tax} ${scheme_key} ${subs}`] = data;
        continuation(data);
    });

    source.stream();
}