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

foreach ($arParams as $key => $val) {
	if (strpos($key, "STATUS_COLOR_") !== false && strpos($key, "~") !== 0) {
		$arChildParams[$key] = $val;
	}
}

$_REQUEST['by'] = isset($_GET['by']) ? $_GET['by'] : 'ID';
$_REQUEST['order'] = isset($_GET['order']) ? strtoupper($_GET['order']) : 'DESC';

$filter = [];
$filterOption = new Bitrix\Main\UI\Filter\Options('ORDER_LIST');
$filterData = $filterOption->getFilter([]);

foreach ($filterData as $key => $value) {
	if (in_array(strtolower($key), $arChildParams['DEFAULT_FILTER_FIELDS'])) {
		$_REQUEST['filter_' . strtolower($key)] = $value;
	}
}

if ($filterData['BUYER']) {
	$orders = [];
	$rs = \Bitrix\Sale\Internals\OrderTable::getList([
		'filter' => [
			'USER_ID' => $USER->GetID()
		]
	]);
	while ($order = $rs->fetch()) {
		$orders[] = $order['ID'];
	}
	if ($orders) {
		$innV = [];
		$innProps = unserialize(Bitrix\Main\Config\Option::get('sotbit.b2bcabinet', 'PROFILE_ORG_INN'));
		if (!is_array($innProps)) {
			$innProps = [];
		}
		$rs = \Bitrix\Sale\Internals\UserPropsValueTable::getList([
			'filter' => [
				"USER_PROPS_ID"  => $filterData['BUYER'],
				'ORDER_PROPS_ID' => $innProps
			],
			"select" => [
				"ORDER_PROPS_ID",
				'USER_PROPS_ID',
				'VALUE'
			]
		]);
		while ($buyer = $rs->fetch()) {
			$innV[] = $buyer['VALUE'];
		}
		
		$rOrders = [];
		$rs = \Bitrix\Sale\Internals\OrderPropsValueTable::getList([
			'filter' => [
				'ORDER_ID'       => $orders,
				'ORDER_PROPS_ID' => $innProps,
				'VALUE'          => $innV
			]
		]);
		while ($v = $rs->fetch()) {
			$rOrders[] = $v['ORDER_ID'];
		}
		if ($rOrders) {
			if ($_REQUEST['filter_id']) {
				$_REQUEST['filter_id'] = array_merge($_REQUEST['filter_id'], $rOrders);
			} else {
				$_REQUEST['filter_id'] = $rOrders;
			}
		}
	}
}

$APPLICATION->IncludeComponent("bitrix:sale.personal.order.list", "b2bcabinet", $arChildParams, $component);
?>