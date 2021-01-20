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
require_once '../include/function/get_sequences_g.php';
require_once '../include/function/retrieve_sequences_g.php';
require_once '../include/function/locate_sequences_g.php';
require_once '../include/function/sample_sequences_g.php';
require_once '../include/function/get_sample_pd_g.php';
require_once '../include/function/update_after_period.php';
require_once '../include/function/make_geojson.php';

set_time_limit(300);
$DIR = getcwd();
register_shutdown_function(function () use ($DIR) {
    // This function will actually be called for all shutdowns,
    // including normal exit, however, all other exit points
    // should return a 'fail' or 'done' event before this function
    // gets called, so that the client ignores this event.
    foreach (glob("$DIR/*.log") as $file) unlink($file);
    foreach (glob("$DIR/temp*") as $file) unlink($file);
    stream_exit('timeout', false);
});


$CLI = (stripos(PHP_SAPI, 'cli') === 0);
$PERIOD = 5.0;
$prev_time = microtime(true); // get start time

$args = [
    'do_download' => FILTER_VALIDATE_BOOLEAN,
    'taxon' => FILTER_SANITIZE_ENCODED,
    'division_scheme_key' => FILTER_SANITIZE_STRING,
    'subsample_size' => FILTER_VALIDATE_INT
];
if(!get_args($args)) stream_exit('incorrect args', $CLI);

$scheme = Division_Scheme::read($args['division_scheme_key']);
if ($scheme === false) stream_exit('incorrect division scheme key', $CLI);

$required_fields = $scheme->required_fields();
if ($args['do_download']) {
    $get_fields = Division_Scheme::all_required_fields();
    $seq_gen = get_sequences_g($args['taxon'], $required_fields, $get_fields);
} else {
    $seq_gen = retrieve_sequences_g($args['taxon'], $required_fields);
}

// set up generators (doesn't call them yet)
$loc_gen = locate_sequences_g($seq_gen, $scheme);
$sample_gen = sample_sequences_g($loc_gen, $args['subsample_size']);
$pd_gen = get_sample_pd_g($sample_gen);

// Get sequences, sample, and calculate pd, building geojson array
$results = [];
$i = 0;
if ($CLI) {
    foreach ($pd_gen as $result) {

        if ($result === null) {
            $i++;
            update_after_period($PERIOD, $prev_time, true, "Read {$i} sequences...");
            continue;
        }
        
        [ $done, $task, $data ] = $result;
        if ($done) {
            $results[] = $data;
        } else {
            update_after_period($PERIOD, $prev_time, true, "Task in progress: {$task}");
        }
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
        if ($done) {
            $results[] = $data;
        } else {
            update_after_period(
                $PERIOD, $prev_time, false, ['task' => 'alignment'], 'working');
        }
    }
}
$geojson_ar = array_map(fn($res) => make_geojson($res, 'pd'), $results);
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
