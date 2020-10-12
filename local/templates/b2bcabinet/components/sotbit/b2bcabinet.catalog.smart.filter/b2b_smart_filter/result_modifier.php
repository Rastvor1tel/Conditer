<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $paramSections, $resultSections;
$paramSections = $arParams['ARR_SECTIONS'];
$resultSections = $arResult['ITEMS']['SECTION_ID']['VALUES'];

function buildSections($sectionID, &$section) {
	global $paramSections, $resultSections;
	if ($paramSections[$sectionID]["IBLOCK_SECTION_ID"] == $section["URL_ID"]) {
		$section["CHILDS"][] = $resultSections[$sectionID];
	} elseif ($section["CHILDS"]) {
		foreach ($section["CHILDS"] as &$child) {
			buildSections($sectionID, $child);
		}
	}
}

function checkSelected($arResult) {
	foreach ($arResult as &$arItem) {
		if ($arItem['CHILDS']) {
			$arItem['CHILD_SELECTED'] = 'N';
			foreach ($arItem['CHILDS'] as $arChild) {
				if ($arChild['CHECKED'] === true) {
					$arItem['CHILD_SELECTED'] = 'Y';
				}
			}
		}
	}
}

if (
	(!empty($arParams['ARR_SECTIONS']) && is_array($arParams['ARR_SECTIONS'])) &&
	(!empty($arResult['ITEMS']['SECTION_ID']['VALUES']) && is_array($arResult['ITEMS']['SECTION_ID']['VALUES']))
) {
	$sections = [];
	
	foreach ($arParams['ARR_SECTIONS'] as $arParam) {
		if (!empty($arParam['IBLOCK_SECTION_ID'])) {
			foreach ($sections as &$section) {
				buildSections($arParam["ID"], $section);
			}
		} else {
			$sections[$arParam['ID']] = $arResult['ITEMS']['SECTION_ID']['VALUES'][$arParam['ID']];
		}
	}
	
	$arResult['ITEMS']['SECTION_ID']['SORT_VALUES'] = $sections;
}

if (!empty($arResult['ITEMS']['SECTION_ID']['SORT_VALUES']) && is_array($arResult['ITEMS']['SECTION_ID']['SORT_VALUES'])) {
	checkSelected($arResult['ITEMS']['SECTION_ID']['SORT_VALUES']);
}

global $sotbitFilterResult;
$sotbitFilterResult = $arResult;