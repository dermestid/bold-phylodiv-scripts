<?php

// Default arguments

$TAXON = '';

$SUBSAMPLE_NUMBER = 20;

$LATITUDE_GRID_SIZE_DEG = 30;
$LONGITUDE_GRID_SIZE_DEG = 30;

$CLUSTAL_PATH = '/usr/local/bin/clustalw2';
$PAUP_PATH = '/usr/local/bin/paup';
if ($WINDOWS) {
	$CLUSTAL_PATH = '\\"Program Files (x86)"\\ClustalW2\\clustalw2';
	$PAUP_PATH = '%appdata%\\PAUP4\\paup4';
}

$ARGS = array(
    &$TAXON,
    &$SUBSAMPLE_NUMBER,
    &$LATITUDE_GRID_SIZE_DEG,
    &$LONGITUDE_GRID_SIZE_DEG,
    &$CLUSTAL_PATH,
    &$PAUP_PATH,
);

?>
