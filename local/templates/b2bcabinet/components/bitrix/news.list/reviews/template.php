<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();


foreach($arResult["ITEMS"] as $arItem){
	echo <<<ITEM
		<div class="review-item">
			<div class="review-item__name">{$arItem["USER_NAME"]}</div>
			<div class="review-item__date">{$arItem["DISPLAY_ACTIVE_FROM"]}</div>
			<div class="review-item__text">{$arItem["PREVIEW_TEXT"]}</div>
		</div>
	ITEM;
}