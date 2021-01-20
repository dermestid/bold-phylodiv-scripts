<?php

function both(array $ar, Iterator $iter) {
    $m = new MultipleIterator();
    $ar_o = new ArrayObject($ar);
    $m->attachIterator($ar_o->getIterator());
    $m->attachIterator($iter);
    return $m;
}

?>
