export default function source_choice() {
    if (document.getElementById("saved_sequences_choice").checked) {
        $("#saved_sequences_select").show();
        $("#download_sequences_form").hide();
        $("#saved_data").prop("disabled", false);
    } else {
        $("#saved_sequences_select").hide();
        $("#download_sequences_form").show();
        $("#saved_data").prop("disabled", true);
    }
}