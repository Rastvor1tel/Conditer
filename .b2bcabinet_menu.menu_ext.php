<?
$aMenuLinks = [
    [
        "Главная",
        SITE_DIR,
        [],
        ['ICON_CLASS' => 'icon-home4'],
        ""
    ],
	[
		"О компании",
		SITE_DIR."about/",
		[],
		['ICON_CLASS' => 'icon-stack-text'],
		""
	],

    [
	    "Отзывы",
	    SITE_DIR."reviews/",
	    [],
	    [],
	    ""
    ],
	[
		"Персональные данные",
        SITE_DIR."personal/",
		[],
        [],
		""
	],
    [
        "Заказы",
        SITE_DIR."orders/",
        [],
        [],
        ""
    ],
    [
        "Документы",
        SITE_DIR."documents/",
        [],
        [],
        ""
    ]
];

if(\Bitrix\Main\Loader::includeModule('support')) {
    $aMenuLinks[] = [
        "Техническая поддержка",
        SITE_DIR."support/",
        [],
        [],
        ""
    ];
}
?>