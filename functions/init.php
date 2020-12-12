<?php

require_once 'coord_grid.php';
require_once 'bold.php';

$COORD_GRID;
$DIVISION_SCHEME;
    
// set argument-dependent global variables
function init() {
    global $COORD_GRID, $DIVISION_SCHEME;
    global $LATITUDE_GRID_SIZE_DEG, $LONGITUDE_GRID_SIZE_DEG;

    $COORD_GRID = new Coord_Grid();
    $COORD_GRID->params = array(
        Coord_Grid::SIZE_LAT => $LATITUDE_GRID_SIZE_DEG,
        Coord_Grid::SIZE_LON => $LONGITUDE_GRID_SIZE_DEG
    );
    $DIVISION_SCHEME = new Division_scheme(Division_scheme::COORDS, array(BOLD::LATITUDE, BOLD::LONGITUDE));
}

?>