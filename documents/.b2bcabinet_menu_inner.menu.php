<?
use Bitrix\Main\Config\Option;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;

$arLinkIcons = [
    SITE_DIR.'documents/contracts/' => 'icon-stack-text',
    SITE_DIR.'documents/acts/' => 'icon-stack-check',
    SITE_DIR.'documents/other/' => 'icon-stack4',
];

$aMenuLinksExt = $APPLICATION->IncludeComponent("bitrix:menu.sections","",Array(
		"IS_SEF" => "Y",
		"SEF_BASE_URL" => SITE_DIR."documents/",
		"SECTION_PAGE_URL" => "#SECTION_CODE#/",
		"DETAIL_PAGE_URL" => "#SECTION_CODE#/#ELEMENT_CODE#/",
		"IBLOCK_TYPE" => Option::get("sotbit.b2bcabinet", "DOCUMENT_IBLOCK_TYPE", ""),
		"IBLOCK_ID" => Option::get("sotbit.b2bcabinet", "DOCUMENT_IBLOCK_ID", ""),
		"DEPTH_LEVEL" => "2",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600"
	)
);

foreach($aMenuLinksExt as &$arItem) {
    if(isset($arLinkIcons[$arItem['1']])) {
        $arItem[3]['ICON_CLASS'] = $arLinkIcons[$arItem['1']];
    }
}


$aMenuLinks = array_merge($aMenuLinksExt, $aMenuLinks);

?>