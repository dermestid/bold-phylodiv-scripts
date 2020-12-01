<?php

function tree_lengths($tree_filename) {
	$tree = file_get_contents($tree_filename);

	$TREE_REGEX = "/\s*tree\s+'[^']*'\s*=[^;]*;/i";
	$BRANCH_LENGTH_REGEX = '/:[-e\.\d]+[,);]/';

	// Find Newick trees within file
	if(!preg_match_all($TREE_REGEX, $tree, $tree_lines)) {
		echo('Trees not found in file ' . $tree_filename . PHP_EOL);
		return array();
	}

	// Get branch lengths array e.g. ((":0.093898,", ":0.001343)")) etc and sum them
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
			// Note that NJ can produce negative branch lengths.
			// We only need the total tree length, which is preserved if we take the absolute value.
			$sum += abs(floatval(substr($len, 2, -1))); // starts with ':' and ends with ',' ')' or ';'
		}
		$tree_name = explode("'", $tree_line)[1];
		$tree_lengths[$tree_name] = $sum;
	}
	return $tree_lengths;
}

?>