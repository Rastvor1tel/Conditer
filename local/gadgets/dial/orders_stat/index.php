<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc, Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

Asset::getInstance()->addCss($arGadget['PATH_SITEROOT'] . '/styles.css');

$gadget = new DialGadgets();
$prices = [
    "DAY"   => $gadget->getOrdersLast("day", $arParams["G_ORDERS_STAT_STATUS"]),
    "MONTH" => $gadget->getOrdersLast("month", $arParams["G_ORDERS_STAT_STATUS"]),
    "YEAR"  => $gadget->getOrdersLast("year", $arParams["G_ORDERS_STAT_STATUS"]),
];
?>

<?= GetMessage("GD_DIAL_ORDER_DAY", ["#COUNT#" => $prices["DAY"]["COUNT"], "#PRICE#" => $prices["DAY"]["PRICE"]]) ?><br>
<?= GetMessage("GD_DIAL_ORDER_MONTH", ["#COUNT#" => $prices["MONTH"]["COUNT"], "#PRICE#" => $prices["MONTH"]["PRICE"]]) ?><br>
<?= GetMessage("GD_DIAL_ORDER_YEAR", ["#COUNT#" => $prices["YEAR"]["COUNT"], "#PRICE#" => $prices["YEAR"]["PRICE"]]) ?>