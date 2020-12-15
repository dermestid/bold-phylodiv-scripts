<?php

// Default arguments

$TAXON = '';

$SUBSAMPLE_COUNT = 20;

$LAT_GRID_DEG = 30;
$LON_GRID_DEG = 30;

$CLUSTAL_PATH = '/usr/local/bin/clustalw2';
$PAUP_PATH = '/usr/local/bin/paup';
if ($WINDOWS) {
	$CLUSTAL_PATH = '\\"Program Files (x86)"\\ClustalW2\\clustalw2';
	$PAUP_PATH = '%appdata%\\PAUP4\\paup4';
}

// Arguments to be given to the script
// Excluding the first, automatically-given argument (which is just the path to the script)
$ARGS = array(
    &$TAXON,
    &$SUBSAMPLE_COUNT,
    &$LAT_GRID_DEG,
    &$LON_GRID_DEG,
    &$CLUSTAL_PATH,
    &$PAUP_PATH,
);

?>
