<?php

function sortQUANTITYasc ($a, $b) {
	if ($a["data"]["QUANTITY"] == $b["data"]["QUANTITY"]) return 0;
	return ($a["data"]["QUANTITY"] < $b["data"]["QUANTITY"]) ? -1 : 1;
}

function sortQUANTITYdesc ($a, $b) {
	if ($a["data"]["QUANTITY"] == $b["data"]["QUANTITY"]) return 0;
	return ($a["data"]["QUANTITY"] > $b["data"]["QUANTITY"]) ? -1 : 1;
}

function sortPRICEasc ($a, $b) {
	if ($a["data"]["PRICE"] == $b["data"]["PRICE"]) return 0;
	return ($a["data"]["PRICE"] < $b["data"]["PRICE"]) ? -1 : 1;
}

function sortPRICEdesc ($a, $b) {
	if ($a["data"]["PRICE"] == $b["data"]["PRICE"]) return 0;
	return ($a["data"]["PRICE"] > $b["data"]["PRICE"]) ? -1 : 1;
}

function sortNAMEasc ($a, $b) {
	if ($a["data"]["NAME"] == $b["data"]["NAME"]) return 0;
	return ($a["data"]["NAME"] < $b["data"]["NAME"]) ? -1 : 1;
}

function sortNAMEdesc ($a, $b) {
	if ($a["data"]["NAME"] == $b["data"]["NAME"]) return 0;
	return ($a["data"]["NAME"] > $b["data"]["NAME"]) ? -1 : 1;
}