<?php

require_once $CLASS_DIR. 'coord_grid.php';
require_once $CLASS_DIR. 'division_scheme.php';
require_once $CLASS_DIR. 'bold.php';

$COORD_GRID;
$DIVISION_SCHEME;
    
// set argument-dependent global variables
function init_args() {
    global $COORD_GRID, $DIVISION_SCHEME;
    global $LAT_GRID_DEG, $LON_GRID_DEG;

    $COORD_GRID = new Coord_Grid();
    $COORD_GRID->params = array(
        Coord_Grid::SIZE_LAT => $LAT_GRID_DEG,
        Coord_Grid::SIZE_LON => $LON_GRID_DEG
    );
    $DIVISION_SCHEME = new Division_Scheme(Division_Scheme::COORDS, array(BOLD::LATITUDE, BOLD::LONGITUDE));
}

?>