<?php

// generator which outputs multiple simple random samples of size $k
// from a iterator $gen which yields [$data, $pop_id]
// where $pop_id is an object/array with field "key"
// where $key = $pop_id["key"] is used to sort $data
// into different populations which will be sampled independently.
//
// Yields null for each member of $gen, while it calculates samples,
// then it yields each of the sample sets as 
// $key => [$pop_id, [data_1, ..., data_k]]
//
// Data belonging to populations smaller than k will not be yielded.

function sample_sequences_g($gen, $k) {
    $reservoirs = [];
    $i = []; // population size seen so far for each different key
    foreach ($gen as [$data, $pop_id]) {

        $key = $pop_id['key'];
        if (!isset($reservoirs[$key])) {
            $reservoirs[$key] = [$pop_id, []];
            $i[$key] = 0;
        }

        if ($i[$key] < $k) {
            // fill the reservoir
            $reservoirs[$key][1][] = $data;
        } else {
            // replace a random member of the reservoir
            // with chance decreasing as $i increases
            $j = rand(0, $i[$key]);
            if ($j < $k) $reservoirs[$key][1][$j] = $data;
        }
        $i[$key]++;
        yield;
    }

    foreach ($reservoirs as $key => $sample) {
        if ($i[$key] >= $k) {
            // if reservoir is full
            yield $key => $sample;
        }
    }
}

?>
