export default function get_pd(dl, tax, scheme_key, subs, co_1, co_2) {

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
        const time = (new Date()).toLocaleTimeString();
        let report = `${time}: `;
        if (event.data == 0) {
            source.removeEventListener("fail", fail_handler);
            source.close();
            report += "PD data complete. <br>";
            $("#result_container").prepend(report);
        } else {
            report += "Updated PD data. <br>";
            $("#result_container").prepend(report);
            // console.log(event.data);
            const data = JSON.parse(event.data);
            if (data[0].properties.iteration === 0) co_1(data);
            else co_2(data);
        }
    });

    source.stream();
}