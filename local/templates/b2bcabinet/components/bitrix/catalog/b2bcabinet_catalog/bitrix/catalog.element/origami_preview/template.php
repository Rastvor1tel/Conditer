<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use Sotbit\B2bCabinet\Helper\Prop;
use Bitrix\Main\Localization\Loc;

$APPLICATION->ShowAjaxHead();
\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/components/bitrix/catalog/.default/bitrix/catalog.element/origami_preview/style.css");
\Bitrix\Main\Page\Asset::getInstance()->addCss(SITE_TEMPLATE_PATH."/components/bitrix/catalog/.default/bitrix/catalog.element/origami_preview/script.js");

$this->setFrameMode(true);

global $analogProducts;

$templateLibrary = ['popup', 'fx'];
$currencyList = '';

//if (!empty($arResult['CURRENCIES'])) {
//    $templateLibrary[] = 'currency';
//    $currencyList = CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true,
//        true);
//}

$template = $this->__name;
if ($this->__name == '.default') {
    $template = '';
}

$templateData = [
    'TEMPLATE_THEME'   => $arParams['TEMPLATE_THEME'],
    'TEMPLATE_LIBRARY' => $templateLibrary,
    'CURRENCIES'       => $currencyList,
    'ITEM'             => [
        'ID'              => $arResult['ID'],
        'IBLOCK_ID'       => $arResult['IBLOCK_ID'],
        'OFFERS_SELECTED' => $arResult['OFFERS_SELECTED'],
        'JS_OFFERS'       => $arResult['JS_OFFERS'],
    ],
    'OFFERS_ID' => $arResult["OFFERS_ID"]
];
unset($currencyList, $templateLibrary);

$mainId = $this->GetEditAreaId($arResult['ID']);
$itemIds = [
    'ID'                    => $mainId,
    'DISCOUNT_PERCENT_ID'   => $mainId.'_dsc_pict',
    'STICKER_ID'            => $mainId.'_sticker',
    'BIG_SLIDER_ID'         => $mainId.'_big_slider',
    'BIG_IMG_CONT_ID'       => $mainId.'_bigimg_cont',
    'SLIDER_CONT_ID'        => $mainId.'_slider_cont',
    'OLD_PRICE_ID'          => $mainId.'_old_price',
    'PRICE_ID'              => $mainId.'_price',
    'DISCOUNT_PRICE_ID'     => $mainId.'_price_discount',
    'PRICE_TOTAL'           => $mainId.'_price_total',
    'SLIDER_CONT_OF_ID'     => $mainId.'_slider_cont_',
    'QUANTITY_ID'           => $mainId.'_quantity',
    'QUANTITY_DOWN_ID'      => $mainId.'_quant_down',
    'QUANTITY_UP_ID'        => $mainId.'_quant_up',
    'QUANTITY_MEASURE'      => $mainId.'_quant_measure',
    'QUANTITY_LIMIT'        => $mainId.'_quant_limit',
    'BUY_LINK'              => $mainId.'_buy_link',
    'ADD_BASKET_LINK'       => $mainId.'_add_basket_link',
    'BASKET_ACTIONS_ID'     => $mainId.'_basket_actions',
    'NOT_AVAILABLE_MESS'    => $mainId.'_not_avail',
    'COMPARE_LINK'          => $mainId.'_compare_link',
    'WISH_LINK'             => $mainId.'_wish_link',
    'WISH_LINK_MODIFICATION' => $mainId.'_wish_link_modification',
    'TREE_ID'               => $mainId.'_skudiv',
    'DISPLAY_PROP_DIV'      => $mainId.'_sku_prop',
    'DISPLAY_MAIN_PROP_DIV' => $mainId.'_main_sku_prop',
    'OFFER_GROUP'           => $mainId.'_set_group_',
    'BASKET_PROP_DIV'       => $mainId.'_basket_prop',
    'SUBSCRIBE_LINK'        => $mainId.'_subscribe',
    'TABS_ID'               => $mainId.'_tabs',
    'TAB_CONTAINERS_ID'     => $mainId.'_tab_containers',
    'SMALL_CARD_PANEL_ID'   => $mainId.'_small_card_panel',
    'TABS_PANEL_ID'         => $mainId.'_tabs_panel',
    'ALL_PRICES'            => $areaId.'_all_prices',
    'MODIFICATION_ID'       => $mainId.'_modification'
];
$obName
    =
$templateData['JS_OBJ'] = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $mainId);
$name = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']
    : $arResult['NAME'];
$title
    = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']
    : $arResult['NAME'];
$alt = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'])
    ? $arResult['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']
    : $arResult['NAME'];

$haveOffers = !empty($arResult['OFFERS']);
if ($haveOffers)
{
    $actualItem = isset($arResult['OFFERS'][$arResult['OFFERS_SELECTED']])
        ? $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]
        : reset($arResult['OFFERS']);

    $showSliderControls = false;

    $canNotBuyOffers = [];
    foreach ($arResult['OFFERS'] as $offer)
    {
        if(!$offer['CAN_BUY'])
        {
            $canNotBuyOffers[] = $offer;
        }
    }

    foreach ($arResult['OFFERS'] as $offer)
    {
        if ($offer['MORE_PHOTO_COUNT'] >= 1)
        {
            $showSliderControls = true;
            break;
        }
    }
} else {
    $actualItem = $arResult;
    $showSliderControls = $arResult['MORE_PHOTO_COUNT'] >= 1;
}
if($arResult['VIDEO'])
{
    $showSliderControls = true;
}
$skuProps = [];
$price = $actualItem['ITEM_PRICES'][$actualItem['ITEM_PRICE_SELECTED']];
$measureRatio
    = $actualItem['ITEM_MEASURE_RATIOS'][$actualItem['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'];
$showDiscount = $price['PERCENT'] > 0;

$showDescription = !empty($arResult['PREVIEW_TEXT'])
    || !empty($arResult['DETAIL_TEXT']);
$showBuyBtn = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION']);
$buyButtonClassName = in_array('BUY', $arParams['ADD_TO_BASKET_ACTION_PRIMARY'])
    ? 'btn-default' : 'btn-link';
$showAddBtn = in_array('ADD', $arParams['ADD_TO_BASKET_ACTION']);
$showButtonClassName = in_array('ADD',
    $arParams['ADD_TO_BASKET_ACTION_PRIMARY']) ? 'btn-default' : 'btn-link';

$showSubscribe = $arParams['PRODUCT_SUBSCRIPTION'] === 'Y'
    && ($arResult['CATALOG_SUBSCRIBE'] === 'Y' || $haveOffers);

$arParams['MESS_BTN_BUY'] = $arParams['MESS_BTN_BUY']
    ?: Loc::getMessage('CT_BCE_CATALOG_BUY');
$arParams['MESS_BTN_ADD_TO_BASKET'] = $arParams['MESS_BTN_ADD_TO_BASKET']
    ?: Loc::getMessage('CT_BCE_CATALOG_ADD');
$arParams['MESS_NOT_AVAILABLE'] = $arParams['MESS_NOT_AVAILABLE']
    ?: Loc::getMessage('CT_BCE_CATALOG_NOT_AVAILABLE');
$arParams['MESS_BTN_COMPARE'] = $arParams['MESS_BTN_COMPARE']
    ?: Loc::getMessage('CT_BCE_CATALOG_COMPARE');
$arParams['MESS_PRICE_RANGES_TITLE'] = $arParams['MESS_PRICE_RANGES_TITLE']
    ?: Loc::getMessage('CT_BCE_CATALOG_PRICE_RANGES_TITLE');
$arParams['MESS_DESCRIPTION_TAB'] = $arParams['MESS_DESCRIPTION_TAB']
    ?: Loc::getMessage('CT_BCE_CATALOG_DESCRIPTION_TAB');
$arParams['MESS_PROPERTIES_TAB'] = $arParams['MESS_PROPERTIES_TAB']
    ?: Loc::getMessage('CT_BCE_CATALOG_PROPERTIES_TAB');
$arParams['MESS_COMMENTS_TAB'] = $arParams['MESS_COMMENTS_TAB']
    ?: Loc::getMessage('CT_BCE_CATALOG_COMMENTS_TAB');
$arParams['MESS_SHOW_MAX_QUANTITY'] = $arParams['MESS_SHOW_MAX_QUANTITY']
    ?: Loc::getMessage('CT_BCE_CATALOG_SHOW_MAX_QUANTITY');
$arParams['MESS_RELATIVE_QUANTITY_MANY']
    = $arParams['MESS_RELATIVE_QUANTITY_MANY']
    ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_MANY');
$arParams['MESS_RELATIVE_QUANTITY_FEW']
    = $arParams['MESS_RELATIVE_QUANTITY_FEW']
    ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_FEW');
$arParams['MESS_RELATIVE_QUANTITY_NO'] = $arParams['MESS_RELATIVE_QUANTITY_NO']
    ?: Loc::getMessage('CT_BCE_CATALOG_RELATIVE_QUANTITY_NO');

$positionClassMap = [
    'left'   => 'product-item-label-left',
    'center' => 'product-item-label-center',
    'right'  => 'product-item-label-right',
    'bottom' => 'product-item-label-bottom',
    'middle' => 'product-item-label-middle',
    'top'    => 'product-item-label-top',
];

$discountPositionClass = 'product-item-label-big';
if ($arParams['SHOW_DISCOUNT_PERCENT'] === 'Y'
    && !empty($arParams['DISCOUNT_PERCENT_POSITION'])
) {
    foreach (explode('-', $arParams['DISCOUNT_PERCENT_POSITION']) as $pos) {
        $discountPositionClass .= isset($positionClassMap[$pos]) ? ' '
            .$positionClassMap[$pos] : '';
    }
}

$labelPositionClass = 'product-item-label-big';
if (!empty($arParams['LABEL_PROP_POSITION'])) {
    foreach (explode('-', $arParams['LABEL_PROP_POSITION']) as $pos) {
        $labelPositionClass .= isset($positionClassMap[$pos]) ? ' '
            .$positionClassMap[$pos] : '';
    }
}

?>
<div class="quick-view">
    <!--Верстку сюда вставлять-->
</div>


<?
$allPrices = [];

if ($haveOffers && $arParams["FILL_ITEM_ALL_PRICES"] == "Y")
{
    foreach ($arResult['OFFERS'] as $offer)
    {
        $allPrices[$offer['ID']] = $offer['ITEM_ALL_PRICES'][$actualItem['ITEM_PRICE_SELECTED']]["PRICES"];
    }
} elseif($arParams["FILL_ITEM_ALL_PRICES"] == "Y")
{
    $allPrices[$item['ID']] = $arResult['ITEM_ALL_PRICES'][$actualItem['ITEM_PRICE_SELECTED']]["PRICES"];
}

if ($haveOffers)
{
    $offerIds = [];
    $offerCodes = [];

    $useRatio = $arParams['USE_RATIO_IN_RANGES'] === 'Y';

    foreach ($arResult['JS_OFFERS'] as $ind => &$jsOffer)
    {
        $offerIds[] = (int)$jsOffer['ID'];
        $offerCodes[] = $jsOffer['CODE'];

        $fullOffer = $arResult['OFFERS'][$ind];
        $measureName = $fullOffer['ITEM_MEASURE']['TITLE'];

        $strAllProps = '';
        $strMainProps = '';
        $strPriceRangesRatio = '';
        $strPriceRanges = '';

        if ($arResult['SHOW_OFFERS_PROPS'])
        {
            if (!empty($jsOffer['DISPLAY_PROPERTIES']))
            {
                foreach ($jsOffer['DISPLAY_PROPERTIES'] as $property)
                {
                    $current
                        = '<div class="product-preview-info-block-property fonts__middle_comment"><span class="property-title">'
                        .$property['NAME']
                        .': </span><span class="property-value">'.(
                        is_array($property['VALUE'])
                            ? implode(' / ', $property['VALUE'])
                            : $property['VALUE']
                        ).'</span></div>';
                    $strAllProps .= $current;

                    if (isset($arParams['MAIN_BLOCK_OFFERS_PROPERTY_CODE'][$property['CODE']]))
                    {
                        $strMainProps .= $current;
                    }
                }

                unset($current);
            }
        }

        if ($arParams['USE_PRICE_COUNT'] && count($jsOffer['ITEM_QUANTITY_RANGES']) > 1)
        {
            $strPriceRangesRatio = '('.Loc::getMessage(
                    'CT_BCE_CATALOG_RATIO_PRICE',
                    [
                        '#RATIO#' => ($useRatio
                                ? $fullOffer['ITEM_MEASURE_RATIOS'][$fullOffer['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']
                                : '1'
                            ).' '.$measureName,
                    ]
                ).')';

            foreach ($jsOffer['ITEM_QUANTITY_RANGES'] as $range)
            {
                if ($range['HASH'] !== 'ZERO-INF')
                {
                    $itemPrice = false;

                    foreach ($jsOffer['ITEM_PRICES'] as $itemPrice)
                    {
                        if ($itemPrice['QUANTITY_HASH'] === $range['HASH'])
                        {
                            break;
                        }
                    }

                    if ($itemPrice)
                    {
                        $strPriceRanges .= '<dt>'.Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_FROM',
                                [
                                    '#FROM#' => $range['SORT_FROM'].' '
                                        .$measureName,
                                ]
                            ).' ';

                        if (is_infinite($range['SORT_TO']))
                        {
                            $strPriceRanges .= Loc::getMessage('CT_BCE_CATALOG_RANGE_MORE');
                        }else{
                            $strPriceRanges .= Loc::getMessage(
                                'CT_BCE_CATALOG_RANGE_TO',
                                ['#TO#' => $range['SORT_TO'].' '.$measureName]
                            );
                        }

                        $strPriceRanges .= '</dt><dd>'.($useRatio
                                ? $itemPrice['PRINT_RATIO_PRICE']
                                : $itemPrice['PRINT_PRICE']).'</dd>';
                    }
                }
            }

            unset($range, $itemPrice);
        }

        $jsOffer['DISPLAY_PROPERTIES'] = $strAllProps;
        $jsOffer['DISPLAY_PROPERTIES_MAIN_BLOCK'] = $strMainProps;
        $jsOffer['PRICE_RANGES_RATIO_HTML'] = $strPriceRangesRatio;
        $jsOffer['PRICE_RANGES_HTML'] = $strPriceRanges;
    }

    $templateData['OFFER_IDS'] = $offerIds;
    $templateData['OFFER_CODES'] = $offerCodes;
    unset($jsOffer, $strAllProps, $strMainProps, $strPriceRanges, $strPriceRangesRatio, $useRatio);

    $jsParams = [
        'CONFIG'          => [
            'USE_CATALOG'              => $arResult['CATALOG'],
            'SHOW_QUANTITY'            => $arParams['USE_PRODUCT_QUANTITY'],
            'SHOW_PRICE'               => true,
            'SHOW_DISCOUNT_PERCENT'    => $arParams['SHOW_DISCOUNT_PERCENT']
                === 'Y',
            'SHOW_OLD_PRICE'           => $arParams['SHOW_OLD_PRICE'] === 'Y',
            'USE_PRICE_COUNT'          => $arParams['USE_PRICE_COUNT'],
            'DISPLAY_COMPARE'          => $arParams['DISPLAY_COMPARE'],
            'SHOW_SKU_PROPS'           => $arResult['SHOW_OFFERS_PROPS'],
            'OFFER_GROUP'              => $arResult['OFFER_GROUP'],
            'MAIN_PICTURE_MODE'        => $arParams['DETAIL_PICTURE_MODE'],
            'ADD_TO_BASKET_ACTION'     => $arParams['ADD_TO_BASKET_ACTION'],
            'SHOW_CLOSE_POPUP'         => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
            'SHOW_MAX_QUANTITY'        => $arParams['SHOW_MAX_QUANTITY'],
            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
            'TEMPLATE_THEME'           => $arParams['TEMPLATE_THEME'],
            'USE_STICKERS'             => true,
            'USE_SUBSCRIBE'            => $showSubscribe,
            'SHOW_SLIDER'              => $arParams['SHOW_SLIDER'],
            'SLIDER_INTERVAL'          => $arParams['SLIDER_INTERVAL'],
            'ALT'                      => $alt,
            'TITLE'                    => $title,
            'SITE_DIR'                 => SITE_DIR,
            'SITE_ID'                  => SITE_ID,
            'IBLOCK_ID'                => $arParams['IBLOCK_ID'],
            'MAGNIFIER_ZOOM_PERCENT'   => 200,
//            'SHOW_ZOOM'                => Config::get('SHOW_ZOOM_'.$template),
            'USE_ENHANCED_ECOMMERCE'   => $arParams['USE_ENHANCED_ECOMMERCE'],
            'DATA_LAYER_NAME'          => $arParams['DATA_LAYER_NAME'],
            'BRAND_PROPERTY'           => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
                : null,
        ],
        'PRODUCT_TYPE'    => $arResult['CATALOG_TYPE'],
        'VISUAL'          => $itemIds,
        'DEFAULT_PICTURE' => [
            'PREVIEW_PICTURE' => $arResult['DEFAULT_PICTURE'],
            'DETAIL_PICTURE'  => $arResult['DEFAULT_PICTURE'],
        ],
        'MESS'            => [
            'NO' => $arParams['~MESS_RELATIVE_QUANTITY_NO'],
        ],
        'PRODUCT'         => [
            'ID'         => $arResult['ID'],
            'ACTIVE'     => $arResult['ACTIVE'],
            'NAME'       => $arResult['~NAME'],
            'CATEGORY'   => $arResult['CATEGORY_PATH'],
            'ALL_PRICES' => $allPrices,
            'VIDEOS'     => $arResult['VIDEOS'],
        ],
        'BASKET'          => [
            'QUANTITY'         => $arParams['PRODUCT_QUANTITY_VARIABLE'],
            'BASKET_URL' => $arParams['BASKET_URL'],
            'BASKET_URL_AJAX' => SITE_DIR.'include/ajax/buy.php',
            'SKU_PROPS'        => $arResult['OFFERS_PROP_CODES'],
            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
        ],
        'WISH'            => [
            'WISHES'            => [],
            'WISH_URL_TEMPLATE' => SITE_DIR.'include/ajax/wish.php',
        ],
        'OFFERS'          => $arResult['JS_OFFERS'],
        'OFFER_SELECTED'  => $arResult['OFFERS_SELECTED'],
        'TREE_PROPS'      => $skuProps,
    ];
}else{
    $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
    if ($arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y' && !$emptyProductProperties)
    {
        ?>
		<div id="<?= $itemIds['BASKET_PROP_DIV'] ?>" style="display: none;">
            <?
            if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])) {
                foreach (
                    $arResult['PRODUCT_PROPERTIES_FILL'] as $propId => $propInfo
                ) {
                    ?>
					<input type="hidden"
					       name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
					       value="<?= htmlspecialcharsbx($propInfo['ID']) ?>">
                    <?
                    unset($arResult['PRODUCT_PROPERTIES'][$propId]);
                }
            }

            $emptyProductProperties = empty($arResult['PRODUCT_PROPERTIES']);
            if (!$emptyProductProperties)
            {
                ?>
				<table>
                    <?
                    foreach (
                        $arResult['PRODUCT_PROPERTIES'] as $propId => $propInfo
                    ) {
                        ?>
						<tr>
							<td><?= $arResult['PROPERTIES'][$propId]['NAME'] ?></td>
							<td>
                                <?
                                if (
                                    $arResult['PROPERTIES'][$propId]['PROPERTY_TYPE']
                                    === 'L'
                                    && $arResult['PROPERTIES'][$propId]['LIST_TYPE']
                                    === 'C'
                                ) {
                                    foreach (
                                        $propInfo['VALUES'] as $valueId =>
                                        $value
                                    ) {
                                        ?>
										<label>
											<input type="radio"
											       name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]"
											       value="<?= $valueId ?>" <?= ($valueId
                                            == $propInfo['SELECTED']
                                                ? '"checked"' : '') ?>>
                                            <?= $value ?>
										</label>
										<br>
                                        <?
                                    }
                                } else {
                                    ?>
									<select name="<?= $arParams['PRODUCT_PROPS_VARIABLE'] ?>[<?= $propId ?>]">
                                        <?
                                        foreach (
                                            $propInfo['VALUES'] as $valueId =>
                                            $value
                                        ) {
                                            ?>
											<option value="<?= $valueId ?>" <?= ($valueId
                                            == $propInfo['SELECTED']
                                                ? '"selected"' : '') ?>>
                                                <?= $value ?>
											</option>
                                            <?
                                        }
                                        ?>
									</select>
                                    <?
                                }
                                ?>
							</td>
						</tr>
                        <?
                    }
                    ?>
				</table>
                <?
            }
            ?>
		</div>
        <?
    }

//    $jsParams = [
//        'CONFIG'       => [
//            'USE_CATALOG'              => $arResult['CATALOG'],
//            'SHOW_QUANTITY'            => $arParams['USE_PRODUCT_QUANTITY'],
//            'SHOW_PRICE'               => !empty($arResult['ITEM_PRICES']),
//            'SHOW_DISCOUNT_PERCENT'    => $arParams['SHOW_DISCOUNT_PERCENT']
//                === 'Y',
//            'SHOW_OLD_PRICE'           => $arParams['SHOW_OLD_PRICE'] === 'Y',
//            'USE_PRICE_COUNT'          => $arParams['USE_PRICE_COUNT'],
//            'DISPLAY_COMPARE'          => $arParams['DISPLAY_COMPARE'],
//            'MAIN_PICTURE_MODE'        => $arParams['DETAIL_PICTURE_MODE'],
//            'ADD_TO_BASKET_ACTION'     => $arParams['ADD_TO_BASKET_ACTION'],
//            'SHOW_CLOSE_POPUP'         => $arParams['SHOW_CLOSE_POPUP'] === 'Y',
//            'SHOW_MAX_QUANTITY'        => $arParams['SHOW_MAX_QUANTITY'],
//            'RELATIVE_QUANTITY_FACTOR' => $arParams['RELATIVE_QUANTITY_FACTOR'],
//            'TEMPLATE_THEME'           => $arParams['TEMPLATE_THEME'],
//            'USE_STICKERS'             => true,
//            'USE_SUBSCRIBE'            => $showSubscribe,
//            'SHOW_SLIDER'              => $arParams['SHOW_SLIDER'],
//            'SLIDER_INTERVAL'          => $arParams['SLIDER_INTERVAL'],
//            'ALT'                      => $alt,
//            'TITLE'                    => $title,
//            'SITE_DIR'                 => SITE_DIR,
//            'SITE_ID'                  => SITE_ID,
//            'IBLOCK_ID'                => $arParams['IBLOCK_ID'],
////            'SHOW_ZOOM'                => Config::get('SHOW_ZOOM_'.$template),
//            'MAGNIFIER_ZOOM_PERCENT'   => 200,
//            'USE_ENHANCED_ECOMMERCE'   => $arParams['USE_ENHANCED_ECOMMERCE'],
//            'DATA_LAYER_NAME'          => $arParams['DATA_LAYER_NAME'],
//            'BRAND_PROPERTY'           => !empty($arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']])
//                ? $arResult['DISPLAY_PROPERTIES'][$arParams['BRAND_PROPERTY']]['DISPLAY_VALUE']
//                : null,
//        ],
//        'VISUAL'       => $itemIds,
////        'ADD_PRODUCT_TO_BASKET_MODE' => (Config::get('SHOW_POPUP_ADD_BASKET') == 'Y') ? 'popup' : 'no-popup',
//        'PRODUCT_TYPE' => $arResult['CATALOG_TYPE'],
//        'MESS'         => [
//            'NO' => $arParams['~MESS_RELATIVE_QUANTITY_NO'],
//        ],
//        'PRODUCT'      => [
//            'ID'                           => $arResult['ID'],
//            'ACTIVE'                       => $arResult['ACTIVE'],
//            'PICT'                         => reset($arResult['MORE_PHOTO']),
//            'NAME'                         => $arResult['~NAME'],
//            'SUBSCRIPTION'                 => true,
//            'ITEM_PRICE_MODE'              => $arResult['ITEM_PRICE_MODE'],
//            'ITEM_PRICES'                  => $arResult['ITEM_PRICES'],
//            'ITEM_PRICE_SELECTED'          => $arResult['ITEM_PRICE_SELECTED'],
//            'ITEM_QUANTITY_RANGES'         => $arResult['ITEM_QUANTITY_RANGES'],
//            'ITEM_QUANTITY_RANGE_SELECTED' => $arResult['ITEM_QUANTITY_RANGE_SELECTED'],
//            'ITEM_MEASURE_RATIOS'          => $arResult['ITEM_MEASURE_RATIOS'],
//            'ITEM_MEASURE_RATIO_SELECTED'  => $arResult['ITEM_MEASURE_RATIO_SELECTED'],
//            'SLIDER_COUNT'                 => $arResult['MORE_PHOTO_COUNT'],
//            'SLIDER'                       => $arResult['MORE_PHOTO'],
//            'CAN_BUY'                      => $arResult['CAN_BUY'],
//            'CHECK_QUANTITY'               => $arResult['CHECK_QUANTITY'],
//            'QUANTITY_FLOAT'               => is_float($arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO']),
//            'MAX_QUANTITY'                 => $arResult['CATALOG_QUANTITY'],
//            'STEP_QUANTITY'                => $arResult['ITEM_MEASURE_RATIOS'][$arResult['ITEM_MEASURE_RATIO_SELECTED']]['RATIO'],
//            'CATEGORY'                     => $arResult['CATEGORY_PATH'],
//            'ALL_PRICES'                   => $allPrices,
//            'VIDEOS'                       => $arResult['VIDEOS'],
//        ],
//        'BASKET'       => [
//            'ADD_PROPS'        => $arParams['ADD_PROPERTIES_TO_BASKET'] === 'Y',
//            'QUANTITY'         => $arParams['PRODUCT_QUANTITY_VARIABLE'],
//            'PROPS'            => $arParams['PRODUCT_PROPS_VARIABLE'],
//            'EMPTY_PROPS'      => $emptyProductProperties,
//            'BASKET_URL' => $arParams['BASKET_URL'],
//            'BASKET_URL_AJAX' => SITE_DIR.'include/ajax/buy.php',
//            'ADD_URL_TEMPLATE' => $arResult['~ADD_URL_TEMPLATE'],
//            'BUY_URL_TEMPLATE' => $arResult['~BUY_URL_TEMPLATE'],
//        ],
//        'WISH'         => [
//            'WISHES'            => $arResult['WISHES'],
//            'WISH_URL_TEMPLATE' => SITE_DIR.'include/ajax/wish.php',
//        ],
//    ];
    unset($emptyProductProperties);
}

//if ($arParams['DISPLAY_COMPARE']) {
//    $jsParams['COMPARE'] = [
//        'COMPARE_URL_TEMPLATE'        => $arResult['~COMPARE_URL_TEMPLATE'],
//        'COMPARE_DELETE_URL_TEMPLATE' => $arResult['~COMPARE_DELETE_URL_TEMPLATE'],
//        'COMPARE_PATH'                => $arParams['COMPARE_PATH'],
//    ];
//}
?>
	<script>
		//BX.message({
		//	ECONOMY_INFO_MESSAGE: '<?//=GetMessageJS('CT_BCE_CATALOG_ECONOMY_INFO2')?>//',
		//	TITLE_ERROR: '<?//=GetMessageJS('CT_BCE_CATALOG_TITLE_ERROR')?>//',
		//	TITLE_BASKET_PROPS: '<?//=GetMessageJS('CT_BCE_CATALOG_TITLE_BASKET_PROPS')?>//',
		//	BASKET_UNKNOWN_ERROR: '<?//=GetMessageJS('CT_BCE_CATALOG_BASKET_UNKNOWN_ERROR')?>//',
		//	BTN_SEND_PROPS: '<?//=GetMessageJS('CT_BCE_CATALOG_BTN_SEND_PROPS')?>//',
		//	BTN_MESSAGE_BASKET_REDIRECT: '<?//=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_BASKET_REDIRECT')?>//',
		//	BTN_MESSAGE_CLOSE: '<?//=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE')?>//',
		//	BTN_MESSAGE_CLOSE_POPUP: '<?//=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_CLOSE_POPUP')?>//',
		//	TITLE_SUCCESSFUL: '<?//=GetMessageJS('CT_BCE_CATALOG_ADD_TO_BASKET_OK')?>//',
		//	COMPARE_MESSAGE_OK: '<?//=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_OK')?>//',
		//	COMPARE_UNKNOWN_ERROR: '<?//=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_UNKNOWN_ERROR')?>//',
		//	COMPARE_TITLE: '<?//=GetMessageJS('CT_BCE_CATALOG_MESS_COMPARE_TITLE')?>//',
		//	BTN_MESSAGE_COMPARE_REDIRECT: '<?//=GetMessageJS('CT_BCE_CATALOG_BTN_MESSAGE_COMPARE_REDIRECT')?>//',
		//	PRODUCT_GIFT_LABEL: '<?//=GetMessageJS('CT_BCE_CATALOG_PRODUCT_GIFT_LABEL')?>//',
		//	PRICE_TOTAL_PREFIX: '<?//=GetMessageJS('CT_BCE_CATALOG_MESS_PRICE_TOTAL_PREFIX')?>//',
		//	RELATIVE_QUANTITY_MANY: '<?//=$arParams['~MESS_RELATIVE_QUANTITY_MANY']?>//',
		//	RELATIVE_QUANTITY_FEW: '<?//=$arParams['~MESS_RELATIVE_QUANTITY_FEW']?>//',
		//	RELATIVE_QUANTITY_NO: '<?//=$arParams['~MESS_RELATIVE_QUANTITY_NO']?>//',
		//	SITE_ID: '<?//=CUtil::JSEscape($component->getSiteId())?>//',
		//	WISH_TO: '<?//=GetMessageJS('WISH_TO')?>//',
		//	WISH_IN: '<?//=GetMessageJS('WISH_IN')?>//',
		//});
        //
        //var <?//=$obName?>// = new JCCatalogElementView(<?//=CUtil::PhpToJSObject($jsParams, false, true)?>//);

	</script>
<?
unset($actualItem, $itemIds, $jsParams);
?>
