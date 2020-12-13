<?php

// Class to store names of fields in sequence_sets file
class SETS
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

?>
