<?php

// given an array (and possibly a callback), 
// write the sequences from the array to a new file and return its name.

function write_temp_fasta(array $sequences, $callback = null) {

    do {
        $temp_file = uniqid('temp').'.fas';
    } while (file_exists($temp_file));

    $handle = fopen($temp_file, 'w');

    foreach ($sequences as $entry) {
        if ($callback === null) $seq = $entry;
        else $seq = $callback($entry);

        $header = '>'.uniqid();
        fwrite($handle, $header.PHP_EOL);
        fwrite($handle, $seq.PHP_EOL);
    }
    fclose($handle);
    
    return $temp_file;
}

?>
