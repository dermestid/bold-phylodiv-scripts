<?php


$WINDOWS = (stripos(PHP_OS, 'WIN') === 0);
$DIR = __DIR__ . DIRECTORY_SEPARATOR ;

$FUNCTIONS_DIR = $DIR .'functions'. DIRECTORY_SEPARATOR;

require_once $FUNCTIONS_DIR. 'get_sequences.php';
require_once $FUNCTIONS_DIR. 'subsample_and_align.php';
require_once $FUNCTIONS_DIR. 'geo_divisions.php';
require_once $FUNCTIONS_DIR. 'division_scheme.php';
require_once $FUNCTIONS_DIR. 'geo_divide.php';
require_once $FUNCTIONS_DIR. 'make_trees.php';
require_once $FUNCTIONS_DIR. 'tree_lengths.php';
require_once $FUNCTIONS_DIR. 'total_sequence_count.php';

// Default arguments

$SUBSAMPLE_NUMBER = 20;

$LATITUDE_GRID_SIZE_DEG = 30;
$LONGITUDE_GRID_SIZE_DEG = 30;

$CLUSTAL_PATH = '/usr/local/bin/clustalw2';
$PAUP_PATH = '/usr/local/bin/paup';
if ($WINDOWS) {
	$CLUSTAL_PATH = '\\"Program Files (x86)"\\ClustalW2\\clustalw2';
	$PAUP_PATH = '%appdata%\\PAUP4\\paup4';
}

// Constants

$GEO_DIVISION_SCHEMES = array_flip(array('COORDS','COUNTRY'));
$GEO_DIVISION_SCHEME = $GEO_DIVISION_SCHEMES['COORDS'];

$PAUP_COMMANDS_SETUP = $FUNCTIONS_DIR. 'paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = $FUNCTIONS_DIR. 'paup_commands_tree.txt';
$PAUP_COMMANDS_END = $FUNCTIONS_DIR. 'paup_commands_end.txt';

$MINIMUM_SUBSAMPLE_NUMBER = 3; // need at least 3 taxa to build trees
$MARKER = 'COI-5P';

$REPLICATES = 10;

$DELETE_TEMP_FILES = true;
$PRINT_OUTPUT = false;
$OUTPUT_RESULTS = true;
$OUTPUT_FILE = 'bold_phylodiv_results.csv';
$OUTPUT_FILE_DELIMITER = ',';

class field
{
	const TAXON = 'taxon';
	const TOTAL_SEQUENCE_COUNT = 'total_sequence_count';
	const DIVISION_SCHEME = 'division_scheme';
	const LOCATION = 'location';
	const COUNT = 'count';
	const FILE = 'file';
	const TAXSET = 'taxset';
}
$TAXSETS_DATA_DELIMITER = ',';
$TAXSET_DELIMITER = ' ';
$SEQUENCE_DATA_DELIMITER = ',';

$TEMP_DIR = getcwd() .DIRECTORY_SEPARATOR. 'temp' .DIRECTORY_SEPARATOR; 
if (!is_dir($TEMP_DIR)) { mkdir($TEMP_DIR); }
$LOG_DIR = getcwd() .DIRECTORY_SEPARATOR. 'logs' .DIRECTORY_SEPARATOR;
if (!is_dir($LOG_DIR)) { mkdir($LOG_DIR); }
$SEQUENCES_DIR = getcwd() .DIRECTORY_SEPARATOR. 'sequences' .DIRECTORY_SEPARATOR;
if (!is_dir($SEQUENCES_DIR)) { mkdir($SEQUENCES_DIR); }

// Get command line arguments

$args = array(
	'SCRIPT_PATH',
	'taxon',
	'SUBSAMPLE_NUMBER',
	'LATITUDE_GRID_SIZE_DEG',
	'LONGITUDE_GRID_SIZE_DEG',
	'CLUSTAL_PATH',
	'PAUP_PATH');

if(!isset($argc)) { exit("argc and argv disabled"); }
if($argc > count($args)) { exit("Wrong number of arguments given"); }
if(!($i = $argc - 1)) { exit("No arguments given"); }

while($i) {
	${$args[$i]} = $argv[$i];
	// echo('set '. $args[$i] . ' to ' . $argv[$i] . PHP_EOL);
	$i--;
}

// Get sequences, subsample, align, and build trees

[$download_attempted, $taxsets_data_file] = get_sequences($taxon, $MARKER);
if (!$taxsets_data_file) {
	exit ('No sequences for '.$taxon.' found locally or on BOLD.');
}

if (!$download_attempted) {
	echo ('Found saved data file for ' . $taxon . '.'.PHP_EOL);
}

$geo_divisions = geo_divisions($taxon);
if ($lc = count($geo_divisions)) {
	if ($download_attempted) {
		echo ('Sorted downloaded sequences into '.$lc.' locations according to '. division_scheme().'.' .PHP_EOL);
	} else {
		echo ('Found sorting of sequences into '.$lc.' locations according to '. division_scheme().'.' .PHP_EOL);
	}
} else {
	echo ('Sorting sequences into location according to ' . division_scheme() . '...' . PHP_EOL);
	$geo_divisions = geo_divide($taxon);
}

if ($OUTPUT_RESULTS) {
	if (!file_exists($OUTPUT_FILE)) {
		$output_handle = fopen($OUTPUT_FILE, 'w');
		$output_header = array(
			'taxon',
			'marker',
			'total_sequence_count',
			'location',
			'subsample_size',
			'subsample_tree_length'
		);
		fputcsv($output_handle, $output_header, $OUTPUT_FILE_DELIMITER);
	} else {
		$output_handle = fopen($OUTPUT_FILE, 'a');
	}

	if ($output_handle === false) {
		exit ('Could not open output file '.$OUTPUT_FILE);
	}
}

for ($i = 0; $i < $REPLICATES; $i++) {
	echo ('Creating and aligning subsamples of size '.$SUBSAMPLE_NUMBER.'...'.PHP_EOL);
	// subsample_and_align takes $geo_divisions by reference and updates it to include only divisions successfully subsampled
	$aligned_subsamples = subsample_and_align($SUBSAMPLE_NUMBER, $taxon, $geo_divisions);

	$tree_file = $taxon.'_'.$i. '.tre';
	if (!make_trees($aligned_subsamples, $geo_divisions, $tree_file)) {
		exit('Tree construction failed.');
	}
	$tree_lengths = tree_lengths($tree_file);
	if ($PRINT_OUTPUT) {
		echo('Tree lengths for location samples: '.PHP_EOL);
		print_r($tree_lengths);
	}

	if ($OUTPUT_RESULTS) {
		foreach ($tree_lengths as $loc => $length) {
			$entry = array(
				$taxon,
				$MARKER,
				total_sequence_count($taxon),
				$loc,
				$SUBSAMPLE_NUMBER,
				$length
			);
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