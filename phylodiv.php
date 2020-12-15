<?php

$DIR = __DIR__ . DIRECTORY_SEPARATOR ;
$INCLUDE_DIR = $DIR . 'include' . DIRECTORY_SEPARATOR;
require_once $INCLUDE_DIR. 'require.php';

get_args();
init();

// Get sequences, subsample, align, and build trees

[$sequences_found, $download_attempted] = get_sequences($TAXON, $MARKER);
if (!$sequences_found) {
	exit("No sequences for {$TAXON} found locally or on BOLD.");
}

if (!$download_attempted) {
	say_verbose("Found saved data file for {$TAXON}.");
}

$locations = Sequence_Sets::get_locations($TAXON, $DIVISION_SCHEME, $SEQUENCE_DATA_DELIMITER);
if ($lc = count($locations)) {
	if ($download_attempted) {
		say_verbose("Sorted downloaded sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.");
	} else {
		say_verbose("Found sorting of sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.");
	}
} else {
	say_verbose("Sorting sequences into location according to {$DIVISION_SCHEME->key}...");
	$locations = $DIVISION_SCHEME->sort($TAXON);
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


say("Creating and aligning subsamples of size {$SUBSAMPLE_COUNT}...");
// subsample_and_align takes $locations by reference and updates it to include only locations successfully subsampled
$aligned_subsamples = subsample_and_align($SUBSAMPLE_COUNT, $TAXON, $locations);

if ($OUTPUT_RESULTS) {
	$location_cols = array();
	foreach ($DIVISION_SCHEME->saved_params as $field) {
		$location_cols[$field] = array_search($field, $output_header);
	}
	foreach (
		both($locations, get_tree_lengths($aligned_subsamples)) 
		as [$loc, $tree_len]
	) {
		$entry = array(
			$TAXON,
			$MARKER,
			total_sequence_count($TAXON),
			$DIVISION_SCHEME->key,
			$loc->key,
			$SUBSAMPLE_COUNT,
			$tree_len,
		);
		$entry = array_pad($entry, $output_header_size, '');
		// Add in the relevant location data
		foreach ($loc->data as $field => $value) {
			$entry[$location_cols[$field]] = $value;
		}
		fputcsv($output_handle, $entry, $OUTPUT_FILE_DELIMITER);
	}
}
fclose($output_handle);

say("Wrote tree lengths to {$OUTPUT_FILE}.");

?>
