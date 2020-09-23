<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$userID = "";
$arOrganization = [];
$elementIDs = [];

$arOrderParams = [
	"Email"       => "test@b2b.ru",
	"Название"    => "Тестовый заказ",
	"Организация" => "d866ff4a-4ac4-11e4-8846-005056a18c1e"
];
$userFilter = [
	"EMAIL" => $arOrderParams["Email"]
];
$arUser = CUser::GetList(($by = "id"), ($order = "asc"), $userFilter)->Fetch();
$userID = $arUser['ID'];

$arUserProfile = (new CSaleOrderUserProps)->GetList([], ["XML_ID"  => $arOrderParams['Организация'], "USER_ID" => $userID])->Fetch();
$arOrganization["ID"] = $arUserProfile['ID'];
$rsUserProfileValue = (new CSaleOrderUserPropsValue)->GetList(["ID" => "ASC"], ["USER_PROPS_ID" => $arUserProfile['ID']]);
while ($arUserProfileValue = $rsUserProfileValue->Fetch()) {
	if ($arUserProfileValue['PROP_ID'] == 14) {
		$arOrganization["NAME"] = str_replace('"', "'", $arUserProfileValue["VALUE"]);
	}
	if ($arUserProfileValue['PROP_ID'] == 27) {
		$arOrganization["PRICE"] = $arUserProfileValue['VALUE'];
	}
}

$itemPropsPrice = [
	"ВнешнийКод" => "55ccdec4-7c69-11e4-814e-005056a18c1e"
];

$elementXmlID = "{$itemPropsPrice["ВнешнийКод"]}_{$arOrganization["PRICE"]}";
$obImport = new CIBlockCMLImport();
$elementIDs[] = $obImport->GetElementByXML_ID(3, $elementXmlID);



$arFields = [
	"MODIFIED_BY"       => $userID,
	"IBLOCK_SECTION_ID" => false,
	"IBLOCK_ID"         => 6,
	"PROPERTY_VALUES"   => [
		"USER"              => $userID,
		"ORGANIZATION_ID"   => $arOrganization["ID"],
		"ORGANIZATION_NAME" => $arOrganization["NAME"],
		"PRODUCTS"          => $elementIDs
	],
	"NAME"              => $arOrderParams["Название"],
	"ACTIVE"            => "Y",
];

echo "<pre>";
print_r($arFields);
echo "</pre>";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>