<?php

require_once '../include/class/division_scheme_coord.php';
require_once '../include/class/bold.php';

class Division_Scheme_Equal_Area_Squares extends Division_Scheme_Coord
{
    public const KEY_BASE = 'EQUAL-AREA';

    public static function read(string $key) {
        $parts = explode('_', $key);
        if ((count($parts) === 2) && ($parts[0] === self::KEY_BASE)) {
            $args = explode('+', $parts[1], 3);
            if (count($args) === 3) {
                [$size, $off_lat, $off_lon] = $args;
                return new Division_Scheme_Equal_Area_Squares($size, $off_lat, $off_lon);
            }
        }
        return false;
    }

    public float $size_lon;
    public float $n_lat_divs;
    public float $fraction_res;

    public float $off_lat;
    public float $off_lon;
    public float $offset_lat_fraction;
    public float $offset_lon;

    public function __construct($size_lon, $off_lat, $off_lon) {
        $this->size_lon = $size_lon;
        $this->n_lat_divs = 90 / $this->size_lon;
        $this->fraction_res = 1 / $this->n_lat_divs;
        $this->off_lat = $off_lat;
        $this->off_lon = $off_lon;
        $this->offset_lat_fraction = $this->fraction_res * $this->off_lat;
        $this->offset_lon = $this->size_lon * $this->off_lon;
    }

    public function get_key() {
        return self::KEY_BASE."_{$this->size_lon}+{$this->off_lat}+{$this->off_lon}";
    }

    private const MINIMUM_LATITUDE_FRACTION = -1;
    private const MAXIMUM_LATITUDE_FRACTION = 1;

    public function locate($fields, $cols) {
        $lat = floatval($fields[$cols[BOLD::LATITUDE]]);
        $lon = floatval($fields[$cols[BOLD::LONGITUDE]]);

        $lon_min = $this->offset_lon + floor($lon / $this->size_lon) * $this->size_lon;
		$lon_max = $lon_min + $this->size_lon;
        $grid_lon = $lon_min . 'to' . $lon_max;

        $lat_fraction = sin(deg2rad($lat));
        $lat_frac_min = max(
            self::MINIMUM_LATITUDE_FRACTION, 
            $this->offset_lat_fraction 
               + floor($lat_fraction * $this->n_lat_divs) * $this->fraction_res);
        $lat_frac_max = min(
            self::MAXIMUM_LATITUDE_FRACTION, 
            $lat_frac_min + $this->fraction_res);

        $lat_min = round(rad2deg(asin($lat_frac_min)), 2);
		$lat_max = round(rad2deg(asin($lat_frac_max)), 2);
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

    private const MINIMUM_LONGITUDE = -180;
    private const MAXIMUM_LONGITUDE = 180;

    public function locations_g() {
        foreach (
            range(
                $this->offset_lat_fraction + self::MINIMUM_LATITUDE_FRACTION, 
                $this->offset_lat_fraction + self::MAXIMUM_LATITUDE_FRACTION - $this->fraction_res, 
                $this->fraction_res
            ) as $lat_frac_min
        ) {
            foreach (
                range(
                    $this->offset_lon + self::MINIMUM_LONGITUDE, 
                    $this->offset_lon + self::MAXIMUM_LONGITUDE - $this->size_lon, 
                    $this->size_lon
                ) as $lon_min
            ) {
                $lat_frac_min = max(
                    self::MINIMUM_LATITUDE_FRACTION,
                    $lat_frac_min);
                $lat_frac_max = min(
                    self::MAXIMUM_LATITUDE_FRACTION,
                    $lat_frac_min + $this->fraction_res);
                $lat_min = round(rad2deg(asin($lat_frac_min)), 2);
                $lat_max = round(rad2deg(asin($lat_frac_max)), 2);
                $grid_lat = $lat_min . 'to' . $lat_max;
                
                $lon_min = max(
                    self::MINIMUM_LONGITUDE,
                    $lon_min);
                $lon_max = min(
                    self::MAXIMUM_LONGITUDE,
                    $lon_min + $this->size_lon);
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