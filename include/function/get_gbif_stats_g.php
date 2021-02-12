<?php

require_once '../include/class/division_scheme.php';
require_once '../include/function/hill_diversity_number.php';
require_once '../include/function/get_gbif_taxon_key.php';
require_once '../include/function/get_gbif_data_g.php';

function get_gbif_stats_g(
    string $taxon, 
    Division_Scheme $scheme, 
    int $hill_order = 0,
    $transform = null
) {
    // get diversity function
    $diversity = hill_diversity_number($hill_order);

    $taxon_key = get_gbif_taxon_key($taxon);
    if ($taxon_key === false) return false;

    foreach($scheme->locations_g() as $loc) {
        $gbif_data_gen = get_gbif_data_g($taxon_key, $loc['data']);
        [$loc_diversity, $total] = $diversity($gbif_data_gen);
        if ($loc_diversity === null) continue;

        if ($transform !== null)
            $loc_diversity = $transform($loc_diversity);
        
            yield [ 'location' => $loc, 'diversity' => $loc_diversity, 'observations' => $total ];
    }
}

?>
