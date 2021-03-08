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
        $get_fields[] = 'genus_name';
        $get_fields[] = 'species_name';
        $seq_gen = get_sequences_g($args['taxon'], $required_fields, $get_fields);
    } else {
        $seq_gen = retrieve_sequences_g($args['taxon'], $required_fields);
    }

    $mean_coord = function ($entries, $cols) {
        $lat_col = $cols[BOLD::LATITUDE];
        $lon_col = $cols[BOLD::LONGITUDE];
        $lat_sum = 0;
        $lon_sum = 0;
        $i = 0;
        foreach($entries as $entry) {
            $i++;
            $lat_sum += $entry['fields'][$lat_col];
            $lon_sum += $entry['fields'][$lon_col];
        }
        return ['lat' => ($lat_sum / $i), 'lon' => ($lon_sum / $i)];
    };
    
    // set up generators (doesn't call them yet)
    $loc_gen = locate_sequences_g($seq_gen, $scheme);
    $sample_gen = sample_sequences_g($loc_gen, $args['subsample_size'], $mean_coord, 'mean_coord');
    $pd_gen = get_sample_pd_g($sample_gen);
    return $pd_gen;
}

?>
