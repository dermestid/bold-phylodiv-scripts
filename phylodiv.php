<?php


$WINDOWS = (stripos(PHP_OS, 'WIN') === 0);
$DIR = __DIR__ . DIRECTORY_SEPARATOR ;

$FUNCTIONS_DIR = $DIR .'functions'. DIRECTORY_SEPARATOR;

require_once $FUNCTIONS_DIR. 'constants.php';
require_once $FUNCTIONS_DIR. 'get_args.php';
require_once $FUNCTIONS_DIR. 'init.php';
require_once $FUNCTIONS_DIR. 'get_sequences.php';
require_once $FUNCTIONS_DIR. 'subsample_and_align.php';
require_once $FUNCTIONS_DIR. 'sequence_sets.php';
require_once $FUNCTIONS_DIR. 'division_scheme.php';
require_once $FUNCTIONS_DIR. 'geo_divide.php';
require_once $FUNCTIONS_DIR. 'make_trees.php';
require_once $FUNCTIONS_DIR. 'tree_lengths.php';
require_once $FUNCTIONS_DIR. 'total_sequence_count.php';

get_args();
init();

// Get sequences, subsample, align, and build trees

[$sequences_found, $download_attempted] = get_sequences($TAXON, $MARKER);
if (!$sequences_found) {
	exit("No sequences for {$TAXON} found locally or on BOLD.");
}

if (!$download_attempted) {
	echo("Found saved data file for {$TAXON}.".PHP_EOL);
}

$locations = Sequence_Sets::get_locations($TAXON, $DIVISION_SCHEME, $SEQUENCE_DATA_DELIMITER);
if ($lc = count($locations)) {
	if ($download_attempted) {
		echo("Sorted downloaded sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.".PHP_EOL);
	} else {
		echo("Found sorting of sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.".PHP_EOL);
	}
} else {
	echo("Sorting sequences into location according to {$DIVISION_SCHEME->key}...".PHP_EOL);
	$locations = geo_divide($TAXON);
}

if ($OUTPUT_RESULTS) {
	$output_header = array(
		'taxon',
		'marker',
		'total_sequence_count',
		'division_scheme',
		'location_key',
		'subsample_size',
		'subsample_tree_length'
	);
	$output_header = array_merge($output_header, $DIVISION_SCHEME->saved_params);
	$output_header_size = count($output_header);
	if (!file_exists($OUTPUT_FILE)) {
		$output_handle = fopen($OUTPUT_FILE, 'w');
		fputcsv($output_handle, $output_header, $OUTPUT_FILE_DELIMITER);
	} else {
		$output_handle = fopen($OUTPUT_FILE, 'a');
	}

	if ($output_handle === false) {
		exit ('Could not open output file '.$OUTPUT_FILE);
	}
}

for ($i = 0; $i < $REPLICATES; $i++) {
	echo ("Creating and aligning subsamples of size {$SUBSAMPLE_NUMBER}...".PHP_EOL);
	// subsample_and_align takes $locations by reference and updates it to include only divisions successfully subsampled
	$aligned_subsamples = subsample_and_align($SUBSAMPLE_NUMBER, $TAXON, $locations);

	$tree_file = $TAXON.'_'.$i. '.tre';
	if (!make_trees($aligned_subsamples, array_keys($locations), $tree_file)) {
		exit('Tree construction failed.');
	}
	$tree_lengths = tree_lengths($tree_file);
	if ($PRINT_OUTPUT) {
		echo('Tree lengths for location samples: '.PHP_EOL);
		print_r($tree_lengths);
	}

	if ($OUTPUT_RESULTS) {
		$location_cols = array();
		foreach ($DIVISION_SCHEME->saved_params as $field) {
			$location_cols[$field] = array_search($field, $output_header);
		}
		foreach ($locations as $key => $loc) {
			$entry = array(
				$TAXON,
				$MARKER,
				total_sequence_count($TAXON),
				$DIVISION_SCHEME->key,
				$key,
				$SUBSAMPLE_NUMBER,
				$tree_lengths[$key]
			);
			$entry = array_pad($entry, $output_header_size, '');
			// Add in the relevant location data
			foreach ($loc->data as $field => $value) {
				$entry[$location_cols[$field]] = $value;
			}
			fputcsv($output_handle, $entry, $OUTPUT_FILE_DELIMITER);
		}
	}
} // end for loop
fclose($output_handle);

// clear up temp folder
$temp_dir_handle = opendir($TEMP_DIR);
do {
	$temp_contents = readdir($temp_dir_handle);
} while ($temp_contents == '.' || $temp_contents == '..');
closedir($temp_dir_handle);
if ($temp_contents === false) { rmdir($TEMP_DIR); }

?>
