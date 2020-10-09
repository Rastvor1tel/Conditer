<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

foreach($arResult["ITEMS"] as &$arItem){
	$user = CUser::GetByID($arItem["DISPLAY_PROPERTIES"]["USER_ID"]["VALUE"])->Fetch();
	$arItem["USER_NAME"] = "{$user["LAST_NAME"]} {$user["NAME"]}";
}