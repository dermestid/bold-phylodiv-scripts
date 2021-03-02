<?php

require_once '../include/config/global.php'; // $CLI

// given an array (and possibly an accessor), 
// write the sequences from the array to a new file and return its name.

function write_temp_fasta(array $sequences, $accessor = null) {
    global $CLI;
    
    $rand_prefix = $CLI ? 'temp' : hash(
        'crc32b', 
        $_SERVER['REMOTE_ADDR'].'_'.$_SERVER['REQUEST_TIME_FLOAT'].'_'.$_SERVER['REMOTE_PORT']);

    do {
        $temp_file = 'temp/'.uniqid($rand_prefix).'.fas';
    } while (file_exists($temp_file));

    $handle = fopen($temp_file, 'w');

    foreach ($sequences as $entry) {
        if ($accessor === null) $seq = $entry;
        else $seq = $accessor($entry);

        $header = '>'.uniqid();
        fwrite($handle, $header.PHP_EOL);
        fwrite($handle, $seq.PHP_EOL);
    }
    fclose($handle);
    
    return $temp_file;
}

?>
