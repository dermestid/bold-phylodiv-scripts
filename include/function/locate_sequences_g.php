<?php

// generator which transforms a given generator adding the location
//
// expects an interator $gen which first yields an indexed array $header, 
// then yields objects or associative arrays $entry,
// with each $entry having $entry["fields"] a numeric array (not necessarily indexed)
// which is suitable for $scheme->locate() and keys are as in $header
//
// yields array_flip($header) and then yields [$entry, location]

function locate_sequences_g($gen, Division_Scheme $scheme) {
    // Take the first yield of $gen and don't rewind prior to the foreach
    $it = new NoRewindIterator($gen);
    $cols = array_flip($it->current());
    yield $cols;
    $it->next();

    foreach ($it as $entry) {
        $loc = $scheme->locate($entry['fields'], $cols);
        yield [$entry, $loc];
    }
}

?>
