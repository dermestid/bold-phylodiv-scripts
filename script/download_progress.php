<?php

// Call this script to get back a status message/object regarding a download
// Expects a process id provided as commandline/GET argument,
// Followed by the args given to $MAIN_SCRIPT

require_once __DIR__.'/../include/require_script.php';

require_once $CONFIG_DIR. 'default_args.php'; // $ARGS
require_once $CLASS_DIR. 'status.php';
require_once $CLASS_DIR. 'download.php';

$pid = 0;
if ($CLI) {
    $args = array_merge(array('pid' => &$pid), $ARGS);
    get_cli_args($args);
} else if (isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    get_url_args($ARGS);
    $args = array_merge(array('pid' => &$pid), $ARGS);
} else {
    Status::no_args($DOWNLOAD_PROGRESS_SCRIPT);
    exit;
}

if (Download::status($pid)) {
    Status::downloading($args);
    exit;
} else {
    unset($args['pid']);
    Status::download_done($args);
    exit;
}

?>
