<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$buildOrderRow = function ($item) {
	$items = array_map(fn($product) => CIBlockElement::GetByID($product)->Fetch()["NAME"], $item["PROPERTIES"]["PRODUCTS"]["VALUE"]);
	$actions = [
		[
			"text"    => "Заказать",
			"onclick" => "add2basket({$item["ID"]});"
		]
	];
	if ($item["PROPERTIES"]["NOT_DELETED"]["VALUE"] != "Y") {
		$actions[] = [
			"text"    => "Удалить",
			"onclick" => "deleteItem({$item["ID"]});"
		];
	}
	return [
		"data" => [
			"ID"           => $item["ID"],
			"NAME"         => $item["NAME"],
			"DATE"         => $item["TIMESTAMP_X"],
			"ORGANIZATION" => $item["PROPERTIES"]["ORGANIZATION_NAME"]["VALUE"],
			"PRODUCTS"     => implode("<br>", $items),
		],
		"actions" => $actions
	];
};

$arResult["ROWS"] = array_map($buildOrderRow, $arResult["ITEMS"]);
