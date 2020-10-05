<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

if (
	(!empty($arParams['ARR_SECTIONS']) && is_array($arParams['ARR_SECTIONS'])) &&
	(!empty($arResult['ITEMS']['SECTION_ID']['VALUES']) && is_array($arResult['ITEMS']['SECTION_ID']['VALUES']))
) {
	$sectionKeys = array_keys($arResult['ITEMS']['SECTION_ID']['VALUES']);
	
	foreach ($arParams['ARR_SECTIONS'] as $arParam) {
		if (!empty($arParam['IBLOCK_SECTION_ID'])) {
			$arResult['ITEMS']['SECTION_ID']['SORT_VALUES'][$arParam['IBLOCK_SECTION_ID']]['CHILDS'][] = $arResult['ITEMS']['SECTION_ID']['VALUES'][$arParam['ID']];
		} else {
			$arResult['ITEMS']['SECTION_ID']['SORT_VALUES'][$arParam['ID']] = $arResult['ITEMS']['SECTION_ID']['VALUES'][$arParam['ID']];
		}
	}
}

if (!empty($arResult['ITEMS']['SECTION_ID']['SORT_VALUES']) && is_array($arResult['ITEMS']['SECTION_ID']['SORT_VALUES'])) {
	foreach ($arResult['ITEMS']['SECTION_ID']['SORT_VALUES'] as &$SORT_VALUE) {
		if (!empty($SORT_VALUE) && is_array($SORT_VALUE['CHILDS'])) {
			$SORT_VALUE['CHILD_SELECTED'] = 'N';
			foreach ($SORT_VALUE['CHILDS'] as $CHILD) {
				if ($CHILD['CHECKED'] === true) {
					$SORT_VALUE['CHILD_SELECTED'] = 'Y';
					break;
				}
			}
		}
	}
}

global $sotbitFilterResult;
$sotbitFilterResult = $arResult;
