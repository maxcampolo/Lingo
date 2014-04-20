<?php
	$wordsFile = file('words5.txt', FILE_IGNORE_NEW_LINES);
	$entries = 5;
	$wordKeys = array_rand($wordsFile, $entries);
	$wordValues = array();
	
	foreach ($wordKeys as $key) {
		array_push($wordValues, $wordsFile[$key]);
	}
	unset($key);
	
	echo json_encode($wordValues);
?>