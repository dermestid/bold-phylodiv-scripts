<?php

$APP_NAME = 'bold-phylodiv-scripts';
$WINDOWS = (stripos(PHP_OS, 'WIN') === 0);
$CLI = (stripos(PHP_SAPI, 'cli') === 0);

if ($CLI) $wd = getcwd(); else $wd = realpath(__DIR__.'/../../');

$OUT_DIR = $wd . DIRECTORY_SEPARATOR . 'out' . DIRECTORY_SEPARATOR;
if (!is_dir($OUT_DIR)) { mkdir($OUT_DIR); }
$TEMP_DIR = $OUT_DIR. 'temp' .DIRECTORY_SEPARATOR; 
if (!is_dir($TEMP_DIR)) { mkdir($TEMP_DIR); }
$LOG_DIR = $OUT_DIR. 'logs' .DIRECTORY_SEPARATOR;
if (!is_dir($LOG_DIR)) { mkdir($LOG_DIR); }
$SEQUENCES_DIR = $OUT_DIR. 'sequences' .DIRECTORY_SEPARATOR;
if (!is_dir($SEQUENCES_DIR)) { mkdir($SEQUENCES_DIR); }
$SETS_DIR = $OUT_DIR. 'sets' .DIRECTORY_SEPARATOR;
if (!is_dir($SETS_DIR)) { mkdir($SETS_DIR); }

?>
