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

$DELETE_TEMP_FILES = true;

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

print_r($geo_divisions);

echo ('Creating and aligning subsamples of size '.$SUBSAMPLE_NUMBER.'...'.PHP_EOL);
$aligned_subsamples = subsample_and_align($SUBSAMPLE_NUMBER, $taxon, $geo_divisions);

$tree_file = $taxon . '.tre';
if (!make_trees($aligned_subsamples, $geo_divisions, $tree_file)) {
	exit('Tree construction failed.');
}
echo('Tree lengths for location samples: '.PHP_EOL);
print_r(tree_lengths($tree_file));

// clear up temp folder
// $temp_dir_handle = opendir($TEMP_DIR);
// $empty = (readdir($temp_dir_handle) === false);
// closedir($temp_dir_handle);
// if ($empty) { rmdir($TEMP_DIR); }

?>