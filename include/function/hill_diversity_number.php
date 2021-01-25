<?php

// Returns a function which calculates diversity using hill numbers.
// order is restricted to an int due to floats not comparing well.
// In future, order PHP_INT_MIN/MAX should be interpreted as positive/negative infinity.
//
// resulting function takes an iterator $gen with values ['count' => $count, 'total' => $total]
// where $count is the (absolute) abundance of some species and $total is the sum of all abundances
// so that species relative abundance (probability of sampling) is $count / $total.
//
// diversity of an empty or throwing iterator is null.
function hill_diversity_number (int $hill_order) {
    switch ($hill_order) {
        case 0:
            return function ($gen) {
                $i = 0;
                try {
                    foreach ($gen as $y) $i++;
                } catch(Exception $e) { return null; }
                if ($i === 0) return null;
                else return $i;
            };
        case 1:
            return function ($gen) {
                $shannon_wiener_index = 0;
                try {
                    foreach ($gen as ['count' => $count, 'total' => $total]) {
                        $p = $count / $total;
                        $shannon_wiener_index -= ($p * log($p));
                    }
                } catch(Exception $e) { return null; }
                if ($shannon_wiener_index === 0) return null;
                else return exp($shannon_wiener_index);
            };
        case 2:
            return function ($gen) {
                $simpson_index = 0;
                try {
                    foreach ($gen as ['count' => $count, 'total' => $total]) {
                        $p = $count / $total;
                        $simpson_index += ($p * $p);
                    }
                } catch(Exception $e) { return null; }
                if ($simpson_index === 0) return null;
                else return (1 / $simpson_index);
            };
        default:
            return function ($gen) {
                $hill_sum = 0;
                try {
                    foreach ($gen as ['count' => $count, 'total' => $total]) {
                        $p = $count / $total;
                        $hill_sum += ($p ** $hill_order);
                    }
                } catch(Exception $e) { return null; }
                if ($hill_sum === 0) return null;
                else {
                    $hill_exp = (1 / (1 - $hill_order));
                    return ($hill_sum ** $hill_exp); }
            };
    } // end switch
}

?>
