<?php


$WINDOWS = (stripos(PHP_OS, 'WIN') === 0);
$DIR = __DIR__ . DIRECTORY_SEPARATOR ;

require_once $DIR. 'get_sequences.php';
require_once $DIR. 'subsample_and_align.php';
require_once $DIR. 'geo_divisions.php';
require_once $DIR. 'division_scheme.php';
require_once $DIR. 'geo_divide.php';
require_once $DIR. 'make_trees.php';
require_once $DIR. 'tree_lengths.php';

// Default arguments

$SUBSAMPLE_NUMBER = 10;

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

$PAUP_COMMANDS_SETUP = $DIR. 'paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = $DIR. 'paup_commands_tree.txt';
$PAUP_COMMANDS_END = $DIR. 'paup_commands_end.txt';

$MINIMUM_SUBSAMPLE_NUMBER = 3; // need at least 3 taxa to build trees
$MARKER = 'COI-5P';
$USE_COORDS = true;

class field
{
	const TAXON = 'taxon';
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

echo ('Creating and aligning subsamples of size '.$SUBSAMPLE_NUMBER.'...'.PHP_EOL);
$aligned_subsamples = subsample_and_align($SUBSAMPLE_NUMBER, $taxon, $geo_divisions);

$tree_file = $taxon . '.tre';
make_trees($aligned_subsamples, $geo_divisions, $tree_file);
echo('Tree lengths for location samples: '.PHP_EOL);
print_r(tree_lengths($tree_file));

?>