<?php

// Event stream script that outputs geoJSON array of species diversity in GBIF by location
//
// expects the following args (in the following order if CLI):
// taxon: string
// division_scheme_key: FILTER_SANITIZE_STRING

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
ignore_user_abort(true);

require_once '../include/function/stream_exit.php';
require_once '../include/function/get_args.php';
require_once '../include/function/get_gbif_stats_g.php';
require_once '../include/class/division_scheme.php';
require_once '../include/class/division_scheme_subset.php';
require_once '../include/function/make_geojson.php';

set_time_limit(0);
register_shutdown_function(function () {
    // This function will actually be called for all shutdowns,
    // including normal exit, however, all other exit points
    // should return a 'fail' or 'done' event before this function
    // gets called, so that the client ignores this event.
    stream_exit('timeout', false);
});

$CLI = (stripos(PHP_SAPI, 'cli') === 0);

$args = [
    'taxon' => FILTER_SANITIZE_ENCODED,
    'subset' => FILTER_VALIDATE_BOOLEAN,
    'division_scheme_key' => FILTER_SANITIZE_STRING
];
if (!get_args($args)) stream_exit('incorrect args', $CLI);
$scheme = Division_Scheme::read($args['division_scheme_key']);
if ($scheme === false) stream_exit('incorrect division scheme key', $CLI);

if ($args['subset']) {
    $arg_loc = ['locations' => FILTER_SANITIZE_STRING];
    if (!get_args($arg_loc, 3)) stream_exit('incorrect args', $CLI);
    $loc_str = $arg_loc['locations'];
    $scheme = Division_Scheme_Subset::get($scheme, $loc_str);
    if ($scheme === false) stream_exit('incorrect division scheme key', $CLI);
}

$HILL_ORDER = 0;

$i = 0;
foreach (get_gbif_stats_g($args['taxon'], $scheme, $HILL_ORDER) as $res) {
    $res['iteration'] = $i;
    $geojson_ar = make_geojson($res, ['iteration', 'td', 'td_observations']);
    $json = json_encode($geojson_ar);

    if (!$CLI) {
        echo "\n\n";
        echo "event: done\n";
        echo "data: {$json}\n\n";
        ob_flush();
        flush();
    }
    $i++;
}

if ($CLI)
    echo "result: {$json}".PHP_EOL;
else {
    echo "\n\n";
    echo "event: done\n";
    echo "data: 0\n\n";
    ob_flush();
    flush();
}

?>
