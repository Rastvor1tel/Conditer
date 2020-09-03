<?php

include_once($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/includes/DialGadgets.php");

use Bitrix\Main\Web\Cookie, Bitrix\Main\Context;

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('sale', 'OnBeforeOrderUpdate', "importDeliveryDate");

function importDeliveryDate($ID, $arFields) {
	\Bitrix\Main\Diag\Debug::writeToFile($arFields, '', '/local/php_interface/import.log');
}


function checkActiveOrganization($arOrganizations) {
	if ($_REQUEST['ORGANIZATION_ID']) {
		if ($_REQUEST['ORGANIZATION_ID'] == "empty") {
			unset($_SESSION['PRICE_ID'], $_SESSION['ORGANIZATION_ID']);
		} else {
			foreach ($arOrganizations as $arItem) {
				if ($arItem['ID'] == $_REQUEST['ORGANIZATION_ID']) {
					$_SESSION['PRICE_ID'] = $arItem['PRICE'];
					$_SESSION['ORGANIZATION_ID'] = $arItem['ID'];
					setcookie("ORGANIZATION_ID", $arItem['ID'], time() + 3600, "/");
				}
			}
		}
	}
}

function bildOrganizationList() {
	global $USER;
	$result = [];
	$rsUserProfile = (new CSaleOrderUserProps)->GetList([], ["USER_ID" => $USER->GetID()]);
	while ($arUserProfile = $rsUserProfile->Fetch()) {
		$orgItem = [
			"ID" => $arUserProfile['ID']
		];
		$rsUserProfileValue = (new CSaleOrderUserPropsValue)->GetList(["ID" => "ASC"], ["USER_PROPS_ID" => $arUserProfile['ID']]);
		while ($arUserProfileValue = $rsUserProfileValue->Fetch()) {
			if ($arUserProfileValue['PROP_ID'] == 35) {
				$orgItem['STOP'] = $arUserProfileValue['VALUE'];
			}
			if ($arUserProfileValue['PROP_ID'] == 14) {
				$orgItem['NAME'] = str_replace('"', "'", $arUserProfileValue["VALUE"]);
			}
			if ($arUserProfileValue['PROP_ID'] == 27) {
				$orgItem['PRICE'] = $arUserProfileValue['VALUE'];
			}
		}
		if ($orgItem['STOP'] != 'Y') {
			$result[] = $orgItem;
		}
	}
	return $result;
}