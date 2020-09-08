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
		$productsIDs[] = $arItems["PRODUCT_ID"];
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