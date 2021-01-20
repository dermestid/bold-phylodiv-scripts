<?php

require_once '../include/class/division_scheme_country.php';
require_once '../include/class/division_scheme_coord_grid.php';

abstract class Division_Scheme
{
    public static function all_required_fields() {
        $fields = array_merge(
            Division_Scheme_Country::REQUIRED_FIELDS,
            Division_Scheme_Coord_Grid::REQUIRED_FIELDS
        );
        return $fields;
    }
    
    // Factory function which returns a concrete Division_Scheme from its key
    public static function read(string $key) {
        if ($key === Division_Scheme_Country::key())
            return new Division_Scheme_Country();
        else if (($scheme = Division_Scheme_Coord_Grid::read($key)) !== false)
            return $scheme;
        else {
            // unimplemented
            return false;
        }
    }

    public abstract function required_fields();

    public abstract function get_key();

    public abstract function locate($fields, $cols);
}

?>
