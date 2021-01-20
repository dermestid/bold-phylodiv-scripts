<?php

$CLUSTAL_PATH = '/usr/local/bin/clustalw2';
$PAUP_PATH = '/usr/local/bin/paup';
if ((stripos(PHP_OS, 'WIN') === 0)) {
	$CLUSTAL_PATH = 'C:\\\\"Program Files (x86)"/ClustalW2/clustalw2';
	$PAUP_PATH = 'C:\\\\"Program Files"/PAUP4/paup';
}

$PAUP_COMMANDS_SETUP = '../template/nexus/paup_commands_setup.txt';
$PAUP_COMMANDS_TREE = '../template/nexus/paup_commands_tree.txt';
$PAUP_COMMANDS_END = '../template/nexus/paup_commands_end.txt';

?>
