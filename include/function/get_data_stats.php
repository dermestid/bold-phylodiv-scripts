<?php

require_once '../include/function/mean_sd_ci.php';

function get_data_stats(array &$data, array &$dataset, int $n, string $field_key, string $ci_key) {

    $data['iteration'] = $n;
    [ $mean, $sd, $ci ] = mean_sd_ci($data, $dataset, $field_key);
    $data[$field_key] = $mean;
    if ($n >= 3) {
        $ci_width = ($n - 1) / ($n + 1);
        $data[$ci_key] = [
            'interval' => true, 
            'upper' => $ci['upper'], 
            'lower' => $ci['lower'],
            'width' => $ci_width
        ];
    } else
        $data[$ci_key] = [
            'interval' => false, 
            'sd' => $sd
        ];
}

?>
