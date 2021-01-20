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
require_once '../include/function/make_geojson.php';

set_time_limit(300);
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
    'division_scheme_key' => FILTER_SANITIZE_STRING
];
if(!get_args($args)) stream_exit('incorrect args', $CLI);

$HILL_ORDER = 0;

$scheme = Division_Scheme::read($args['division_scheme_key']);
if ($scheme === false) stream_exit('incorrect division scheme key', $CLI);

$geojson_ar = [];
foreach (get_gbif_stats_g($args['taxon'], $scheme, $HILL_ORDER) as $res) {
    $geojson_ar[] = make_geojson($res, 'diversity');
}
$json = json_encode($geojson_ar);

if ($CLI)
    echo "result: {$json}".PHP_EOL;
else {
    echo "event: done\n";
    echo "data: {$json}\n\n";
    ob_flush();
    flush();
}

?>
