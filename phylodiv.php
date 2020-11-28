<?php


$WINDOWS = (stripos(PHP_OS, 'WIN') === 0);

// Default arguments and constants

$SAMPLE_NUMBER = 20;

$CLUSTAL_PATH = '/usr/local/bin/clustalw2';
$PAUP_PATH = '/usr/local/bin/paup';
if ($WINDOWS) {
	$CLUSTAL_PATH = '\\"Program Files (x86)"\\ClustalW2\\clustalw2';
	$PAUP_PATH = '%appdata%\\PAUP4\\paup4';
}

$MARKER = 'COI-5P';
$USE_COORDS = true;
$LATITUDE_GRID_SIZE_DEG = 30;
$LONGITUDE_GRID_SIZE_DEG = 30;

// Function for dividing up location data into key strings
function location_string($country, $lat, $lon) {
	global $USE_COORDS, $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;

	if ($USE_COORDS)
	{
		$lat_a = floor($lat / $LATITUDE_GRID_SIZE_DEG) * $LATITUDE_GRID_SIZE_DEG;
		$lat_b = $lat_a + $LATITUDE_GRID_SIZE_DEG;
		$grid_lat = $lat_a . 'to' . $lat_b;
		$lon_a = floor($lon / $LONGITUDE_GRID_SIZE_DEG) * $LONGITUDE_GRID_SIZE_DEG;
		$lon_b = $lon_a + $LONGITUDE_GRID_SIZE_DEG;
		$grid_lon = $lon_a . 'to' . $lon_b;
		return 'lat_' . $grid_lat . '_lon_' . $grid_lon;
	} else {
		return $country;
	}
}

// Get command line args
// List of arguments:
// 1. Taxonomic group as understood by BOLD (required)
// 2. Sample number of taxa to choose and build a tree with for each location
// 3. Path to Clustal executable
// 4. Path to PAUP executable

if(!isset($argc)) {
	exit("argc and argv disabled");
}

switch($argc) {
	case 5:
		$PAUP_PATH = $argv[4];
	case 4:
		$CLUSTAL_PATH = $argv[3];
	case 3:
		$SAMPLE_NUMBER = $argv[2];		
	case 2:
		$taxon_group = $argv[1];
	break;
	case 1:
		exit("No arguments given");
	break;
	default:
		exit("Wrong number of arguments given");
}


// Set up paths to sequences

$SEQUENCES_FOLDER = getcwd() . DIRECTORY_SEPARATOR . $taxon_group . '_sequences';
if(!is_dir($SEQUENCES_FOLDER)) {mkdir($SEQUENCES_FOLDER);}
$SEQUENCES_PATH = $SEQUENCES_FOLDER . DIRECTORY_SEPARATOR;
$SEQUENCES_LIST_FILE_DELIMITER = ',';

$sequences_list_file = $taxon_group . '_sequences.csv';

function sequence_file($location) {
	global $SEQUENCES_PATH;
	return $SEQUENCES_PATH . $location . '.fas';
}


// Functions to do the alignment and tree work

function align($infile) {
	global $CLUSTAL_PATH;

	$command = $CLUSTAL_PATH 
	. ' -INFILE=' . $infile 
	. ' -QUICKTREE -OUTORDER=INPUT -OUTPUT=NEXUS' 
	. " 1>" . $basename . '_CLUSTALW.log';
	system($command);

	$outfile = preg_replace('/\.fas/i', '.nxs', $infile);
	$outfile_ = preg_replace('/_sample/i', '', $outfile);
	if(rename($outfile, $outfile_)) {
		return $outfile_;
	}
}

function make_tree($infile) {
	global $PAUP_PATH;

	// First build the nexus file of commands
	// TODO #3 this should be in a separate file for easier maintenance
	$nexus = file_get_contents($infile) . '
	[PAUP block]
	begin paup;
		set autoclose=yes warntree=no warnreset=no;
		[root trees at midpoint]
		set rootmethod=midpoint;
		set outroot=monophyl;
		[construct tree using neighbour-joining]
		nj;
		[ensure branch lengths are output as substituions per nucleotide]
		set criterion=distance;
		[write rooted trees in Newick format with branch lengths]
		savetrees format=nexus root=yes brlen=yes replace=yes;
		quit;
	end;
	';

	$basename = preg_replace('/\..*$/', '', $infile);
	$nex_filename = $basename . '.nex';
	file_put_contents($nex_filename, $nexus);

	// Run PAUP
	$command =  $PAUP_PATH . ' ' . $nex_filename .  " 1>" . $basename . '_PAUP.log';

	system($command);

	if(file_exists($basename . '.tre')) {
		return $basename . '.tre';
	}
}

function tree_length($tree_filename) {
	$tree = file_get_contents($tree_filename);

	// Find Newick tree within file
	$tree_regex = '/\s*tree\s+\'.*\'\s+=.*;/i';
	preg_match($tree_regex, $tree, $tree_line);
	if(!count($tree_line)) {
		exit('Tree not found in output file!');
	}

	// Get branch lengths array e.g. ((":0.0938", ":0.0013")) etc and sum them
	$branch_length_regex = '/:\d+[\.,]?\d*/';
	preg_match_all($branch_length_regex, $tree_line[0], $branch_lengths);
	if(!count($branch_lengths[0])) {
		exit('No branch lengths in tree!');
	}

	$sum = 0;
	foreach ($branch_lengths[0] as $len)
	{
		$sum += substr($len, 1); // first char is ':'
	}
	return $sum;
}

// First get hold of the sequences, if they're not downloaded already

// TODO #4 the sequences should only be downloaded once for potentially different geographical division schemes
if(!file_exists($sequences_list_file)) {
	echo('List of sequence files not found, downloading sequences for ' . $taxon_group . PHP_EOL);

	// Open stream handle
	$bold_query = 'http://www.boldsystems.org/index.php/API_Public/combined?' . 
		'format=tsv&marker=' . $MARKER . '&taxon=' . $taxon_group;	
	$source_handle = fopen($bold_query, 'r');

	// Get header for indexing columns
	$tsv = array_flip(fgetcsv($source_handle, 0, "\t"));

	$locations = array();
	$sequence_counts = array();
	$sequence_count_total = 0;

	// Step through stream line by line
	while ($fields = fgetcsv($source_handle, 0, "\t"))
	{
		// Check marker is the one we want to use
		if($fields[$tsv['markercode']] != $MARKER) {continue;}

		// Check location is present: either coordinates or country
		if($USE_COORDS) {
			if(($lat = $fields[$tsv['lat']]) == '') {continue;}
			if(($lon = $fields[$tsv['lon']]) == '') {continue;}
		} else {
			if(($country = $fields[$tsv['country']]) == '') {continue;}
		}
		$loc = location_string($country, $lat, $lon);

		// Get handle to sequence file if not open yet
		if(!array_key_exists($loc, $locations)) {
			$locations[$loc] = fopen(sequence_file($loc), 'w');
		}

		// Make sequence header
		if (($id = $fields[$tsv['processid']]) == '') {continue;}
		$id = preg_replace('/-/', '_', $id);
		if (($species = $fields[$tsv['species_name']]) != '') {
			$sequence_header = preg_replace('/[- ]/', '_', $species) . '|' . $id;
		} else if (($genus = $fields[$tsv['genus_name']]) != '') {
			$sequence_header = $genus . '_sp|' . $id;
		} else {
			$sequence_header = $id;
		}
		$sequence_header = '>' . $sequence_header . PHP_EOL;

		// Get sequence and write to file after header
		if (($seq = $fields[$tsv['nucleotides']]) == '') {continue;}
		fwrite($locations[$loc], $sequence_header);
		fwrite($locations[$loc], $seq . PHP_EOL);

		$sequence_counts[$loc]++;
		$sequence_count_total++;
		if($sequence_count_total % 500 == 0) {
			echo("Saved " . $sequence_count_total . ' sequences...' . PHP_EOL);
		}
	}

	// Close all open files, and save the paths and sequence counts as csv
	$list_handle = fopen($sequences_list_file, 'w');
	fwrite($list_handle, 
		'location' . $SEQUENCES_LIST_FILE_DELIMITER
		. 'path' . $SEQUENCES_LIST_FILE_DELIMITER
		. 'count' . PHP_EOL);
	foreach ($locations as $loc => $handle)
	{
		fclose($handle);
		fwrite($list_handle, 
			$loc . $SEQUENCES_LIST_FILE_DELIMITER 
			. sequence_file($loc) . $SEQUENCES_LIST_FILE_DELIMITER
			. $sequence_counts[$loc] . PHP_EOL);
	}
	fclose($list_handle);
	fclose($source_handle);
} else {
	echo('List of sequence files found for ' . $taxon_group . PHP_EOL);
}

// Next, create samples and build trees.

// For each file of sequences listed in sequences_list_file,
$list_handle = fopen($sequences_list_file, 'r');
$col = array_flip(fgetcsv($list_handle));
while($loc = fgetcsv($list_handle))
{
	// Check that there are enough sequences to sample
	if(($seq_count = $loc[$col['count']]) <= $SAMPLE_NUMBER) {
		echo('Location ' . $loc[$col['location']] . ' is too small to sample.' . PHP_EOL);
		continue;
	}

	// Generate SAMPLE_NUMBER random numbers between 0 and sequence count
	$sample_indices = array();
	for ($i = 0; $i < $SAMPLE_NUMBER; $i++)	{
		do {
			$r = mt_rand(0, $seq_count- 1);
		} while (in_array($r, $sample_indices));
		array_push($sample_indices, $r);
	}

	// Go through the file and copy the sequences occurring at those indices into a sample file
	$sequence_file_path = $loc[$col['path']];
	$sequence_file = fopen($sequence_file_path, 'r');
	$sample_file_path = preg_replace('/\.fas/i', '_sample.fas', $sequence_file_path);
	$sample_file = fopen($sample_file_path, 'w');

	echo('Sampling sequences from ' . $sequence_file_path . PHP_EOL);

	$i = -1;
	while($line = fgets($sequence_file)) {

		if(preg_match('/\>/', $line)){
			// header
			$i++;
		}
		if(in_array($i, $sample_indices)) {
			fwrite($sample_file, $line);
		}
	}
	fclose($sample_file);
	fclose($sequence_file);


	// Now align and generate a tree for this taxon
	echo('Making alignment and tree from ' . $sample_file_path . PHP_EOL);
	$tree = make_tree(align($sample_file_path));
	echo('Tree branch sum: ' . tree_length($tree) . PHP_EOL);

}

?>