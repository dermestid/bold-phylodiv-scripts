<?php

require_once $CLASS_DIR. 'coord_grid.php';
require_once $FUNCTION_DIR. 'get_sequence_file.php';
require_once $FUNCTION_DIR. 'total_sequence_count.php';
require_once $CLASS_DIR. 'sequence_sets.php';
require_once $CLASS_DIR. 'sequence_data.php';
require_once $FUNCTION_DIR. 'say.php';

class Division_Scheme
{
    const COORDS = 'COORDS';
    const COUNTRY = 'COUNTRY';
    const LAT_MIN = 'lat_min';
    const LON_MIN = 'lon_min';
    const LAT_MAX = 'lat_max';
    const LON_MAX = 'lon_max';
    const LAT_AVG = 'lat_avg';
    const LON_AVG = 'lon_avg';
    const COORD_PARAMS = array(
        self::LAT_MIN, self::LAT_MAX, self::LAT_AVG,
        self::LON_MIN, self::LON_MAX, self::LON_AVG
    );
    public string $scheme;
    public string $key;
    public array $arg_data; // local data (e.g. from command line args) which this division scheme uses 
    public array $bold_params; // the columns in the BOLD data which the division scheme is dependent on
    public array $saved_params; // the keys of Location::data using this division scheme
    public function __construct(string $scheme_, array $params) {

        $this->scheme = $scheme_;
        $this->bold_params = $params;
        if ($scheme_ === self::COORDS) {
            [$this->key, $this->arg_data] = self::setup_coords();
            $this->saved_params = self::COORD_PARAMS;
        } else if ($scheme_ === self::COUNTRY) {
            [$this->key, $this->arg_data] = self::setup_country();
            $this->saved_params = array(self::COUNTRY);
        } else {
            exit('Unimplemented location division scheme requested');
        }
    }

    // Sorts the downloaded sequences for $taxon into sets and adds them as entries 
    // to the sequence sets file, which is assumed to already exist with some entries.
    // Doesn't do a check if they're already in there, and will duplicate entries if they are.
    // Returns an array of the new geographical division names.
    function sort($taxon) {
        global $SEQUENCE_DATA_DELIMITER, $SETS_DATA_DELIMITER;

        if(!(file_exists($data_file = Sequence_Sets::get_file($taxon)))) { 
            exit("Error: non-existent {$data_file} requested by sort({$taxon})");
        }
        if(!(file_exists($sequence_file = get_sequence_file($taxon)))) {
            exit("Error: non-existent {$sequence_file} requested by sort({$taxon})");
        }
        if(!(file_exists($sequence_data_file = Sequence_Data::get_file($taxon)))) {
            exit("Error: non-existent {$sequence_data_file} requested by sort({$taxon})");
        }

        $sequence_index = 0;

        $sets = Sequence_Sets::open($taxon, $SETS_DATA_DELIMITER);
        $sequence_data = Sequence_Data::open($taxon, $SEQUENCE_DATA_DELIMITER);
        $sequences_handle = fopen($sequence_file, 'r');

        while($line = fgets($sequences_handle)) {
            // only count header lines:
            if ((trim($line))[0] != '>') { continue; }

            $sequence_index++;
            
            $sequence_data_entry = $sequence_data->get_entry($sequence_file, $sequence_index);
            if (!$sequence_data_entry) { continue; }

            $sets->update_set($taxon, $sequence_data_entry, $sequence_index, $this);

            // Update the user as this may take a while
            if ($sequence_index % 500 == 0) {
                say_verbose("Sorted {$sequence_index} into locations...");
            }
        } // end while loop
        fclose($sequences_handle);
        Sequence_Data::close($sequence_data);

        if ($sequence_index == 0) {
            exit("Error: sort({$taxon}) requested empty sequence file {$sequence_file}");
        }

        // Save data in a csv and return the new locations of the sets

        $total_sequence_count = total_sequence_count($taxon);
        $sets->update_sequence_count($taxon, $total_sequence_count);
        return $sets->write_updates('location');
    }

    private static function setup_coords() {
        global $COORD_GRID;

        $a = $COORD_GRID->params;
        $k = self::COORDS.'_'.$a[Coord_Grid::SIZE_LAT].'x'.$a[Coord_Grid::SIZE_LON];

        return array($k, $a);
    }
    private static function setup_country() {
        return array(self::COUNTRY, array());
    }
}

?>
