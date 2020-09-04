<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');

$arChildParams = [
	"PATH_TO_DETAIL"     => $arResult["PATH_TO_DETAIL"],
	"PATH_TO_CANCEL"     => $arResult["PATH_TO_CANCEL"],
	"PATH_TO_COPY"       => $arResult["PATH_TO_LIST"] . '?ID=#ID#',
	"PATH_TO_BASKET"     => $arParams["PATH_TO_BASKET"],
	"SAVE_IN_SESSION"    => $arParams["SAVE_IN_SESSION"],
	"ORDERS_PER_PAGE"    => $arParams["ORDERS_PER_PAGE"],
	"SET_TITLE"          => $arParams["SET_TITLE"],
	"ID"                 => $arResult["VARIABLES"]["ID"],
	"NAV_TEMPLATE"       => $arParams["NAV_TEMPLATE"],
	"ACTIVE_DATE_FORMAT" => $arParams["ACTIVE_DATE_FORMAT"],
	"HISTORIC_STATUSES"  => [
		'O'
	],
	"CACHE_TYPE"            => $arParams["CACHE_TYPE"],
	"CACHE_TIME"            => $arParams["CACHE_TIME"],
	"CACHE_GROUPS"          => $arParams["CACHE_GROUPS"],
	"DEFAULT_FILTER_FIELDS" => [
		'date_to',
		'date_from',
		'status',
		'id',
		'payed',
		'find'
	],
	"ALLOW_COLUMNS_SORT"    => [
		'ID',
		'DATE_INSERT',
		'STATUS',
		'PRICE',
		'PAYED',
		'PAYMENT_METHOD',
		'SHIPMENT_METHOD',
		'PAY_SYSTEM_ID',
	]
];

$APPLICATION->IncludeComponent("bitrix:sale.personal.order.list", "b2bcabinet", $arChildParams, $component);
?>