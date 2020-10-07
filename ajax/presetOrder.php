<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest()->getValues();

$action = $request["action"];

if ($action == "add") {
	global $USER;
	$userID = $USER->GetID();
	$organisationID = Context::getCurrent()->getRequest()->getCookieRaw("ORGANIZATION_ID");
	$arOrganisation = (new CSaleOrderUserProps)->GetList([], ["ID" => $organisationID])->Fetch();
	
	$productsIDs = [];
	$dbBasketItems = CSaleBasket::GetList(
		[],
		[
			"FUSER_ID" => CSaleBasket::GetBasketUserID(),
			"LID"      => SITE_ID,
			"ORDER_ID" => "NULL"
		],
		false,
		false,
		["ID", "PRODUCT_ID", "QUANTITY"]
	);
	while ($arItems = $dbBasketItems->Fetch()) {
		$productsIDs[] = [
			"VALUE"       => $arItems["PRODUCT_ID"],
			"DESCRIPTION" => $arItems["QUANTITY"]
		];
	}
	
	$arFields = [
		"MODIFIED_BY"       => $userID,
		"IBLOCK_SECTION_ID" => false,
		"IBLOCK_ID"         => 6,
		"PROPERTY_VALUES"   => [
			"USER"              => $userID,
			"ORGANIZATION_ID"   => $arOrganisation["ID"],
			"ORGANIZATION_NAME" => $arOrganisation["NAME"],
			"PRODUCTS"          => $productsIDs
		],
		"NAME"              => "Шаблон заказа для организации {$arOrganisation["NAME"]}",
		"ACTIVE"            => "Y",
	];
	
	$element = new CIBlockElement();
	$element->Add($arFields);
}

if ($action == "delete") {
	CIBlockElement::Delete($request["item"]);
}

if ($action == "add2basket") {
	$preset = $request["item"];
	$basket = new CSaleBasket();
	$basket->DeleteAll(CSaleBasket::GetBasketUserID());
	$basketData = [];
	$rsBasketItems = CIBlockElement::GetList([], ["IBLOCK_ID" => 6, "ID" => $preset], false, false, ["ID", "IBLOCK_ID", "NAME", "PROPERTY_PRODUCTS", "PROPERTY_ORGANIZATION_ID"]);
	while ($arBasketItem = $rsBasketItems->GetNextElement()) {
		$basketProps = $arBasketItem->GetProperties();
		$basketData["ITEMS"] = [];
		array_walk($basketProps["PRODUCTS"]["VALUE"], function ($item, $key) use ($basketProps, &$basketData) {
			$arItem = CIBlockElement::GetByID($item)->Fetch();
			$arPrice = CPrice::GetBasePrice($arItem["ID"]);
			$basketData["ITEMS"][] = [
				"NAME"       => $arItem["NAME"],
				"PRODUCT_ID" => $arPrice["PRODUCT_ID"],
				"PRICE"      => $arPrice["PRICE"],
				"CURRENCY"   => $arPrice["CURRENCY"],
				"QUANTITY"   => $basketProps["PRODUCTS"]["DESCRIPTION"][$key],
				"LID"        => SITE_ID
			];
		});
		$basketData["ORGANIZATION"] = $basketProps["ORGANIZATION_ID"]["VALUE"];
	}
	array_walk($basketData["ITEMS"], fn($item) => $basket->Add($item));
	$basketData["QUANTITY"] = count($basketData["ITEMS"]);
	echo json_encode($basketData);
}