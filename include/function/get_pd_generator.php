<?php

require_once '../include/class/division_scheme.php';
require_once '../include/function/get_sequences_g.php';
require_once '../include/function/retrieve_sequences_g.php';
require_once '../include/function/locate_sequences_g.php';
require_once '../include/function/sample_sequences_g.php';
require_once '../include/function/get_sample_pd_g.php';

function get_pd_generator(array $args, Division_Scheme $scheme) {
    $required_fields = $scheme->required_fields();
    if ($args['do_download']) {
        $get_fields = Division_Scheme::all_required_fields();
        $seq_gen = get_sequences_g($args['taxon'], $required_fields, $get_fields);
    } else {
        $seq_gen = retrieve_sequences_g($args['taxon'], $required_fields);
    }
    
    // set up generators (doesn't call them yet)
    $loc_gen = locate_sequences_g($seq_gen, $scheme);
    $sample_gen = sample_sequences_g($loc_gen, $args['subsample_size']);
    $pd_gen = get_sample_pd_g($sample_gen);
    return $pd_gen;
}

?>
