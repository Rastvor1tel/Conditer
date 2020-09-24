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
    $buyers = new DialGadgets();
    $listBuyers = $buyers->findBuyersForUser($idUser);
    echo <<<TITLE
        <div class="organisation_item title">
            <div>Название</div>
            <div>ДЗ</div>
            <div>Просроченная ДЗ</div>
        </div>
    TITLE;
    foreach ($listBuyers as $idBuyer => $buyer) {
        $dz = CCurrencyLang::CurrencyFormat($buyer["DEBET"], "RUB");
        $dzExpired = CCurrencyLang::CurrencyFormat($buyer["EXPIRED_DEBET"], "RUB");
        $stopClass = $buyer["STOP"] == "Y" ? ' stopItem' : '';
        $itemName = $buyer["COMPANY"] . ($buyer["STOP"] == "Y" ? '<sup>(запрет)</sup>' : '');
        if ($buyer["DEBET"] || $buyer["EXPIRED_DEBET"]) {
            echo <<<COMPANY
                <div class="organisation_item{$stopClass}">
                    <div>{$itemName}</div>
                    <div>{$dz}</div>
                    <div>{$dzExpired}</div>
                </div>
            COMPANY;
        }
    }
}
?>