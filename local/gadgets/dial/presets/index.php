<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader, Bitrix\Main\Localization\Loc, Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

Asset::getInstance()->addCss($arGadget['PATH_SITEROOT'] . '/styles.css');
global $USER;
$idUser = intval($USER->GetID());

if (Loader::includeModule('sale') && $idUser > 0) {
    $presets = new DialGadgets();
    $listPresets = $presets->getRecomendedOrders();
    array_walk($listPresets, function($item) {
    	echo <<<ITEM
			<div class="preset-container-link">
				<a class="preset-title-link" href="#" data-id="{$item["ID"]}">
					<div class="preset-title">{$item["NAME"]}</div>
				</a>
			</div>
		ITEM;
    });
    echo <<<EDIT
		<a class="preset-edit-link" href="/orders/recomended/">Изменить</a>
	EDIT;

}
?>