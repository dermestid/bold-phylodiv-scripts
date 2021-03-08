export default function get_pd(dl, tax, scheme_key, subs, continuation, loc_str = "") {

    const pd_script = "script/get_pd.php";

    const args = new URLSearchParams({
        do_download: dl,
        taxon: tax,
        subset: (loc_str !== ""),
        division_scheme_key: scheme_key,
        subsample_size: subs,
        locations: loc_str
    });

    let source = new SSE(pd_script, { payload: args });

    source.addEventListener("working", event => {
        const time = (new Date()).toLocaleTimeString();
        const data = JSON.parse(event.data);
        let report = `${time}: Current task: ${data.task}. `;
        if (data.task == "sampling") report += `Sequences read: ${data.sequences}`;
        report += "<br>";
        // $("#result_container").prepend(report);
    });

    const fail_handler = event => {
        source.removeEventListener("fail", fail_handler);
        source.close();
        const time = (new Date()).toLocaleTimeString();
        let report = `${time}: Get PD: Request failed. `;
        console.log(event.data);
        if (event.data == "timeout") report += "Took too long.";
        report += "<br>";
        $("#result_container").prepend(report);
    };

    source.addEventListener("fail", fail_handler);

    source.addEventListener("done", event => {
        source.removeEventListener("fail", fail_handler);
        source.close();

        const time = (new Date()).toLocaleTimeString();
        let report = `${time}: `;
        report += "Received PD data. <br>";
        $("#result_container").prepend(report);

        if (event.data != "") {
            const data = JSON.parse(event.data);
            continuation(data);
        }
    });

    source.stream();
}