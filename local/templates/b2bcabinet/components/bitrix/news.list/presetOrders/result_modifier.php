<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$buildOrderRow = function ($item) {
	$items = [];
	array_walk($item["PROPERTIES"]["PRODUCTS"]["VALUE"], function ($product, $key) use ($item, &$items) {
		$product = CIBlockElement::GetByID($product)->Fetch();
		if ($item["PROPERTIES"]["PRODUCTS"]["DESCRIPTION"][$key])
			$items[] = "{$product["NAME"]} x {$item["PROPERTIES"]["PRODUCTS"]["DESCRIPTION"][$key]}";
		else
			$items[] = "{$product["NAME"]}";
	});
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
		"data"    => [
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
