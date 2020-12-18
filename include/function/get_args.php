<?php

require_once $CONFIG_DIR. 'setup.php'; // $CLI
require_once $CONFIG_DIR. 'default_args.php';
require_once $FUNCTION_DIR. 'init_args.php';
require_once $CLASS_DIR. 'status.php';

function get_args() {
    global $CLI;

    if ($CLI)
        get_cli_args();
    else
        get_url_args();

    init_args();
}

function get_cli_args(&$args = null) {
    global $argc, $argv;
    global $ARGS;

    if ($args === null) $args = $ARGS;

    if(!isset($argc)) exit("argc and argv disabled");
    if($argc - 1 > count($args)) exit("Wrong number of arguments given");
    if($argc <= 1) exit("No arguments given");

    $i = 1;
    foreach ($args as $k => &$v) {
        if ($i >= $argc) break;
        $v = $argv[$i];
        $i++;
    }
}

function get_url_args(&$args = null) {
    global $TAXON, $SUBSAMPLE_COUNT, $LAT_GRID_DEG, $LON_GRID_DEG;

    if(!isset($_GET['taxon'])) {
        Status::arg_missing('taxon');
        exit;
    }

    $taxon = $_GET['taxon'];
    $bad_chars = '/[^- a-zA-Z]/'; // everything but hyphen, space, letters
    $TAXON = rawurlencode(preg_replace($bad_chars, '', $taxon));

    if (isset($_GET['subs'])) $SUBSAMPLE_COUNT = intval($_GET['subs']);
    if (isset($_GET['lat_grid'])) $LAT_GRID_DEG = floatval($_GET['lat_grid']);
    if (isset($_GET['lon_grid'])) $LON_GRID_DEG = floatval($_GET['lon_grid']);

    if ($args !== null)
        $args = array(
            'taxon' => $TAXON, 
            'subs' => $SUBSAMPLE_COUNT, 
            'lat_grid' => $LAT_GRID_DEG, 
            'lon_grid' => $LON_GRID_DEG);

    // Clustal/PAUP path is server business, not user input!
}

?>
