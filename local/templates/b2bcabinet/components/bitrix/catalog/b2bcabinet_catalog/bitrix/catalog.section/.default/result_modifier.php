<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogSectionComponent $component
 */

$arTableHeader = [];

$arTableHeader["NAME"] = Loc::getMessage("HEAD_NAME");

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();

// Settings -> Infoblocks -> Property Features Enabled
// When this option is enabled, the properties displayed in the list are selected in the Infoblock TYPES
$isEnabledFeature = (\COption::getOptionString('iblock', 'property_features_enabled', '') == 'Y');

if (
	is_array($arParams['PROPERTY_CODE']) && !empty($arParams['PROPERTY_CODE']) &&
	is_array($arResult['ITEMS'][0]['PROPERTIES']) && !empty($arResult['ITEMS'][0]['PROPERTIES'])
) {
	$arPropKeys = array_flip($arParams['PROPERTY_CODE']);
	foreach ($arResult['ITEMS'][0]['PROPERTIES'] as $key => $prop) {
		if (
			array_key_exists($key, $arPropKeys) &&
			!empty($prop['NAME']) &&
			$prop['PROPERTY_TYPE'] !== 'F'
		) {
			$arTableHeader[$key] = $prop['NAME'];
		}
	}
}

if ($isEnabledFeature) {
	foreach ($arResult['SKU_PROPS'][$arResult['IBLOCK_ID']] as $key => $prop) {
		if (!array_key_exists($key, $arTableHeader) && $prop['PROPERTY_TYPE'] !== 'F') {
			$arTableHeader[$key] = $prop['NAME'];
		}
	}
} else {
	if (is_array($arParams['OFFERS_PROPERTY_CODE']) && !empty($arParams['OFFERS_PROPERTY_CODE'])) {
		$arOfferPropKeys = array_flip($arParams['OFFERS_PROPERTY_CODE']);

		foreach ($arResult['ITEMS'] as &$ITEM) {
			if (isset($ITEM['OFFERS'][0]) && !empty($ITEM['OFFERS'][0])) {
				foreach ($ITEM['OFFERS'][0]['PROPERTIES'] as $key => $prop) {
					if (
						!array_key_exists($key, $arTableHeader)
						&& array_key_exists($key, $arOfferPropKeys) &&
						$prop['PROPERTY_TYPE'] !== 'F'
					) {
						$arTableHeader[$key] = $prop['NAME'];
					}
				}
			}
		}
	}
}

if (is_array($arResult['PRICES']) && !empty($arResult['PRICES'])) {
	foreach ($arResult['PRICES'] as $key => $PRICE) {
		if (!empty($PRICE['TITLE'])) {
			$arTableHeader['PRICES'][$key]['NAME'] = $PRICE['TITLE'];
			$arTableHeader['PRICES'][$key]['ID'] = $PRICE['ID'];
		}
	}
	$arTableHeader['QUANTITY'] = Loc::getMessage('HEAD_QUANTITY');
	$arTableHeader['RATIO'] = Loc::getMessage('HEAD_RATIO');
	$arTableHeader['MEASURE'] = Loc::getMessage('HEAD_MEASURE');
}

$arTableHeader["AVALIABLE"] = Loc::getMessage("HEAD_AVAILABLE");

$arParams['TABLE_HEADER'] = $arTableHeader;