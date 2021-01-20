<?php

function get_gbif_taxon_key(string $taxon) {
    $GBIF_SPECIES_URL_PREFIX = 'http://api.gbif.org/v1/species/match';
    $GBIF_SPECIES_TAXON_KEY_FIELD = 'usageKey';

    // Get GBIF taxon key for required taxon
    $name = urlencode($taxon);
    $tax_data_url = $GBIF_SPECIES_URL_PREFIX . '?'
    . 'verbose=false'
    . '&name=' . $name;
    $tax_data_json = file_get_contents($tax_data_url);
    if ($tax_data_json === false) return false;
    $tax_data = json_decode($tax_data_json, true);
    if ($tax_data === null) return false;
    if (!isset($tax_data[$GBIF_SPECIES_TAXON_KEY_FIELD])) return false;
    return $tax_data[$GBIF_SPECIES_TAXON_KEY_FIELD];
}

?>
