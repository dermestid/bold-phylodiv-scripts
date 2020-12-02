<?php

require_once $FUNCTIONS_DIR. 'division_scheme.php';

class LocationException extends Exception 
{
}

class Location
{
    public division_scheme $scheme;
    public array $data;
    public string $key;

    public function __construct($scheme_) {
        $this->scheme = $scheme_;
    }

    public static function load($scheme_, $data_, $key_) {
        $loc = new Location($scheme_);
        $loc->data = $data_;
        $loc->key = $key_;

        return $loc;
    }

    public static function read($scheme_, $entry) {
        $loc = new Location($scheme_);

        if ($scheme_->scheme === division_scheme::COORDS) {
            [$loc->data, $loc->key] = self::setup_coords($scheme_, $entry);
        } else if ($scheme_->scheme === division_scheme::COUNTRY) {
            [$loc->data, $loc->key] = self::setup_country($scheme_, $entry);
        } else {
            exit("Unimplemented division_scheme {$scheme_->scheme} requested in Location::read()");
        }
        return $loc;
    }

    private static function setup_coords($scheme_, $entry) {
        if (array_search(BOLD::LATITUDE, $scheme_->bold_params) === false) {
            exit("division_scheme {$scheme_->key} set up wrong; Location::read() expected param ".BOLD::LATITUDE);
        }
        if (array_search(BOLD::LONGITUDE, $scheme_->bold_params) === false) {
            exit("division_scheme {$scheme_->key} set up wrong; Location::read() expected param ".BOLD::LONGITUDE);
        }

        if (!key_exists(BOLD::LATITUDE, $entry) || $entry[BOLD::LATITUDE] == '') {
            throw new LocationException(
                "Bad entry passed to Location::read(); missing expected field ".BOLD::LATITUDE);
        }
        if (!key_exists(BOLD::LONGITUDE, $entry) || $entry[BOLD::LONGITUDE] == '') {
            throw new LocationException(
                "Bad entry passed to Location::read(); missing expected field ".BOLD::LONGITUDE);
        }

        $lat = $entry[BOLD::LATITUDE];
        $lon = $entry[BOLD::LONGITUDE];

        $size_lat = $scheme_->arg_data[Coord_grid::SIZE_LAT];
        $size_lon = $scheme_->arg_data[Coord_grid::SIZE_LON];

        $lat_min = floor($lat / $size_lat) * $size_lat;
		$lat_max = $lat_min + $size_lat;
		$grid_lat = $lat_min . 'to' . $lat_max;
		$lon_min = floor($lon / $size_lon) * $size_lon;
		$lon_max = $lon_min + $size_lon;
        $grid_lon = $lon_min . 'to' . $lon_max;
        
        $d = array_combine(Division_scheme::COORD_PARAMS, array(
            $lat_min, $lat_max, ($lat_min + $lat_max)/2.0,
            $lon_min, $lon_max, ($lon_min + $lon_max)/2.0
        ));
        $k = "lat_{$grid_lat}_lon_{$grid_lon}";

        return array($d, $k);
    }

    private static function setup_country($scheme_, $entry) {
        if (array_search(BOLD::COUNTRY, $scheme_->bold_params) === false) {
            throw new LocationException(
                "division_scheme {$scheme_->key} set up wrong; Location::read() expected param ".BOLD::COUNTRY);
        }
        if (!key_exists(BOLD::COUNTRY, $entry) || $entry[BOLD::COUNTRY] == '') {
            throw new LocationException(
                "Bad entry passed to Location::read(); missing expected field ".BOLD::COUNTRY);
        }

        $d = array(BOLD::COUNTRY => $entry[BOLD::COUNTRY]);
        $k = $entry[BOLD::COUNTRY];
        return array($d, $k);
    }
}

// // Returns a string encoding the location of the specimen represented by array $entry.
// // Expects $entry to be in the format of a BOLD specimen record,
// // and $fields should be an array of strings describing the fields of $entry (ie, the table header).
// // The output of this function for given input varies with $DIVISION_SCHEME.
// function get_geo_division($entry, $fields) {
//     global $DIVISION_SCHEME;
//     global $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;

//     $col = array_flip($fields);

//     // Check location is present: either coordinates or country
//     $country = $lat = $lon = '';
//     if ($DIVISION_SCHEME->scheme === division_scheme::COORDS) {
//         if (($lat = $entry[$col[BOLD::LATITUDE]]) == '') { return false; }
//         if (($lon = $entry[$col[BOLD::LONGITUDE]]) == '') { return false; }

//         $lat_a = floor($lat / $LATITUDE_GRID_SIZE_DEG) * $LATITUDE_GRID_SIZE_DEG;
// 		$lat_b = $lat_a + $LATITUDE_GRID_SIZE_DEG;
// 		$grid_lat = $lat_a . 'to' . $lat_b;
// 		$lon_a = floor($lon / $LONGITUDE_GRID_SIZE_DEG) * $LONGITUDE_GRID_SIZE_DEG;
// 		$lon_b = $lon_a + $LONGITUDE_GRID_SIZE_DEG;
// 		$grid_lon = $lon_a . 'to' . $lon_b;
// 		return 'lat_' . $grid_lat . '_lon_' . $grid_lon;
//     } else if ($DIVISION_SCHEME->scheme === division_scheme::COUNTRY) {
//         if (($country = $entry[$col[BOLD::COUNTRY]]) == '') { return false; }
//         return $country;
//     } else {
//         return false;
//     }
// }

?>


