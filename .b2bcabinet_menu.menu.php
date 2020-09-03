<?
$aMenuLinks = [
	[
		"Главная",
		"",
		[],
		["ICON_CLASS" => "icon-home4"],
		"\\Bitrix\\Main\\Loader::includeModule('sotbit.b2bcabinet')"
	],
	[
		"Персональные данные",
		"personal/",
		[],
		[],
		"\\Bitrix\\Main\\Loader::includeModule('sotbit.b2bcabinet')"
	],
	[
		"Заказы",
		"orders/",
		[],
		[],
		"\\Bitrix\\Main\\Loader::includeModule('sotbit.b2bcabinet')"
	],
	[
		"Документы",
		"documents/",
		[],
		["ICON_CLASS" => "icon-stack-text"],
		"\\Bitrix\\Main\\Loader::includeModule('sotbit.b2bcabinet') && \\Sotbit\\B2bCabinet\\Helper\\Document::getIblocks()"
	],
	[
		"Техническая поддержка",
		"support/",
		[],
		[],
		"\\Bitrix\\Main\\Loader::includeModule('sotbit.b2bcabinet') && \\Bitrix\\Main\\Loader::includeModule('support')"
	]
];
?>