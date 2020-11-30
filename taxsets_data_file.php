<?php

// Gets the path to a file for storing data relating to geographical taxsets of a given taxon.
function taxsets_data_file($taxon) {
    return  getcwd() . DIRECTORY_SEPARATOR . $taxon . '_sets.csv';
}

?>