<?php

// generator which calculates pd for samples returned from an iterator $gen
//
// expects $gen to yield either null or [$location, $data]
// where $location is some object/array of data common to the sample (e.g., the location)
// and $data is an array of items (object/array) with fields:
// "sequence": nucleotide sequence in FASTA format from which to calculate pd
//
// yields null for each null value of $gen, otherwise:
// yields [done, task, data] where:
// done is false while still waiting on completion of task, otherwise true
// task is a string describing the current task (e.g. "alignment"), or "" if done
// data is associative array [$location, pd] if done
// note: done is yielded multiple times.

require_once '../include/function/write_temp_fasta.php';
require_once '../include/class/alignment.php';
require_once '../include/function/get_tree_lengths_g.php';
require_once '../include/function/both.php';

function get_sample_pd_g($gen) {

    // yield null until all the samples have been collected
    // launch processes for alignment of samples
    $alignments = [];
    $locations = [];
    foreach ($gen as $key => $entry) {
        if ($entry === null) { yield; continue; }

        $temp_file = write_temp_fasta(
            $entry[1], 
            fn($data) => $data['sequence']
        );    
        $locations[$key] = $entry[0];
        $alignments[$key] = new Alignment($temp_file); // Alignment class manages a process
        // pause to update calling context on progress
        yield [false, 'alignment', []];
    }

    // build nexus string of aligned samples as they complete alignment
    $nexus_string = '#NEXUS'.PHP_EOL;
    $file_offset = strlen($nexus_string);
    do {
        $alignment_in_progress = false;
        foreach ($alignments as $key => $aln) {
            // pause to update calling context on progress
            yield [false, 'alignment', []];

            [$status, $file] = $aln->get();

            if ($status === Alignment::STATUS_WORKING) {
                $alignment_in_progress = true;
                continue 2; // continues do-while loop, resetting foreach
                // note: since we skip back to the beginning of foreach,
                // this means that alignments are written to the string in
                // the original order as in $gen.
            } else if ($status === Alignment::STATUS_DONE) {
                $nexus_string .= file_get_contents($file, 0, NULL, $file_offset);

                unlink($file);
                unlink(str_replace('nxs', 'dnd', $file));
                unlink($aln->get_input_file());
                
                unset($alignments[$key]);
            } else {
                // alignment failed
                unset($alignments[$key]);
                unset($locations[$key]);
            }
        }
        usleep(10000); // loop each 0.01s
    } while ($alignment_in_progress);

    // calculate pd
    $pd_gen = get_tree_lengths_g($nexus_string);
    foreach (both($locations, $pd_gen) as [$loc, $pd]) {
        yield [true, '', ['location' => $loc, 'pd' => $pd]];
    }
}

?>
