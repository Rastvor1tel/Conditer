<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

$lang = [
	"SPOL_PSEUDO_CANCELLED" => Loc::GetMessage('SPOL_PSEUDO_CANCELLED'),
	"MEASURE_TEXT"          => Loc::GetMessage("MEASURE_TEXT")
];

$methodIstall = Option::get('sotbit.b2bcabinet', 'method_install', '', SITE_ID) == 'AS_TEMPLATE' ? SITE_DIR . 'b2bcabinet/' : SITE_DIR;

$buyers = $pt = $innProps = $orgProps = [];

$pt = unserialize(Option::get("sotbit.b2bcabinet", "BUYER_PERSONAL_TYPE", "a:0:{}"));
$innProps = unserialize(Option::get('sotbit.b2bcabinet', 'PROFILE_ORG_INN'));
$orgProps = unserialize(Option::get('sotbit.b2bcabinet', 'PROFILE_ORG_NAME'));

$idBuyers = [];
$rs = CSaleOrderUserProps::GetList(
	["DATE_UPDATE" => "DESC"],
	[
		"PERSON_TYPE_ID" => $pt,
		"USER_ID"        => (int)$USER->GetID()
	]
);
while ($buyer = $rs->Fetch()) {
	$idBuyers[] = $buyer['ID'];
}

if ($idBuyers) {
	$rs = \Bitrix\Sale\Internals\UserPropsValueTable::getList(
		[
			'filter' => [
				"USER_PROPS_ID"  => $idBuyers,
				'ORDER_PROPS_ID' => array_merge($innProps, $orgProps)
			],
			"select" => ["ORDER_PROPS_ID", 'USER_PROPS_ID', 'VALUE']
		]
	);
	while ($prop = $rs->Fetch()) {
		if (in_array($prop['ORDER_PROPS_ID'], $innProps)) {
			$buyers[$prop['USER_PROPS_ID']]['INN'] = $prop['VALUE'];
		}
		if (in_array($prop['ORDER_PROPS_ID'], $orgProps)) {
			$buyers[$prop['USER_PROPS_ID']]['ORG'] = $prop['VALUE'];
		}
	}
}

$orgs = $idOrders = $catalog = [];

$idOrders = array_map(fn($item) => $item["ORDER"]["ID"], $arResult['ORDERS']);

array_walk($arResult['ORDERS'], function (&$order) use (&$catalog) {
	return array_walk($order["BASKET_ITEMS"], function (&$item) use (&$catalog) {
		$arItem = CIBlockElement::GetByID($item["PRODUCT_ID"])->Fetch();
		$arSection = CIBlockSection::GetByID($arItem["IBLOCK_SECTION_ID"])->Fetch();
		if ($arSection) {
			$item["SECTION"] = [
				"ID"   => $arSection["ID"],
				"NAME" => $arSection["NAME"]
			];
			if (!$catalog["SECTIONS"][$arSection["NAME"]])
				$catalog["SECTIONS"][$arSection["NAME"]] = $arSection["NAME"];
		}
		return (!$catalog["PRODUCTS"][$item["PRODUCT_ID"]]) ? $catalog["PRODUCTS"][$item["PRODUCT_ID"]] = $item["NAME"] : false;
	});
});

$arResult["CATALOG"] = $catalog;

$arResult['BUYERS'] = [];

if ($buyers) {
	foreach ($buyers as $id => $v) {
		$name = $v['ORG'];
		$name .= ($v['INN']) ? ' (' . $v['INN'] . ')' : '';
		$arResult['BUYERS'][$id] = $name;
	}
	
	$rs = \Bitrix\Sale\Internals\OrderPropsValueTable::getList([
		'filter' => [
			'ORDER_ID'       => $idOrders,
			'ORDER_PROPS_ID' => $orgProps
		]
	]);
	while ($org = $rs->fetch()) {
		foreach ($buyers as $id => $v) {
			if ($v['ORG'] == $org['VALUE']) {
				$name = $v['ORG'];
				$name .= ($v['INN']) ? ' (' . $v['INN'] . ')' : '';
				$orgs[$org['ORDER_ID']] = [
					"HTML" => '<a href="' . $methodIstall . 'personal/buyer/profile_detail.php?ID=' . $id . '">' . $name . '</a>',
					"ID"   => $id
				];
			}
		}
	}
}

$arResult['INFO']['STATUS'] = array_map(fn($item) => CSaleStatus::GetByID($item["ID"]), $arResult['INFO']['STATUS']);

$arResult["STATUS"] = [
	"cancel" => $lang['SPOL_PSEUDO_CANCELLED']
];
foreach ($arResult['INFO']['STATUS'] as $arStatus) {
	$arResult["STATUS"][$arStatus["ID"]] = $arStatus["NAME"];
}


foreach ($arResult['ORDERS'] as $arOrder) {
	$aActions = [
		["ICONCLASS" => "detail", "TEXT" => GetMessage('SPOL_MORE_ABOUT_ORDER'), "ONCLICK" => "jsUtils.Redirect(arguments, '" . $arOrder['ORDER']["URL_TO_DETAIL"] . "')", "DEFAULT" => true]
	];
	
	$items = array_map(fn($item) => "{$item["NAME"]} - ({$item["QUANTITY"]} {$lang["MEASURE_TEXT"]})", $arOrder['BASKET_ITEMS']);
	$itemsIDs = array_map(fn($item) => $item["PRODUCT_ID"], $arOrder['BASKET_ITEMS']);
	$itemsData = array_map(fn($item) => [
		"ID"       => $item["PRODUCT_ID"],
		"NAME"     => $item["NAME"],
		"QUANTITY" => $item["QUANTITY"],
		"PRICE"    => $item["PRICE"],
		"SECTION"  => $item["SECTION"]
	], $arOrder['BASKET_ITEMS']);
	
	$orderStatus = [];
	if ($arOrder['ORDER']['CANCELED'] == 'Y') {
		$orderStatus = [
			"ID"   => "cancel",
			"NAME" => $lang['SPOL_PSEUDO_CANCELLED'],
			"SORT" => 0
		];
	} else {
		$orderStatus = $arResult['INFO']['STATUS'][$arOrder['ORDER']['STATUS_ID']];
	}
	
	$arResult['ROWS'][] = [
		'data'     => array_merge($arOrder['ORDER'], [
			"SHIPMENT_METHOD" => $arResult["INFO"]["DELIVERY"][$arOrder["ORDER"]["DELIVERY_ID"]]["NAME"],
			"PAYMENT_METHOD"  => $arResult["INFO"]["PAY_SYSTEM"][$arOrder["ORDER"]["PAY_SYSTEM_ID"]]["NAME"],
			'ITEMS'           => implode('<br>', $items),
			'ITEMS_DATA'      => $itemsData,
			'STATUS_NAME'     => $orderStatus["NAME"],
			'STATUS'          => $orderStatus,
			'PAYED'           => GetMessage('SPOL_' . ($arOrder["ORDER"]["PAYED"] == "Y" ? 'YES' : 'NO')),
			'PAY_SYSTEM_ID'   => $arOrder["ORDER"]["PAY_SYSTEM_ID"],
			'DELIVERY_ID'     => $arOrder["ORDER"]["DELIVERY_ID"],
			'BUYER'           => $orgs[$arOrder['ORDER']['ID']]["HTML"],
			'BUYER_ID'        => $orgs[$arOrder['ORDER']['ID']]["ID"],
		]),
		'actions'  => $aActions,
		'editable' => true,
	];
}

$filterOption = new Bitrix\Main\UI\Filter\Options('PRODUCT_LIST');
$filterData = $filterOption->getFilter([]);

if ($filterData) {
	$filterRows = function ($item) use ($filterData, $compareItemsArray) {
		if ($filterData["BUYER_ID"]) {
			if (!in_array($item["data"]["BUYER_ID"], $filterData["BUYER_ID"])) return false;
		}
		
		if ($filterData["STATUS"]) {
			if (!in_array($item["data"]["STATUS"]["ID"], $filterData["STATUS"])) return false;
		}
		
		if ($filterData["DATE_INSERT_datesel"]) {
			$dateFrom = new DateTime($filterData["DATE_INSERT_from"]);
			$dateTo = new DateTime($filterData["DATE_INSERT_to"]);
			$dateOrder = $item["data"]["DATE_INSERT"]->getTimestamp();
			if (($dateOrder < $dateFrom->getTimestamp()) || ($dateOrder > $dateTo->getTimestamp())) return false;
		}
		
		if ($filterData["PRODUCT_ID"]) {
			$contain = false;
			foreach ($item["data"]["ITEMS_DATA"] as $product)
				if (in_array($product["ID"], $filterData["PRODUCT_ID"])) $contain = true;
			if (!$contain) return false;
		}
		
		if ($filterData["FIND"]) {
			if (mb_stripos($item["data"]["ITEMS"], $filterData["FIND"]) === false) return false;
		}
		
		return true;
	};
	
	$arResult["ROWS"] = array_filter($arResult["ROWS"], $filterRows);
}

$dateArray = $resultArray = $month = [];

array_walk($arResult["ROWS"], function ($row) use (&$dateArray, &$resultArray) {
	$dateOrder = $row["data"]["DATE_INSERT"]->getTimestamp();
	$month = date("F-y", $dateOrder);
	$day = date("m-d-y", $dateOrder);
	if (!$dateArray[$month])
		$dateArray[$month] = ["SUMM" => 0, "COUNT" => 0];
	if (!$dateArray[$day])
		$dateArray[$day] = ["SUMM" => 0, "COUNT" => 0];
});

$dateArray["Итого"] = ["SUMM" => 0, "COUNT" => 0];

array_walk($arResult["ROWS"], function ($row) use ($dateArray, &$resultArray) {
	$dateOrder = $row["data"]["DATE_INSERT"]->getTimestamp();
	$month = date("F-y", $dateOrder);
	$day = date("m-d-y", $dateOrder);
	$arItems = $row["data"]["ITEMS_DATA"];
	array_walk($arItems, function ($item) use (&$resultArray, $dateArray, $month, $day) {
		$section = $item["SECTION"]["NAME"] ?? "Без раздела";
		if (!$resultArray[$section]) {
			$resultArray[$section]["NAME"] = $section;
			$resultArray[$section]["IS_SECTION"] = "Y";
			$resultArray[$section]["COUNT"] = $dateArray;
		}
		if (!$resultArray[$item["ID"]]) {
			$resultArray[$item["ID"]]["ID"] = $item["ID"];
			$resultArray[$item["ID"]]["NAME"] = $item["NAME"];
			$resultArray[$item["ID"]]["SECTION"] = $item["SECTION"]["NAME"];
			$resultArray[$item["ID"]]["COUNT"] = $dateArray;
		}
		$resultArray[$section]["COUNT"][$month]["SUMM"] += $item["PRICE"];
		$resultArray[$section]["COUNT"][$month]["COUNT"] += $item["QUANTITY"];
		$resultArray[$section]["COUNT"][$day]["SUMM"] += $item["PRICE"];
		$resultArray[$section]["COUNT"][$day]["COUNT"] += $item["QUANTITY"];
		$resultArray[$section]["COUNT"]["Итого"]["SUMM"] += $item["PRICE"];
		$resultArray[$section]["COUNT"]["Итого"]["COUNT"] += $item["QUANTITY"];
		
		$resultArray[$item["ID"]]["COUNT"][$month]["SUMM"] += $item["PRICE"];
		$resultArray[$item["ID"]]["COUNT"][$month]["COUNT"] += $item["QUANTITY"];
		$resultArray[$item["ID"]]["COUNT"][$day]["SUMM"] += $item["PRICE"];
		$resultArray[$item["ID"]]["COUNT"][$day]["COUNT"] += $item["QUANTITY"];
		$resultArray[$item["ID"]]["COUNT"]["Итого"]["SUMM"] += $item["PRICE"];
		$resultArray[$item["ID"]]["COUNT"]["Итого"]["COUNT"] += $item["QUANTITY"];
	});
});

if ($filterData) {
	$filterRows = function ($item) use ($filterData) {
		if ($filterData["PRODUCT_ID"])
			if (!in_array($item["ID"], $filterData["PRODUCT_ID"])) return false;
		
		if ($filterData["SECTION"]) {
			if (($item["IS_SECTION"] == "Y")) {
				if (!in_array($item["NAME"], $filterData["SECTION"])) return false;
			} else {
				if (!in_array($item["SECTION"], $filterData["SECTION"])) return false;
			}
		}
		
		return true;
	};
	
	$resultArray = array_filter($resultArray, $filterRows);
}

$resultArray[] = [
	"NAME"  => "Итого",
	"COUNT" => $dateArray
];

array_walk($resultArray, function ($item) use (&$resultArray) {
	if ($item["NAME"] != "Итого") {
		array_walk($item["COUNT"], function ($count, $key) use (&$resultArray) {
			$resultArray[array_key_last($resultArray)]["COUNT"][$key]["SUMM"] += $count["SUMM"];
			$resultArray[array_key_last($resultArray)]["COUNT"][$key]["COUNT"] += $count["COUNT"];
		});
	}
});

$arResult["TABLE"] = [
	"DATE" => $dateArray,
	"ROWS" => $resultArray
];

/*$itemRows = [];

foreach ($arResult["ROWS"] as $arRow) {
	foreach ($arRow["data"]["ITEMS_DATA"] as $arItem) {
		if (!$itemRows[$arItem["ID"]]) {
			$itemRows[$arItem["ID"]] = [
				"data"     => $arItem,
				"actions"  => '',
				"editable" => false,
			];
			$itemRows[$arItem["ID"]]["data"] = $arItem;
			$itemRows[$arItem["ID"]]["data"]["PRICE"] = (float)$arItem["QUANTITY"] * $arItem["PRICE"];
			$itemRows[$arItem["ID"]]["data"]["FORMATED_PRICE"] = CCurrencyLang::CurrencyFormat($itemRows[$arItem["ID"]]["data"] ["PRICE"], "RUB");
		} else {
			$itemRows[$arItem["ID"]]["data"]["QUANTITY"] += (int)$arItem["QUANTITY"];
			$itemRows[$arItem["ID"]]["data"]["PRICE"] += (float)$arItem["QUANTITY"] * $arItem["PRICE"];
			$itemRows[$arItem["ID"]]["data"]["FORMATED_PRICE"] = CCurrencyLang::CurrencyFormat($itemRows[$arItem["ID"]]["data"] ["PRICE"], "RUB");
		}
	}
}

$arResult["ROWS"] = $itemRows;

if ($filterData) {
	$filterRows = function ($item) use ($filterData, $compareItemsArray) {
		if ($filterData["ID"] && ($item["data"]["ID"] != $filterData["ID"])) return false;
		
		if ($filterData["SECTION_ID"])
			if (!in_array($item["data"]["SECTION"]["ID"], $filterData["SECTION_ID"])) return false;
		
		return true;
	};
	
	$arResult["ROWS"] = array_filter($arResult["ROWS"], $filterRows);
}

$gridOptions = new Bitrix\Main\Grid\Options('PRODUCT_LIST');
$sortData = $gridOptions->GetSorting([]);

if ($sortData["sort"]) {
	require_once("{$_SERVER["DOCUMENT_ROOT"]}{$this->GetFolder()}/sortFunctions.php");
	$fieldName = key($sortData["sort"]);
	$fieldDirection = current($sortData["sort"]);
	usort($arResult["ROWS"], "sort{$fieldName}{$fieldDirection}");
}*/

//Выгрузка в Excel
if (DialHelper::checkAjax()) {
	$GLOBALS['APPLICATION']->RestartBuffer();
	
	$header = \Bitrix\Main\Context::getCurrent()->getRequest()->getPostList()->getValues();
	$helper = new DialHelperCrutch();
	$helper->export2ExcelCrutch($arResult["TABLE"], "Список заказанных товаров");
	//$helper->export2Excel($header, $arResult["ROWS"], "Список заказанных товаров");
	
	die();
}
