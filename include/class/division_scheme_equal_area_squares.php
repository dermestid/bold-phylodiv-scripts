<?php

require_once '../include/class/division_scheme_coord.php';
require_once '../include/class/bold.php';

class Division_Scheme_Equal_Area_Squares extends Division_Scheme_Coord
{
    public const KEY_BASE = 'EQUAL-AREA';

    public static function read(string $key) {
        $parts = explode('_', $key);
        if ((count($parts) === 2) && ($parts[0] === self::KEY_BASE)) {
            return new Division_Scheme_Equal_Area_Squares($parts[1]);
        }
        return false;
    }

    public float $size_lon;
    public float $n_lat_divs;
    public float $fraction_res;

    public function __construct($size_lon) {
        $this->size_lon = $size_lon;
        $this->n_lat_divs = 90 / $this->size_lon;
        $this->fraction_res = 1 / $this->n_lat_divs;
    }

    public function get_key() {
        return self::KEY_BASE."_{$this->size_lon}";
    }

    public function locate($fields, $cols) {
        $lat = floatval($fields[$cols[BOLD::LATITUDE]]);
        $lon = floatval($fields[$cols[BOLD::LONGITUDE]]);

        $lon_min = floor($lon / $this->size_lon) * $this->size_lon;
		$lon_max = $lon_min + $this->size_lon;
        $grid_lon = $lon_min . 'to' . $lon_max;

        $lat_fraction = sin(deg2rad($lat));
        $lat_fraction_lower = max(-1, floor($lat_fraction * $this->n_lat_divs) * $this->fraction_res);
        $lat_fraction_upper = min(1, $lat_fraction_lower + $this->fraction_res);

        $lat_min = round(rad2deg(asin($lat_fraction_lower)), 2);
		$lat_max = round(rad2deg(asin($lat_fraction_upper)), 2);
		$grid_lat = $lat_min . 'to' . $lat_max;


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

    private const MINIMUM_LATITUDE_FRACTION = -1;
    private const MAXIMUM_LATITUDE_FRACTION = 1;
    private const MINIMUM_LONGITUDE = -180;
    private const MAXIMUM_LONGITUDE = 180;

    public function locations_g() {
        foreach (
            range(
                self::MINIMUM_LATITUDE_FRACTION, 
                self::MAXIMUM_LATITUDE_FRACTION - $this->fraction_res, 
                $this->fraction_res
            ) as $lat_frac_min
        ) {
            foreach (
                range(
                    self::MINIMUM_LONGITUDE, 
                    self::MAXIMUM_LONGITUDE - $this->size_lon, 
                    $this->size_lon
                ) as $lon_min
            ) {
                $lat_min = round(rad2deg(asin($lat_frac_min)), 2);
                $lat_max = round(rad2deg(asin($lat_frac_min + $this->fraction_res)), 2);
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
}

?>