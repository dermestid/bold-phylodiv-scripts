function get_tree_lengths() {
    var main_script = 'phylodiv.php';

    var args = {
        taxon: $("#taxon").val(),
        subs: $("#subs").val(),
        lat_grid: $("#lat_grid").val(),
        lon_grid: $("#lon_grid").val(),
    };

    $.getJSON(main_script, args, respond);
}

function respond(data) {
    if (data.status === "FAIL") {
        $("#result").text(`Download of taxon ${$("#taxon").val()} failed.`);
    } else if (data.status == "WORKING") {
        if (data.next != "") {
            $("#result").text(`Working: ${data.message}`);
            $.getJSON(data.next, data.next_args, respond);
        } else {
            // ???
            $("#result").text("Error: I don't know what to do next");
        }
    } else if (data.status === "DONE") {
        if (data.next != "") {
            $("#result").text(`Working: ${data.message}`);
            $.getJSON(data.next, data.next_args, respond);
        } else {
            handle_results(data);
        }
    } else {
        // error
        // $("#result").append(JSON.stringify(data));
    }
}

function handle_results(data) {
    // Make a table of results
    $("#result").html("<table id=\"result_table\"></table>");
    $("#result_table").prepend("<tr id=\"result_table_head\"></tr>");
    Object.keys(data.result[0]).forEach(function (item) {
        $("#result_table_head").append(`<th>${item}</th>`);
    });
    data.result.forEach(function (item, index) {
        $("#result_table").append(`<tr id="${index}"></tr>`);
        Object.keys(item).forEach(function (field) {
            $(`#${index}`).append(`<td>${item[field]}</td>`);
        });
    });
}