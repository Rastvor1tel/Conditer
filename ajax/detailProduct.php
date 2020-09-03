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

/*
?>

<div class="fast-card">
    <h1 class="fast-card__title mb-3">Корм для кошек влажный</h1>
    <figure class="fast-card__image">
        <img src="https://foodservice766722936.files.wordpress.com/2020/07/d092d0b8d181d0bad0b0d181-d0b4d0bbd18f-d0bad0bed182d18fd182-d0b6d0b5d0bbd0b5-d182d0b5d0bbd18fd182-28-d188d182.-85-d0b3.jpg" alt="">
        <figcaption class="font-weight-light">Вискас для котят желе телят 28 шт. 85 г</figcaption>
    </figure>
    <div class="fast-card__content">
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Aliquam at culpa deleniti
            deserunt dicta dolorum, est expedita facere labore natus non obcaecati omnis possimus, quas
            quo saepe temporibus unde vitae.</p>
        <ul>
            <li>Производитель: ООО «Марс».</li>
            <li>Условия хранения: хранить при температуре от +4 до +35 ˚C и относительной влажности
                воздуха не более 75%.
            </li>
            <li>Количество в упаковке (шт.): 28.</li>
            <li>Срок годности: 730 дней.</li>
            <li>Вес продукта (нетто): 85 г.</li>
            <li>Размер продукта<em> </em>В*Ш*Г (м): 0,139*0,092*0,009.</li>
            <li>Размер кейса В*Ш*Г (м): 0,142*0,99*0,386.</li>
            <li>Состав: мясо и субпродукты (в том числе телятина), растительное масло, минеральные
                вещества, витамины, таурин.
            </li>
        </ul>
    </div>
    <div class="fast-card__footer d-flex align-items-center mt-4">
        <div class="fast-card__button btn btn-dark">Кнопка</div>
        <div class="fast-card__button btn btn-light ml-2">Кнопка еще кнопка</div>
    </div>
</div>

*/
?>