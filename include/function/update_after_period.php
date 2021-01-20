<?php

function update_after_period(
    float $period, // in seconds
    float &$prev_time, // in seconds
    bool $is_cli, 
    $data, // string if $is_cli, otherwise json encodable object/array/etc
    string $event = 'update'
) {
    $next_time = microtime(true);
    if (($next_time - $prev_time) < $period) return;

    $prev_time = $next_time;
    if ($is_cli) echo $data.PHP_EOL;
    else {
        echo "event: {$event}\n";
        $json = json_encode($data);
        echo "data: {$json}\n\n";
        ob_flush();
        flush();
    }
}

?>
