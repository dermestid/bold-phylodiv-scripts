<?php

function mean_sd_ci(array $datum, array &$dataset, string $field) {

    $key = $datum['location']['key'];
    $x = $datum[$field];
    if (!array_key_exists($key, $dataset))
        $dataset[$key] = [ 
            'values' => [], 
            'ci' => ['upper' => $x, 'lower' => $x], 
            'bounds' => ['upper' => $x, 'lower' => $x], 
            'sum' => 0
        ];
    $dataset[$key]['values'][] = $x;
    $sum = ($dataset[$key]['sum'] += $x);

    // Update CI to the second-highest and second-lowest values
    if ($x < $dataset[$key]['bounds']['lower']) {
        $dataset[$key]['ci']['lower'] = $dataset[$key]['bounds']['lower'];
        $dataset[$key]['bounds']['lower'] = $x;
    } elseif ($x > $dataset[$key]['bounds']['upper']) {
        $dataset[$key]['ci']['upper'] = $dataset[$key]['bounds']['upper'];
        $dataset[$key]['bounds']['upper'] = $x;
    } elseif ($x < $dataset[$key]['ci']['lower']) {
        $dataset[$key]['ci']['lower'] = $x;
    } elseif ($x > $dataset[$key]['ci']['upper']) {
        $dataset[$key]['ci']['upper'] = $x;
    }

    $count = count($dataset[$key]['values']);
    $mean = $sum / $count;
    $sd = sqrt(array_sum(array_map(
        fn($x) => ($x - $mean) ** 2,
        $dataset[$key]['values']
    )));
    $ci = $dataset[$key]['ci'];

    return [$mean, $sd, $ci];
}

?>
