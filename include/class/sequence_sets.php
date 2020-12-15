<?php 

require_once $CLASS_DIR. 'data_file.php';
require_once $CLASS_DIR. 'sets.php';
require_once $CLASS_DIR. 'division_scheme.php';
require_once $FUNCTION_DIR. 'get_sequence_file.php';
require_once $CLASS_DIR. 'location.php';

// Wraps Data_File management functions with additional checks
class Sequence_Sets
{
    private Data_File $data_file;
    
    private string $path;
    private array $header;
    
    private bool $make_new_file;
    
    // Copy in memory of what's in the file
    // TODO: 
    // at present, this is only what's been written in the lifetime of this object,
    // so may potentially duplicate what's already in the file.
    // But for simplicity, it is currently left to the user of the class not to duplicate
    // file contents.
    private array $sets = array();

    // Numerical array of different division_schemes in use
    // Lets us know what fields we will need to store
    private array $division_schemes = array();

    // Associative array of counts of sequences for different taxa
    // At present one taxon is stored per file, so this should have only one entry
    private array $total_sequence_count = array();

    // factory construct only
    private function __construct(
        string $path_,
        string $delim_,
        bool $make_new_file_
    ) {
        $this->path = $path_;
        $this->delim = $delim_;
        $this->make_new_file = $make_new_file_;
    } 

    public static function get_file($taxon) {
        global $SETS_DIR;
        return $SETS_DIR . $taxon . '_sets.csv';
    }

    public static function open(
        string $taxon, 
        string $delim,
        bool $make_new_file = false
    ) {
        $s = new Sequence_Sets(self::get_file($taxon), $delim, $make_new_file);
        $s->header = SETS::FIELDS;
        if (!$s->make_new_file) {
            // get all the data that's there already
            // TODO
            // $s->read_sets();
        }
        return $s;
    } // end function open

    // Reads in the saved locations for a given taxon under a given division scheme
    public static function get_locations($taxon, $division_scheme, $delim) {

        $s = self::open($taxon, $delim);
        $s->data_file = Data_File::open($s->path, $s->delim, $s->header);

        $location_data_cols = array();
        foreach ($division_scheme->saved_params as $field) {
            $i = array_search($field, $s->header, true);
            if ($i !== false) {
                array_push($location_data_cols, $i);
            }
        }

        $start_pos = $s->data_file->tell();
        $i_taxon = array_search(SETS::TAXON, $s->header, true);
        $i_location = array_search(SETS::LOCATION, $s->header, true);
        $i_division_scheme = array_search(SETS::DIVISION_SCHEME, $s->header, true);
        $locations = array();

        do {
            $entry = $s->data_file->read_entry();
            if (!$entry) { // eof
                $s->data_file->go_to_start();
            } else {
                if ($entry[$i_taxon] !== $taxon) { continue; }
                if ($entry[$i_division_scheme] !== $division_scheme->key) { continue; }

                $key = $entry[$i_location];
                $data = array();
                foreach ($location_data_cols as $col) {
                    $data[$s->header[$col]] = $entry[$col];
                }
                $loc = Location::load(
                    $division_scheme, 
                    $data, 
                    $entry[$i_location]);
                $locations[$key] = $loc;
            }
        } while ($s->data_file->tell() !== $start_pos);

        return $locations;
    } //  end function get_locations

    // Read through all entries until we find a matching taxon and optionally location
    // Return the entry, or if $field is not null, return that field of the entry
    // If no matching entry is found, return false
    public function get_entry(string $taxon, string $field = null, string $loc_key = null) {

        $this->data_file = Data_File::open($this->path, $this->delim, $this->header);

        $start_pos = $this->data_file->tell();
        $i_taxon = array_search(SETS::TAXON, $this->header, true);
        if ($loc_key !== null) {
            $i_location = array_search(SETS::LOCATION, $this->header, true);
        }

        $found = false;
        do {
            $entry = $this->data_file->read_entry();
            if (!$entry) { // eof
                $this->data_file->go_to_start();
            } else {
                $found = ($entry[$i_taxon] === $taxon);
                if ($loc_key !== null) {
                    $found = ($found && ($entry[$i_location] === $loc_key));
                }
                if ($found) { break; }
            }
        } while ($this->data_file->tell() !== $start_pos);

        Data_File::close($this->data_file);

        if (!$entry) {
            return false;
        } else if ($field !== null) {
            $i_field = array_search($field, $this->header, true);
            return $entry[$i_field];
        } else {
            return $entry;
        }
    }

    // Given an appropriately formatted associative array $seq_data, construct or update the entry
    // which houses the set of sequences for that location.
    // Nothing will be written to file yet: call write_updates() to commit the changes.
    // Returns the location key for the new or changed entry.
    public function update_set(
        string $taxon,
        array $seq_data, 
        int $seq_index, 
        Division_Scheme $division_scheme
    ) {
        global $TAXSET_DELIMITER;

        try{
            // throws if entry doesn't have the right fields for $division_scheme
            $loc = Location::read($division_scheme, $seq_data);
        } catch (LocationException $e) {
            return false;
        }    

        if (!in_array($division_scheme, $this->division_schemes, true)) {
            array_push($this->division_schemes, $division_scheme);
        }

        if(!isset($this->sets[$loc->key])) {
            $this->sets[$loc->key] = array(
                'taxon' => $taxon,
                'sequence_count' => 0,
                'taxset' => '',
                'location' => $loc
            ); 
        }
        $this->sets[$loc->key]['sequence_count']++;

        // Add the index to the taxset
        if ($this->sets[$loc->key]['sequence_count'] > 1) {
            $this->sets[$loc->key]['taxset'] .= $TAXSET_DELIMITER; 
        }
        $this->sets[$loc->key]['taxset'] .= $seq_index;

        return $loc->key;
    } // end function update_set

    public function update_sequence_count(string $taxon, int $count) {
        $this->total_sequence_count[$taxon] = $count;
    }

    public function write_updates(string $return_values_ = null) {

        // Update header to include all fields saved by locations
        foreach ($this->division_schemes as $scheme) {
            foreach ($scheme->saved_params as $field) {
                if (!in_array($field, $this->header)) {
                    array_push($this->header, $field);
                }
            }
        }

        if ($this->make_new_file) {
            $this->data_file = Data_File::open_new($this->path, $this->delim, $this->header);
            $this->make_new_file = false;
        } else {
            $this->data_file = Data_File::open($this->path, $this->delim, $this->header);
        }

        $values = array();

        foreach ($this->sets as $loc_key => $data) {
            $entry = array_combine(SETS::FIELDS, array(
                $data['taxon'],
                $this->total_sequence_count[$data['taxon']],
                $data['location']->scheme->key,
                $loc_key,
                $data['sequence_count'],
                get_sequence_file($data['taxon']),
                $data['taxset']
            ));
            $entry = array_merge($entry, $data['location']->data);

            if (array_key_exists($return_values_, $data)) {
                $values[$loc_key] = $data[$return_values_];
            }

            $this->data_file->write_entry_assoc($entry);
        }
        Data_File::close($this->data_file);
        return $values;
    } // end function write_updates
}


?>

