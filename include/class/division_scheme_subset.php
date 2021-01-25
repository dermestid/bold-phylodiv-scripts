<?php

require_once '../include/class/division_scheme_coord.php';
require_once '../include/class/division_scheme_coord_grid.php';
require_once '../include/class/bold.php';

class Division_Scheme_Subset extends Division_Scheme_Coord
{
    public static function get(Division_Scheme_Coord_Grid $super, string $loc_str) {
        // Split X,Y/Z,W_A,B/C,D into [[[X,Y],[Z,W]],[[A,B],[C,D]]] etc
        $rects = array_map(
            fn($rect) => array_map(
                fn($point) => array_map(
                    'floatval',
                    explode(',', $point)
                ),
                explode('/', $rect)
            ), 
            explode('_', $loc_str)
        );
        if (count($rects[0]) !== 2) return false;
        else return new Division_Scheme_Subset($super, $rects, $loc_str);
    }

    private Division_Scheme_Coord_Grid $super;
    private array $rects;
    private string $loc_str;

    public function __construct(Division_Scheme_Coord_Grid $super, array $rects, string $loc_str) {
        $this->super = $super;
        $this->rects = $rects;
        $this->loc_str = $loc_str;
    }

    public function required_fields() {
        return $this->super->required_fields();
    }
    
    public const KEY_BASE = 'COORD-SUBSET';

    public function get_key() {
        return self::KEY_BASE."_{$this->loc_str}";
    }

    public function locate($fields, $cols) {
        return $this->super->locate($fields, $cols);
    }

    public function locations_g() {
        foreach ($this->rects as [$ul, $lr]) {
            $grid_lat = $lr[1] . 'to' . $ul[1];
            $grid_lon = $ul[0] . 'to' . $lr[0];
    
            yield [
                'key' => "lat_{$grid_lat}_lon_{$grid_lon}",
                'scheme' => $this->super->get_key(),
                'data' => [
                    'lat_min' => $lr[1],
                    'lat_max' => $ul[1],
                    'lon_min' => $ul[0],
                    'lon_max' => $lr[0]
            ]];
        }
    }
}

?>
