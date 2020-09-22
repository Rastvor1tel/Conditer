<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$buildOrderRow = function ($item) {
	$items = array_map(fn($product) => CIBlockElement::GetByID($product)->Fetch()["NAME"], $item["PROPERTIES"]["PRODUCTS"]["VALUE"]);
	return [
		"data" => [
			"ID"           => $item["ID"],
			"NAME"         => $item["NAME"],
			"DATE"         => $item["TIMESTAMP_X"],
			"ORGANIZATION" => $item["PROPERTIES"]["ORGANIZATION_NAME"]["VALUE"],
			"PRODUCTS"     => implode("<br>", $items),
		],
		"actions" => [
			[
				"text"    => "Заказать",
				"onclick" => "add2basket({$item["ID"]});"
			], [
				"text"    => "Удалить",
				"onclick" => "deleteItem({$item["ID"]});"
			]
		]
	];
};

$arResult["ROWS"] = array_map($buildOrderRow, $arResult["ITEMS"]);
