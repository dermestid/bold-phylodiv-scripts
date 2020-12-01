<?php

function tree_lengths($tree_filename) {
	$tree = file_get_contents($tree_filename);

	$TREE_REGEX = "/\s*tree\s+'[^']*'\s*=[^;]*;/i";
	$BRANCH_LENGTH_REGEX = '/:\d+[\.,]?\d*/';

	// Find Newick trees within file
	if(!preg_match_all($TREE_REGEX, $tree, $tree_lines)) {
		echo('Trees not found in file ' . $tree_filename . PHP_EOL);
		return array();
	}

	// Get branch lengths array e.g. ((":0.0938", ":0.0013")) etc and sum them
	$tree_lengths = array();

	foreach ($tree_lines[0] as $tree_line)
	{
		preg_match_all($BRANCH_LENGTH_REGEX, $tree_line, $branch_lengths);
		if(!count($branch_lengths[0])) {
			echo('Branch lengths missing from Newick tree in ' . $tree_filename . PHP_EOL);
			continue;
		}

		$sum = 0;
		foreach ($branch_lengths[0] as $len)
		{
			$sum += floatval(substr($len, 1)); // first char is ':'
		}
		$tree_name = explode("'", $tree_line)[1];
		$tree_lengths[$tree_name] = $sum;
	}
	return $tree_lengths;
}

?>