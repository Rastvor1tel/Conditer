<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$buildLeaderItem = fn($item) => [
	"NAME"     => $item["FIELDS"]["NAME"],
	"IMAGE"    => $item["FIELDS"]["PREVIEW_PICTURE"]["SRC"],
	"EMAIL"    => $item["PROPERTIES"]["EMAIL"]["VALUE"],
	"PHONE"    => [
		"VALUE"     => $item["PROPERTIES"]["PHONE"]["VALUE"],
		"FORMATTED" => preg_replace("/[^+0-9]/", '', $item["PROPERTIES"]["PHONE"]["VALUE"])
	],
	"POSITION" => $item["PROPERTIES"]["POSITION"]["VALUE"]
];

$arResult["LEADERS"] = array_map($buildLeaderItem, $arResult["ITEMS"]);
