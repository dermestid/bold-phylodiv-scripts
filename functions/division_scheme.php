<?php

class Division_scheme
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
            exit ('Unimplemented location division scheme requested');
        }
    }

    private static function setup_coords() {
        global $COORD_GRID;

        $a = $COORD_GRID->params;
        $k = self::COORDS.'_'.$a[Coord_grid::SIZE_LAT].'x'.$a[Coord_grid::SIZE_LON];

        return array($k, $a);
    }
    private static function setup_country() {
        return array(self::COUNTRY, array());
    }
}

?>