<?php


$WINDOWS = (stripos(PHP_OS, 'WIN') === 0);
$DIR = __DIR__ . DIRECTORY_SEPARATOR ;

$FUNCTIONS_DIR = $DIR .'functions'. DIRECTORY_SEPARATOR;

require_once $FUNCTIONS_DIR. 'get_sequences.php';
require_once $FUNCTIONS_DIR. 'subsample_and_align.php';
require_once $FUNCTIONS_DIR. 'sequence_sets.php';
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

$PAUP_COMMANDS_SETUP = $FUNCTIONS_DIR. 'paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = $FUNCTIONS_DIR. 'paup_commands_tree.txt';
$PAUP_COMMANDS_END = $FUNCTIONS_DIR. 'paup_commands_end.txt';

$MINIMUM_SUBSAMPLE_NUMBER = 3; // need at least 3 taxa to build trees
$MARKER = 'COI-5P';

$REPLICATES = 5;

$DELETE_TEMP_FILES = true;
$PRINT_OUTPUT = false;
$OUTPUT_RESULTS = true;
$OUTPUT_FILE = 'bold_phylodiv_results.csv';
$OUTPUT_FILE_DELIMITER = ',';

// Class to store names of fields in sequence_sets file
class TAXSETS
{
	const TAXON = 'taxon';
	const TOTAL_SEQUENCE_COUNT = 'total_sequence_count';
	const DIVISION_SCHEME = 'division_scheme';
	const LOCATION = 'location';
	const COUNT = 'count';
	const FILE = 'file';
	const TAXSET = 'taxset';
	const FIELDS = array(
		self::TAXON, self::TOTAL_SEQUENCE_COUNT, self::DIVISION_SCHEME, self::LOCATION, 
		self::COUNT, self::FILE, self::TAXSET
	);
}

class Coord_grid
{
	const SIZE_LAT = 'SIZE_LAT';
	const SIZE_LON = 'SIZE_LON';
	public array $params = array();
}

// Class to store names of the fields we need in data obtained from BOLD
// Make sure to check the output from BOLD if there are any format changes; 
// otherwise, there will be fields missing from the saved data.
class BOLD
{
	const MARKER_CODE = 'markercode';
	const PROCESS_ID = 'processid';
	const SPECIES_NAME = 'species_name';
	const GENUS_NAME = 'genus_name';
	const NUCLEOTIDES = 'nucleotides';
	const INSTITUTION = 'institution_storing';
	const COLLECTION_EVENT_ID = 'collection_event_id';
	const COLLECION_DATE_START = 'collectiondate_start';
	const COLLECTION_DATE_END = 'collectiondate_end';
	const COLLECTION_TIME = 'collectiontime';
	const COLLECTION_NOTE = 'collection_note';
	const SITE_CODE = 'site_code';
	const SAMPLING_PROTOCOL = 'sampling_protocol';
	const HABITAT = 'habitat';
	const NOTES = 'notes';
	const LATITUDE = 'lat';
	const LONGITUDE = 'lon';
	const COORD_SOURCE = 'coord_source';
	const COORD_ACCURACY = 'coord_accuracy';
	const ELEVATION = 'elev';
	const DEPTH = 'depth';
	const ELEVATION_ACCURACY = 'elev_accuracy';
	const DEPTH_ACCURACY = 'depth_accuracy';
	const COUNTRY = 'country';
	const PROVINCE_STATE = 'province_state';
	const REGION = 'region';
	const SECTOR = 'sector';
	const EXACT_SITE = 'exactsite';
	const SEQUENCE_DATA_FIELDS = array(
		self::MARKER_CODE, self::PROCESS_ID, self::SPECIES_NAME, self::GENUS_NAME,
		self::INSTITUTION, self::COLLECTION_EVENT_ID, self::COLLECION_DATE_START, 
		self::COLLECTION_DATE_END, self::COLLECTION_TIME, self::COLLECTION_NOTE, 
		self::SITE_CODE, self::SAMPLING_PROTOCOL, self::HABITAT, self::NOTES, 
		self::LATITUDE, self::LONGITUDE, self::COORD_SOURCE, self::COORD_ACCURACY, 
		self::ELEVATION, self::DEPTH, self::ELEVATION_ACCURACY, self::DEPTH_ACCURACY, 
		self::COUNTRY, self::PROVINCE_STATE, self::REGION, self::SECTOR, self::EXACT_SITE
	);
	const LOCATION_FIELDS = array(
		self::INSTITUTION, self::COLLECTION_EVENT_ID, self::COLLECION_DATE_START, 
		self::COLLECTION_DATE_END, self::COLLECTION_TIME, self::COLLECTION_NOTE, 
		self::SITE_CODE, self::SAMPLING_PROTOCOL, self::HABITAT, self::NOTES, 
		self::LATITUDE, self::LONGITUDE, self::COORD_SOURCE, self::COORD_ACCURACY, 
		self::ELEVATION, self::DEPTH, self::ELEVATION_ACCURACY, self::DEPTH_ACCURACY, 
		self::COUNTRY, self::PROVINCE_STATE, self::REGION, self::SECTOR, self::EXACT_SITE
	);
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

$COORD_GRID = new Coord_grid();
$COORD_GRID->params = array(
	Coord_grid::SIZE_LAT => $LATITUDE_GRID_SIZE_DEG,
	Coord_grid::SIZE_LON => $LONGITUDE_GRID_SIZE_DEG
);
$DIVISION_SCHEME = new Division_scheme(Division_scheme::COORDS, array(BOLD::LATITUDE, BOLD::LONGITUDE));

// Get sequences, subsample, align, and build trees

[$sequences_found, $download_attempted] = get_sequences($taxon, $MARKER);
if (!$sequences_found) {
	exit("No sequences for {$taxon} found locally or on BOLD.");
}

if (!$download_attempted) {
	echo("Found saved data file for {$taxon}.".PHP_EOL);
}

$locations = Sequence_Sets::get_locations($taxon, $DIVISION_SCHEME, $SEQUENCE_DATA_DELIMITER);
if ($lc = count($locations)) {
	if ($download_attempted) {
		echo("Sorted downloaded sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.".PHP_EOL);
	} else {
		echo("Found sorting of sequences into {$lc} locations according to {$DIVISION_SCHEME->key}.".PHP_EOL);
	}
} else {
	echo("Sorting sequences into location according to {$DIVISION_SCHEME->key}...".PHP_EOL);
	$locations = geo_divide($taxon);
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
	$aligned_subsamples = subsample_and_align($SUBSAMPLE_NUMBER, $taxon, $locations);

	$tree_file = $taxon.'_'.$i. '.tre';
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
				$taxon,
				$MARKER,
				total_sequence_count($taxon),
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
