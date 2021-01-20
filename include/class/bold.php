<?php

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
}

?>
