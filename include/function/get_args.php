<?php

require_once $CONFIG_DIR. 'default_args.php';

function get_args() {
    global $CLI;

    if ($CLI) {
        get_cli_args();
    } else {
        get_url_args();
    }
}

function get_cli_args() {
    global $argc, $argv;
    global $ARGS;

    if(!isset($argc)) { exit("argc and argv disabled"); }
    if($argc + 1 > count($ARGS)) { exit("Wrong number of arguments given"); }
    if($argc <= 1) { exit("No arguments given"); }

    $i = 1;
    foreach ($ARGS as $k => &$v) {
        if ($i >= $argc) { break; }
        $v = $argv[$i];
        $i++;
    }
}

function get_url_args() {
    global $TAXON, $SUBSAMPLE_COUNT, $LAT_GRID_DEG, $LON_GRID_DEG;

    if(!isset($_GET['taxon'])) {
        exit ("Taxon argument not given");
    }

    $taxon = $_GET['taxon'];
    $bad_chars = '/[^- a-zA-Z]/'; // everything but hyphen, space, letters
    $TAXON = rawurlencode(preg_replace($bad_chars, '', $taxon));

    if (isset($_GET['subs'])) {
        $SUBSAMPLE_COUNT = intval($_GET['subs']);
    }
    if (isset($_GET['lat_grid'])) {
        $LAT_GRID_DEG = floatval($_GET['lat_grid']);
    }
    if (isset($_GET['lon_grid'])) {
        $LON_GRID_DEG = floatval($_GET['lon_grid']);
    }

    // Clustal/PAUP path is server business, not user input!
}

?>
