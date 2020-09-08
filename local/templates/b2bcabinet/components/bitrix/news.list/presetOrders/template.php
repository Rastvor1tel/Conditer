<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$APPLICATION->IncludeComponent("bitrix:main.ui.grid", "", [
	"GRID_ID" => "presetsList",
	"COLUMNS" => [
		//['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
		['id' => 'NAME', 'name' => 'Название', 'sort' => 'NAME', 'default' => true],
		['id' => 'DATE', 'name' => 'Дата', 'sort' => 'DATE', 'default' => true],
		['id' => 'ORGANIZATION', 'name' => 'Организация', 'sort' => 'ORGANISATION', 'default' => true],
		['id' => 'PRODUCTS', 'name' => 'Товары', 'sort' => 'PRODUCTS', 'default' => true],
	],
	"ROWS"    => $arResult["ROWS"],
	"SHOW_ROW_ACTIONS_MENU" => "Y",
	"SHOW_TOTAL_COUNTER" => "Y"
]);