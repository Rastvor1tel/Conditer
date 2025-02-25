<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Data\Cache;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
global $analogProducts;
$tmp = [];
$tmpD = [];
$tmp[$arResult['ID']] = $arResult['PREVIEW_PICTURE'];
if($arResult['OFFERS']){
    foreach($arResult['OFFERS'] as $offer){
        $tmp[$offer['ID']] = $offer['PREVIEW_PICTURE'];
        $tmpD[$offer['ID']] = $offer['DETAIL_PICTURE'];
    }
}

$checkSlider = $arParams['ADD_DETAIL_TO_SLIDER'];
$arParams['ADD_DETAIL_TO_SLIDER'] = "N";
$arTmpPhoto = $arResult["MORE_PHOTO"];
unset($arResult["MORE_PHOTO"]);
$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();
$arResult["MORE_PHOTO"] = $arTmpPhoto;
$arParams['ADD_DETAIL_TO_SLIDER'] = $checkSlider;
unset($arTmpPhoto);

$arResult[$arResult['ID']] = $tmp[$arResult['ID']];

if($arResult['JS_OFFERS']){
    foreach($arResult['JS_OFFERS'] as $i => $offer)
    {
        $arResult['JS_OFFERS'][$i]['PREVIEW_PICTURE'] = $tmp[$offer['ID']];
        $arResult['JS_OFFERS'][$i]['DETAIL_PICTURE'] = $tmpD[$offer['ID']];
    }
}

$template = $this->__name;
if($this->__name == '.default'){
    $template = '';
}
$arResult['TEMPLATE'] = $template;

$arResult['SKU'] = $arResult['ID'];
if($arResult['OFFERS'])
{
    $arResult['SKU'] = $arResult['OFFERS'][0]['ID'];
}

//\SotbitOrigami::checkPriceDiscount($arResult);

//$arResult = \SotbitOrigami::changeColorImages($arResult);

//$Element = new \Sotbit\Origami\Image\Element($template);
//$arResult = $Element->prepareImages($arResult);

//$color = \Sotbit\Origami\Helper\Color::getInstance(SITE_ID);
//$arResult = $color::changePropColorView($arResult, $arParams)['RESULT'];


//$arResult["ITEM_PRICE_DELTA"] = \SotbitOrigami::getPriceDelta($arResult, $template);


//$arResult['BRAND'] = [];
//if($arParams['BRAND_USE'] && $arParams['BRAND_PROP_CODE']){
//    $Brand = new \Sotbit\Origami\Brand($template);
//    $Brand->setBrandProps($arParams['BRAND_PROP_CODE']);
//    $Brand->setResize(['width' => 205,'height' => 50,'type' => BX_RESIZE_IMAGE_PROPORTIONAL]);
//    $arResult['BRAND'] = $Brand->findBrandsForElement($arResult['PROPERTIES']);
//}
/**/

//$arResult['VIDEO'] = [];
//$videoProp = Option::get('PROP_VIDEO_'.$template);
//if($arResult['PROPERTIES'][$videoProp]['VALUE']){
//    foreach($arResult['PROPERTIES'][$videoProp]['VALUE'] as $url){
//        $Video = new \Sotbit\Origami\Video($url);
//        $arResult['VIDEO'][] = $Video->getContent();
//    }
//}

if($arResult['OFFERS'] && $arResult['SKU_PROPS']) {
    foreach($arResult['SKU_PROPS'] as $code => $sku) {
        $values = [];
        $table = '';
        foreach ($arResult['OFFERS'] as $i => $o) {
            if ($o['PROPERTIES'][$code]['VALUE']) {
                $table = $o['PROPERTIES'][$code]['USER_TYPE_SETTINGS']['TABLE_NAME'];
                $values[$i] = $o['PROPERTIES'][$code]['VALUE'];
            }
        }

        if ($table && $values) {
            $HL = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                "filter" => [
                    'TABLE_NAME' => $table,
                ],
                'limit'  => 1,
            ])->Fetch();
            if ($HL['ID'] > 0) {
                $HLEntity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($HL)->getDataClass();
                $rs = $HLEntity::getList([
                    'filter' => [
                        'UF_XML_ID' => $values,
                    ]
                ]);
                while ($row = $rs->fetch()) {
                    foreach($arResult['OFFERS'] as $i => $o){
                        if($o['PROPERTIES'][$code]['VALUE'] == $row['UF_XML_ID']){
                            $arResult['OFFERS'][$i]['PROPERTIES'][$code]['DISPLAY_VALUE'] = mb_convert_case($row['UF_NAME'], MB_CASE_TITLE, "UTF-8");
                        }
                    }
                }
            }
        }
    }
}


//$colorCode = \Sotbit\Origami\Helper\Config::get('COLOR');
//if($arResult['SKU_PROPS'][$colorCode]) {
//    $tmp = [$colorCode => $arResult['SKU_PROPS'][$colorCode]];
//    foreach($arResult['SKU_PROPS'] as $code => $prop){
//        if($code == $colorCode){
//            continue;
//        }
//        $tmp[$code] = $prop;
//    }
//    $arResult['SKU_PROPS'] = $tmp;
//}

//if (Bitrix\Main\Loader::includeModule( "sotbit.price" ))
//{
    //$arResult = SotbitPrice::ChangeMinPrice( $arResult );
//}
//if (Bitrix\Main\Loader::includeModule( "sotbit.regions" ))
//{
    //$arResult = \Sotbit\Regions\Sale\Price::change( $arResult );
//}

$arResult["SHOW_BUY"] = 0;
$arResult["SHOW_DELAY"] = 0;
$arResult["SHOW_COMPARE"] = 0;

if(isset($arParams['ACTION_PRODUCTS']))
{
    if(in_array("BUY", $arParams['ACTION_PRODUCTS']))
        $arResult["SHOW_BUY"] = 1;

    if(in_array("DELAY", $arParams['ACTION_PRODUCTS']))
        $arResult["SHOW_DELAY"] = 1;

    if(in_array("COMPARE", $arParams['ACTION_PRODUCTS']))
        $arResult["SHOW_COMPARE"] = 1;
}

$this->__component->arResultCacheKeys = array_merge( $this->__component->arResultCacheKeys, [
    'ADVANTAGES_SECTIONS',
    'OFFERS',
    'SKU_PROPS',
    'PROPERTIES',
    'ITEM_MEASURE',
    'ITEM_MEASURE_RATIOS',
    //'TABS',
    'ID',
    'OFFERS_SELECTED',
    'SECTION',
    'IBLOCK_ID',
    'QUANTITY',
    'PRODUCT_PROVIDER_CLASS',
    'MODULE',
    'OFFERS_IBLOCK',
    'CATALOG',
    'OFFERS_ID',
    'SHOW_BUY',
    'SHOW_DELAY',
    'SHOW_COMPARE'
] );
?>