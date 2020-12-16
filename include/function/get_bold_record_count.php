<?php

function get_bold_record_count(string $taxon) {

    $BOLD_URL_PREFIX = 'http://www.boldsystems.org/index.php/API_Public/stats';
    $BOLD_STATS_FORMAT = 'json';
    $TOTAL_RECORDS = 'total_records';

    $bold_query = $BOLD_URL_PREFIX . '?'
        . 'format=' . $BOLD_STATS_FORMAT
        . '&taxon=' . $taxon;
    
    $bold_response = json_decode(file_get_contents($bold_query), true);
    return intval($bold_response[$TOTAL_RECORDS]);
}

?>
