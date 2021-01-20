<?php

require_once '../include/function/get_data_g.php';

$taxa = [];
foreach(get_data_g(['taxon']) as $taxon) {
    $taxa[] = $taxon;
}
$json = json_encode($taxa);

echo $json;

?>
