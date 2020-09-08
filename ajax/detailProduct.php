<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest()->getValues();
$elementID = $request["id"];
$element = CIBlockElement::GetByID($elementID)->Fetch();
$image = CFile::ResizeImageGet($element["PREVIEW_PICTURE"], ["width" => 725, "height"=> 340], BX_RESIZE_IMAGE_PROPORTIONAL, true)["src"];
echo <<<ITEM
	<div class="fast-card">
	    <h1 class="fast-card__title mb-3">{$element["NAME"]}</h1>
	    <figure class="fast-card__image">
	        <img src="{$image}" alt="">
	        <figcaption class="font-weight-light">{$element["NAME"]}</figcaption>
	    </figure>
	    <div class="fast-card__content">
	        {$element["PREVIEW_TEXT"]}
	    </div>
	</div>
ITEM;