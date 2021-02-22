<?php

require_once '../include/class/division_scheme.php';

abstract class Division_Scheme_Coord extends Division_Scheme
{
    public const REQUIRED_FIELDS = [BOLD::LATITUDE, BOLD::LONGITUDE];
    
    public function required_fields() {
        return self::REQUIRED_FIELDS;
    }

    public abstract function locations_g();
}

?>
