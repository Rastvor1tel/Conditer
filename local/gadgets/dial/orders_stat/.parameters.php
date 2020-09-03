<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$statuses = [
    'ALL' => GetMessage("GD_SOTBIT_CABINET_ORDER_STATUS_ALL")
];

$rsStatuses = \Bitrix\Sale\StatusLangTable::getList(['filter' => ['LID' => LANGUAGE_ID]]);
while ($status = $rsStatuses->fetch()) {
    $statuses[$status['STATUS_ID']] = $status['NAME'];
}

$arParameters = [
    "PARAMETERS"      => [
        "STATUS" => [
            "NAME"     => GetMessage("GD_DIAL_ORDER_STATUS"),
            "TYPE"     => "LIST",
            "MULTIPLE" => "Y",
            "DEFAULT"  => "ALL",
            "VALUES"   => $statuses,
        ],
    ],
    "USER_PARAMETERS" => [
    ]
];
?>
