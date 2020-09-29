<?php

function sortIDasc($a, $b) {
	if ($a["data"]["ID"] == $b["data"]["ID"]) return 0;
	return ($a["data"]["ID"] < $b["data"]["ID"]) ? -1 : 1;
}

function sortIDdesc($a, $b) {
	if ($a["data"]["ID"] == $b["data"]["ID"]) return 0;
	return ($a["data"]["ID"] > $b["data"]["ID"]) ? -1 : 1;
}

function sortPRICEasc($a, $b) {
	if ($a["data"]["PRICE"] == $b["data"]["PRICE"]) return 0;
	return ($a["data"]["PRICE"] < $b["data"]["PRICE"]) ? -1 : 1;
}

function sortPRICEdesc($a, $b) {
	if ($a["data"]["PRICE"] == $b["data"]["PRICE"]) return 0;
	return ($a["data"]["PRICE"] > $b["data"]["PRICE"]) ? -1 : 1;
}

function sortDATEasc($a, $b) {
	$dateA = $a["data"]["DATE_INSERT"]->getTimestamp();
	$dateB = $b["data"]["DATE_INSERT"]->getTimestamp();
	if ($dateA == $dateB) return 0;
	return ($dateA < $dateB) ? -1 : 1;
}

function sortDATEdesc($a, $b) {
	$dateA = $a["data"]["DATE_INSERT"]->getTimestamp();
	$dateB = $b["data"]["DATE_INSERT"]->getTimestamp();
	if ($dateA == $dateB) return 0;
	return ($dateA > $dateB) ? -1 : 1;
}

function sortSTATUSasc($a, $b) {
	if ($a["data"]["STATUS"]["SORT"] == $b["data"]["STATUS"]["SORT"]) return 0;
	return ($a["data"]["STATUS"]["SORT"] < $b["data"]["STATUS"]["SORT"]) ? -1 : 1;
}

function sortSTATUSdesc($a, $b) {
	if ($a["data"]["STATUS"]["SORT"] == $b["data"]["STATUS"]["SORT"]) return 0;
	return ($a["data"]["STATUS"]["SORT"] > $b["data"]["STATUS"]["SORT"]) ? -1 : 1;
}