<?php

require_once '../include/class/division_scheme.php';
require_once '../include/class/bold.php';

class Division_Scheme_Country extends Division_Scheme
{
    public const KEY = 'COUNTRY';
    public const REQUIRED_FIELDS = [BOLD::COUNTRY];

    public static function key() {
        return self::KEY;
    }

    public function required_fields() {
        return self::REQUIRED_FIELDS;
    }

    public function get_key() {
        return self::KEY;
    }

    public function locate($fields, $cols) {
        return [
            "key" => $fields[$cols[BOLD::COUNTRY]],
            "scheme" => $this->get_key(),
            "data" => []
        ];
    }
}

?>
