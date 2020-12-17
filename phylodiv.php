<?php

require_once 'include/require_main.php';

get_args();

// Get sequences, subsample, align, and build trees

$local_sequences_found = get_sequences($TAXON, $MARKER);

if ($local_sequences_found)
	say_verbose("Found saved data for {$TAXON}.");

$locations = Sequence_Sets::get_locations($TAXON, $DIVISION_SCHEME, $SEQUENCE_DATA_DELIMITER);
if ($lc = count($locations)) {
	if ($local_sequences_found)
		say_verbose("Found sorting of sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.");
	else
		say_verbose("Sorted downloaded sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.");
} else {
	say_verbose("Sorting sequences into location according to {$DIVISION_SCHEME->key}...");
	$locations = $DIVISION_SCHEME->sort($TAXON);
}

if ($SAVE_RESULTS_CSV) {
	$results_file = Tree_Lengths::open($DIVISION_SCHEME);
}


say("Creating and aligning subsamples of size {$SUBSAMPLE_COUNT}...");
// subsample_and_align takes $locations by reference and updates it to include only locations successfully subsampled
$aligned_subsamples = subsample_and_align($SUBSAMPLE_COUNT, $TAXON, $locations);

$results = array();
foreach(
	both($locations, get_tree_lengths($aligned_subsamples))
	as [$loc, $tree_len]
) {
	array_push(
		$results, 
		$r = Tree_Lengths::make_entry($TAXON, $MARKER, $loc, $SUBSAMPLE_COUNT, $tree_len));
	if ($SAVE_RESULTS_CSV)
		$results_file->write_entry($r);
}

Status::done($results);

?>
