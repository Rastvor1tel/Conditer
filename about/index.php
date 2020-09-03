<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("О компании");

$APPLICATION->IncludeComponent(
	"bitrix:news.list",
	"leaders",
	[
		"COMPONENT_TEMPLATE"        => "leaders",
		"IBLOCK_TYPE"               => "info",
		"IBLOCK_ID"                 => "5",
		"SORT_BY1"                  => "SORT",
		"SORT_ORDER1"               => "ASC",
		"SORT_BY2"                  => "ID",
		"SORT_ORDER2"               => "ASC",
		"FILTER_NAME"               => "",
		"FIELD_CODE"                => [
			0 => "NAME",
			1 => "PREVIEW_PICTURE",
			2 => "",
		],
		"PROPERTY_CODE"             => [
			0 => "EMAIL",
			1 => "POSITION",
			2 => "PHONE",
			3 => "",
		],
		"CACHE_TYPE"                => "A",
		"CACHE_TIME"                => "36000000",
		"CACHE_FILTER"              => "N",
		"CACHE_GROUPS"              => "Y",
		"SET_TITLE"                 => "N",
		"SET_BROWSER_TITLE"         => "N",
		"SET_META_KEYWORDS"         => "N",
		"SET_META_DESCRIPTION"      => "N",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N"
	],
	false
);
?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>