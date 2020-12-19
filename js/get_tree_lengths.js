function get_tree_lengths(args, on_success) {
    var main_script = 'phylodiv.php';

    $.getJSON(main_script, args, respond_fn(on_success));
}

function respond_fn(on_success) {
    return function (data) { respond(data, on_success); }
}

function respond(data, on_success) {
    var responder = respond_fn(on_success);

    if (data.status === "FAIL") {
        $("#result").text(`Download of taxon ${$("#taxon").val()} failed.`);
    } else if (data.status == "WORKING") {
        if (data.next != "") {
            $("#result").text(`Working: ${data.message}`);
            $.getJSON(data.next, data.next_args, responder);
        } else {
            $("#result").text("Error: I don't know what to do next");
        }
    } else if (data.status === "DONE") {
        if (data.next != "") {
            $("#result").text(`Working: ${data.message}`);
            $.getJSON(data.next, data.next_args, responder);
        } else {
            on_success(data);
        }
    }
}