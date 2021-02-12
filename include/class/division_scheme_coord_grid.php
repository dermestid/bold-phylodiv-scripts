<?php

require_once '../include/class/division_scheme_coord.php';
require_once '../include/class/bold.php';

class Division_Scheme_Coord_Grid extends Division_Scheme_Coord
{
    public const KEY_BASE = 'COORD-GRID';
    public const REQUIRED_FIELDS = [BOLD::LATITUDE, BOLD::LONGITUDE];

    public static function read(string $key) {
        $parts = explode('_', $key);
        if ((count($parts) === 2) && ($parts[0] === self::KEY_BASE)) {
            $sizes = explode('x', $parts[1]);
            if (count($sizes) === 2)
                return new Division_Scheme_Coord_Grid($sizes[0], $sizes[1]);
        }
        return false;
    }

    public float $size_lat;
    public float $size_lon;

    public function required_fields() {
        return self::REQUIRED_FIELDS;
    }

    public function get_key() {
        return self::KEY_BASE."_{$this->size_lat}x{$this->size_lon}";
    }

    public function locate($fields, $cols) {
        $lat = floatval($fields[$cols[BOLD::LATITUDE]]);
        $lon = floatval($fields[$cols[BOLD::LONGITUDE]]);

        $lat_min = floor($lat / $this->size_lat) * $this->size_lat;
		$lat_max = $lat_min + $this->size_lat;
		$grid_lat = $lat_min . 'to' . $lat_max;
		$lon_min = floor($lon / $this->size_lon) * $this->size_lon;
		$lon_max = $lon_min + $this->size_lon;
        $grid_lon = $lon_min . 'to' . $lon_max;

        return [
            'key' => "lat_{$grid_lat}_lon_{$grid_lon}",
            'scheme' => $this->get_key(),
            'data' => [
                'lat_min' => $lat_min,
                'lat_max' => $lat_max,
                'lon_min' => $lon_min,
                'lon_max' => $lon_max
        ]];
    }
   
    private const MINIMUM_LATITUDE = -90;
    private const MINIMUM_LATITUDE_NONPOLAR = -60;
    private const MAXIMUM_LATITUDE = 90;
    private const MAXIMUM_LATITUDE_NONPOLAR = 70;
    private const MINIMUM_LONGITUDE = -180;
    private const MAXIMUM_LONGITUDE = 180;

    public function locations_g(bool $include_poles = true) {
        $min_latitude = $include_poles ? self::MINIMUM_LATITUDE : self::MINIMUM_LATITUDE_NONPOLAR;
        $max_latitude = $include_poles ? self::MAXIMUM_LATITUDE : self::MAXIMUM_LATITUDE_NONPOLAR;
        foreach (
            range(
                $min_latitude, 
                $max_latitude - $this->size_lat, 
                $this->size_lat
            ) as $lat_min
        ) {
            foreach (
                range(
                    self::MINIMUM_LONGITUDE, 
                    self::MAXIMUM_LONGITUDE - $this->size_lon, 
                    $this->size_lon
                ) as $lon_min
            ) {
                $lat_max = $lat_min + $this->size_lat;
                $grid_lat = $lat_min . 'to' . $lat_max;
                $lon_max = $lon_min + $this->size_lon;
                $grid_lon = $lon_min . 'to' . $lon_max;
        
                yield [
                    'key' => "lat_{$grid_lat}_lon_{$grid_lon}",
                    'scheme' => $this->get_key(),
                    'data' => [
                        'lat_min' => $lat_min,
                        'lat_max' => $lat_max,
                        'lon_min' => $lon_min,
                        'lon_max' => $lon_max
                ]];
            }
        }
    }

    public function __construct($size_lat, $size_lon) {
        $this->size_lat = $size_lat;
        $this->size_lon = $size_lon;
    }
}

?>