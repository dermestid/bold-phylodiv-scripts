<?php

// Event stream script to produce phylogenetic diversity stats in geoJSON format.
//
// expects the following args (in the following order if CLI):
// do_download: boolean
// taxon: string
// division_scheme_key: string
// subsample_size: integer

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
ignore_user_abort(true);

require_once '../include/function/stream_exit.php';
require_once '../include/function/get_args.php';
require_once '../include/class/division_scheme.php';
require_once '../include/class/division_scheme_subset.php';
require_once '../include/function/get_pd_generator.php';
require_once '../include/function/update_after_period.php';
require_once '../include/function/make_geojson.php';

set_time_limit(0);
$DIR = getcwd();
// register_shutdown_function(function () {
//     // This function will actually be called for all shutdowns,
//     // including normal exit, however, all other exit points
//     // should return a 'fail' or 'done' event before this function
//     // gets called, so that the client ignores this event.
//     stream_exit('timeout', false);
// });


$CLI = (stripos(PHP_SAPI, 'cli') === 0);
$PERIOD = 5.0;
$prev_time = microtime(true); // get start time

$args = [
    'do_download' => FILTER_VALIDATE_BOOLEAN,
    'taxon' => FILTER_SANITIZE_ENCODED,
    'subset' => FILTER_VALIDATE_BOOLEAN,
    'division_scheme_key' => FILTER_SANITIZE_STRING,
    'subsample_size' => FILTER_VALIDATE_INT
];
if(!get_args($args)) stream_exit('incorrect args', $CLI);

$scheme = Division_Scheme::read($args['division_scheme_key']);
$scheme_key = $args['division_scheme_key'];
if ($scheme === false) stream_exit("incorrect division scheme key: {$scheme_key}", $CLI);

if ($args['subset'] === 'true' || $args['subset'] === true) {
    $arg_loc = ['locations' => FILTER_SANITIZE_STRING];
    if (!get_args($arg_loc, 3)) stream_exit('incorrect args', $CLI);
    $loc_str = $arg_loc['locations'];
    $scheme = Division_Scheme_Subset::get($scheme, $loc_str);
    if ($scheme === false) stream_exit('incorrect division scheme key', $CLI);
}

$pd_gen = get_pd_generator($args, $scheme);
$results = [];
$i = 0;

// Get sequences, sample, and calculate pd, building geojson array
if ($CLI) {
    foreach ($pd_gen as $result) {

        if ($result === null) {
            $i++;
            update_after_period($PERIOD, $prev_time, true, "Read {$i} sequences...");
            continue;
        }
        
        [ $done, $task, $data ] = $result;
        if ($done)
            $results[] = $data;
        else
            update_after_period($PERIOD, $prev_time, true, "Task in progress: {$task}");
    }
} else {
    foreach ($pd_gen as $result) {
        if (connection_aborted()) exit;
    
        if ($result === null) {
            $i++;
            update_after_period(
                $PERIOD, $prev_time, false, ['task' => 'sampling', 'sequences' => $i], 'working');
            continue;
        }
        
        [ $done, $task, $data ] = $result;
        if ($done)
            $results[] = $data;
        else
            update_after_period(
                $PERIOD, $prev_time, false, ['task' => 'alignment'], 'working');
    }
}

$geojson_ar = array_map(
    fn($res) => make_geojson($res, ['pd', 'pd_observations', 'mean_coord']),
    $results
);
$json = json_encode($geojson_ar);
if ($json === '') $json = '[]';
if ($CLI)
    echo "result: {$json}".PHP_EOL;
else {
    echo "\n\n";
    echo "event: done\n";
    echo "data: {$json}\n\n";
    ob_flush();
    flush();
}
?>
