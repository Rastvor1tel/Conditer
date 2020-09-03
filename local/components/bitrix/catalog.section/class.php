<?
use \Bitrix\Main;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Error;
use Bitrix\Main\Type\Collection;
use \Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Iblock;
use \Bitrix\Iblock\Component\ElementList;
use \Bitrix\Catalog;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CIntranetToolbar $INTRANET_TOOLBAR
 */

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('iblock'))
{
	ShowError(Loc::getMessage('IBLOCK_MODULE_NOT_INSTALLED'));
	return;
}

class CatalogSectionComponent extends ElementList
{
	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->setExtendedMode(false)->setMultiIblockMode(false)->setPaginationMode(true);
	}

	public function onPrepareComponentParams($params)
	{/*
        $SKU = CIBlockElement::SubQuery("PROPERTY_CML2_LINK", [
            "=PROPERTY_XML_ID_CONN" => PRICE_ID,
        ]);
        $GLOBALS[$params['FILTER_NAME']]["ID"] = $SKU;

        $params['OFFERS_LIMIT'] = 100;*/

        $resSection = CIBlockSection::GetList(false, ['IBLOCK_ID' => $params['IBOCK_ID'], 'XML_ID' => $_SESSION['PRICE_ID']])->Fetch();
        $GLOBALS[$params['FILTER_NAME']][">SUBSECTION"] = $resSection['ID'];
        $GLOBALS[$params['FILTER_NAME']][">DEPTH_LEVEL"] = 1;
        $GLOBALS[$params['FILTER_NAME']][">LEFT_MARGIN"] = $resSection['LEFT_MARGIN'];
        $GLOBALS[$params['FILTER_NAME']]["<RIGHT_MARGIN"] = $resSection['RIGHT_MARGIN'];

        $params = parent::onPrepareComponentParams($params);
		$params['IBLOCK_TYPE'] = isset($params['IBLOCK_TYPE']) ? trim($params['IBLOCK_TYPE']) : '';

		if ((int)$params['SECTION_ID'] > 0 && (int)$params['SECTION_ID'].'' != $params['SECTION_ID'] && Loader::includeModule('iblock'))
		{
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
			return $params;
		}

		$params['SECTION_ID_VARIABLE'] = (isset($params['SECTION_ID_VARIABLE']) ? trim($params['SECTION_ID_VARIABLE']) : '');
		if ($params['SECTION_ID_VARIABLE'] == '' || !preg_match(self::PARAM_TITLE_MASK, $params['SECTION_ID_VARIABLE']))
			$params['SECTION_ID_VARIABLE'] = 'SECTION_ID';

		$params['SHOW_ALL_WO_SECTION'] = isset($params['SHOW_ALL_WO_SECTION']) && $params['SHOW_ALL_WO_SECTION'] === 'Y';
		$params['USE_MAIN_ELEMENT_SECTION'] = isset($params['USE_MAIN_ELEMENT_SECTION']) && $params['USE_MAIN_ELEMENT_SECTION'] === 'Y';
		$params['SECTIONS_CHAIN_START_FROM'] = isset($params['SECTIONS_CHAIN_START_FROM']) ? (int)$params['SECTIONS_CHAIN_START_FROM'] : 0;

		$params['BACKGROUND_IMAGE'] = isset($params['BACKGROUND_IMAGE']) ? trim($params['BACKGROUND_IMAGE']) : '';
		if ($params['BACKGROUND_IMAGE'] === '-')
		{
			$params['BACKGROUND_IMAGE'] = '';
		}

		// compatibility for bigData case with zero initial elements
		if ($params['PAGE_ELEMENT_COUNT'] <= 0 && !isset($params['PRODUCT_ROW_VARIANTS']))
		{
			$params['PAGE_ELEMENT_COUNT'] = 20;
		}

		$params['CUSTOM_CURRENT_PAGE'] = isset($params['CUSTOM_CURRENT_PAGE']) ? trim($params['CUSTOM_CURRENT_PAGE']) : '';

		$params['COMPATIBLE_MODE'] = (isset($params['COMPATIBLE_MODE']) && $params['COMPATIBLE_MODE'] === 'N' ? 'N' : 'Y');
		if ($params['COMPATIBLE_MODE'] === 'N')
		{
			$params['DISABLE_INIT_JS_IN_COMPONENT'] = 'Y';
			$params['OFFERS_LIMIT'] = 0;
		}

		$this->setCompatibleMode($params['COMPATIBLE_MODE'] === 'Y');

		$params['DISABLE_INIT_JS_IN_COMPONENT'] = isset($params['DISABLE_INIT_JS_IN_COMPONENT']) && $params['DISABLE_INIT_JS_IN_COMPONENT'] === 'Y' ? 'Y' : 'N';

		if ($params['DISABLE_INIT_JS_IN_COMPONENT'] !== 'Y')
		{
			CJSCore::Init(array('popup'));
		}

		return $params;
	}

	protected function processResultData()
	{
		if ($this->initSectionResult())
		{
			$this->initSectionProperties();
            $this->iblockProducts = $this->getProductsSeparatedByIblock();
            $this->checkIblock();

            if ($this->hasErrors())
                return;

            $this->initCurrencyConvert();
            $this->initCatalogInfo();
            $this->initIblockPropertyFeatures();
            $this->initPrices();
            $this->initVats();
            $this->initUrlTemplates();

            $this->initElementList();
            if (!$this->hasErrors())
            {
                $this->sortElementList();
                $this->makeElementLinks();
                $this->prepareData();
                $this->filterPureOffers();
                $this->makeOutputResult();
            }
		}
	}

    protected function prepareData()
    {
        $this->clearItems();
        $this->initCatalogDiscountCache();
        $this->processProducts();
        $this->processOffers();
        $this->makeOutputResult();
        $this->clearItems();
    }

    /**
     * Load, calculate and fill data (prices, measures, discounts, deprecated fields) for offers.
     * Link offers to products.
     *
     * @return void
     */
    protected function processOffers()
    {
        if ($this->useCatalog && !empty($this->iblockProducts))
        {
            $offers = array();

            $paramStack = array();
            $enableCompatible = $this->isEnableCompatible();
            if ($enableCompatible)
            {
                $paramStack['USE_PRICE_COUNT'] = $this->arParams['USE_PRICE_COUNT'];
                $paramStack['SHOW_PRICE_COUNT'] = $this->arParams['SHOW_PRICE_COUNT'];
                $this->arParams['USE_PRICE_COUNT'] = false;
                $this->arParams['SHOW_PRICE_COUNT'] = 1;
            }

            foreach (array_keys($this->iblockProducts) as $iblock)
            {
                if (!empty($this->productWithOffers[$iblock]))
                {
                    $iblockOffers = $this->getIblockOffers($iblock);
                    if (!empty($iblockOffers))
                    {
                        $offersId = array_keys($iblockOffers);
                        $this->initItemsMeasure($iblockOffers);
                        $this->loadMeasures($this->getMeasureIds($iblockOffers));

                        $this->loadMeasureRatios($offersId);

                        $this->loadPrices($offersId);
                        $this->calculateItemPrices($iblockOffers);

                        $this->transferItems($iblockOffers);

                        $this->modifyOffers($iblockOffers);
                        $this->chooseOffer($iblockOffers, $iblock);

                        $offers = array_merge($offers, $iblockOffers);
                    }
                    unset($iblockOffers);
                }
            }
            if ($enableCompatible)
            {
                $this->arParams['USE_PRICE_COUNT'] = $paramStack['USE_PRICE_COUNT'];
                $this->arParams['SHOW_PRICE_COUNT'] = $paramStack['SHOW_PRICE_COUNT'];
            }
            unset($enableCompatible, $paramStack);
        }
    }

    protected function modifyOffers($offers)
    {
        //$urls = $this->storage['URLS'];

        foreach ($offers as &$offer)
        {
            $elementId = $offer['LINK_ELEMENT_ID'];

            if (!isset($this->elementLinks[$elementId]))
                continue;

            $offer['CAN_BUY'] = $this->elementLinks[$elementId]['ACTIVE'] === 'Y' && $offer['CAN_BUY'];

            $this->elementLinks[$elementId]['OFFERS'][] = $offer;

            unset($elementId, $offer);
        }
    }

	protected function initSectionResult()
	{
		$success = true;
		$selectFields = array();

		if (!empty($this->arParams['SECTION_USER_FIELDS']) && is_array($this->arParams['SECTION_USER_FIELDS']))
		{
			foreach ($this->arParams['SECTION_USER_FIELDS'] as $field)
			{
				if (is_string($field) && preg_match('/^UF_/', $field))
				{
					$selectFields[] = $field;
				}
			}
		}

		if (preg_match('/^UF_/', $this->arParams['META_KEYWORDS']))
		{
			$selectFields[] = $this->arParams['META_KEYWORDS'];
		}

		if (preg_match('/^UF_/', $this->arParams['META_DESCRIPTION']))
		{
			$selectFields[] = $this->arParams['META_DESCRIPTION'];
		}

		if (preg_match('/^UF_/', $this->arParams['BROWSER_TITLE']))
		{
			$selectFields[] = $this->arParams['BROWSER_TITLE'];
		}

		if (preg_match('/^UF_/', $this->arParams['BACKGROUND_IMAGE']))
		{
			$selectFields[] = $this->arParams['BACKGROUND_IMAGE'];
		}

		$filterFields = array(
			'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			'IBLOCK_ACTIVE' => 'Y',
			'ACTIVE' => 'Y',
			'GLOBAL_ACTIVE' => 'Y',
		);

		// Hidden tricky parameter USED to display linked
		// by default it is not set
		if ($this->arParams['BY_LINK'] === 'Y')
		{
			$sectionResult = array(
				'ID' => 0,
				'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			);
		}
		elseif ($this->arParams['SECTION_ID'] > 0)
		{
			$filterFields['ID'] = $this->arParams['SECTION_ID'];
			$sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
			$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
			$sectionResult = $sectionIterator->GetNext();
		}
		elseif (strlen($this->arParams['SECTION_CODE']) > 0)
		{
			$filterFields['=CODE'] = $this->arParams['SECTION_CODE'];
			$sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
			$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
			$sectionResult = $sectionIterator->GetNext();
		}
		elseif (strlen($this->arParams['SECTION_CODE_PATH']) > 0)
		{
			$sectionId = CIBlockFindTools::GetSectionIDByCodePath($this->arParams['IBLOCK_ID'], $this->arParams['SECTION_CODE_PATH']);
			if ($sectionId)
			{
				$filterFields['ID'] = $sectionId;
				$sectionIterator = CIBlockSection::GetList(array(), $filterFields, false, $selectFields);
				$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				$sectionResult = $sectionIterator->GetNext();
			}
		}
		else	// Root section (no section filter)
		{
			$sectionResult = array(
				'ID' => 0,
				'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
			);
		}

		if (empty($sectionResult))
		{
			$success = false;
			$this->abortResultCache();
			$this->errorCollection->setError(new Error(Loc::getMessage('CATALOG_SECTION_NOT_FOUND'), self::ERROR_404));
		}
		else
		{
			$this->arResult = array_merge($this->arResult, $sectionResult);
			if ($this->arResult['ID'] > 0 && $this->arParams['ADD_SECTIONS_CHAIN'])
			{
				$this->arResult['PATH'] = array();
				$pathIterator = CIBlockSection::GetNavChain(
					$this->arResult['IBLOCK_ID'],
					$this->arResult['ID'],
					array(
						'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
						'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
						'DEPTH_LEVEL', 'SECTION_PAGE_URL'
					)
				);
				$pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				while ($path = $pathIterator->GetNext())
				{
					$ipropValues = new Iblock\InheritedProperty\SectionValues($this->arParams['IBLOCK_ID'], $path['ID']);
					$path['IPROPERTY_VALUES'] = $ipropValues->getValues();
					$this->arResult['PATH'][] = $path;
				}

				if ($this->arParams['SECTIONS_CHAIN_START_FROM'] > 0)
				{
					$this->arResult['PATH'] = array_slice($this->arResult['PATH'], $this->arParams['SECTIONS_CHAIN_START_FROM']);
				}
			}
		}

		return $success;
	}

	protected function initSectionProperties()
	{
		$arResult =& $this->arResult;

		$arResult['IPROPERTY_VALUES'] = array();
		if ($arResult['ID'] > 0)
		{
			$ipropValues = new Iblock\InheritedProperty\SectionValues($arResult['IBLOCK_ID'], $arResult['ID']);
			$arResult['IPROPERTY_VALUES'] = $ipropValues->getValues();
		}

		Iblock\Component\Tools::getFieldImageData(
			$arResult,
			array('PICTURE', 'DETAIL_PICTURE'),
			Iblock\Component\Tools::IPROPERTY_ENTITY_SECTION,
			'IPROPERTY_VALUES'
		);

		$arResult['BACKGROUND_IMAGE'] = false;
		if ($this->arParams['BACKGROUND_IMAGE'] != '' && !empty($arResult[$this->arParams['BACKGROUND_IMAGE']]))
		{
			$arResult['BACKGROUND_IMAGE'] = CFile::GetFileArray($arResult[$this->arParams['BACKGROUND_IMAGE']]);
		}
	}

	protected function initCatalogInfo()
	{
		parent::initCatalogInfo();
		$useCatalogButtons = array();
		if (
			!empty($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
			&& is_array($this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']])
		)
		{
			$catalogType = $this->storage['CATALOGS'][$this->arParams['IBLOCK_ID']]['CATALOG_TYPE'];
			if ($catalogType == CCatalogSku::TYPE_CATALOG || $catalogType == CCatalogSku::TYPE_FULL)
			{
				$useCatalogButtons['add_product'] = true;
			}

			if ($catalogType == CCatalogSku::TYPE_PRODUCT || $catalogType == CCatalogSku::TYPE_FULL)
			{
				$useCatalogButtons['add_sku'] = true;
			}
			unset($catalogType);
		}

		$this->storage['USE_CATALOG_BUTTONS'] = $useCatalogButtons;
	}

	protected function getCacheKeys()
	{
		return array(
			'ID',
			'NAV_CACHED_DATA',
			'NAV_STRING',
			$this->arParams['META_KEYWORDS'],
			$this->arParams['META_DESCRIPTION'],
			$this->arParams['BROWSER_TITLE'],
			$this->arParams['BACKGROUND_IMAGE'],
			'NAME',
			'PATH',
			'IBLOCK_SECTION_ID',
			'IPROPERTY_VALUES',
			'ITEMS_TIMESTAMP_X',
			'BACKGROUND_IMAGE',
			'USE_CATALOG_BUTTONS'
		);
	}

	protected function getFilter()
	{
		$filterFields = parent::getFilter();

		if ($this->getAction() === 'bigDataLoad')
		{
			return $filterFields;
		}

		$filterFields['INCLUDE_SUBSECTIONS'] = $this->arParams['INCLUDE_SUBSECTIONS'] === 'N' ? 'N' : 'Y';

		if ($this->arParams['INCLUDE_SUBSECTIONS'] === 'A')
		{
			$filterFields['SECTION_GLOBAL_ACTIVE'] = 'Y';
		}

		if ($this->arParams['BY_LINK'] !== 'Y')
		{
			if ($this->arResult['ID'])
			{
				$filterFields['SECTION_ID'] = $this->arResult['ID'];
			}
			elseif (!$this->arParams['SHOW_ALL_WO_SECTION'])
			{
				$filterFields['SECTION_ID'] = 0;
			}
			else
			{
				unset($filterFields['INCLUDE_SUBSECTIONS']);
				unset($filterFields['SECTION_GLOBAL_ACTIVE']);
			}
		}

		return $filterFields;
	}

	protected function makeOutputResult()
	{
		parent::makeOutputResult();
		$this->arResult['USE_CATALOG_BUTTONS'] = $this->storage['USE_CATALOG_BUTTONS'];
	}

	protected function initialLoadAction()
	{
		parent::initialLoadAction();

		if (!$this->hasErrors())
		{
			$this->initAdminIconsPanel();
			$this->setTemplateCachedData($this->arResult['NAV_CACHED_DATA']);
			$this->initMetaData();
		}
	}

	protected function initAdminIconsPanel()
	{
		global $APPLICATION, $INTRANET_TOOLBAR, $USER;

		if (!$USER->IsAuthorized())
		{
			return;
		}

		$arResult =& $this->arResult;

		if (
			$APPLICATION->GetShowIncludeAreas()
			|| (is_object($INTRANET_TOOLBAR) && $this->arParams['INTRANET_TOOLBAR'] !== 'N')
			|| $this->arParams['SET_TITLE']
			|| isset($arResult[$this->arParams['BROWSER_TITLE']])
		)
		{
			if (Loader::includeModule('iblock'))
			{
				$urlDeleteSectionButton = '';

				if ($arResult['IBLOCK_SECTION_ID'] > 0)
				{
					$sectionIterator = CIBlockSection::GetList(
						array(),
						array('=ID' => $arResult['IBLOCK_SECTION_ID']),
						false,
						array('SECTION_PAGE_URL')
					);
					$sectionIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
					$section = $sectionIterator->GetNext();
					$urlDeleteSectionButton = $section['SECTION_PAGE_URL'];
				}

				if (empty($urlDeleteSectionButton))
				{
					$urlTemplate = CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'LIST_PAGE_URL');
					$iblock = CIBlock::GetArrayByID($this->arParams['IBLOCK_ID']);
					$iblock['IBLOCK_CODE'] = $iblock['CODE'];
					$urlDeleteSectionButton = CIBlock::ReplaceDetailUrl($urlTemplate, $iblock, true, false);
				}

				$returnUrl = array(
					'add_section' => (
					strlen($this->arParams['SECTION_URL'])
						? $this->arParams['SECTION_URL']
						: CIBlock::GetArrayByID($this->arParams['IBLOCK_ID'], 'SECTION_PAGE_URL')
					),
					'delete_section' => $urlDeleteSectionButton,
				);
				$buttonParams = array(
					'RETURN_URL' => $returnUrl,
					'CATALOG' => true
				);

				if (isset($arResult['USE_CATALOG_BUTTONS']))
				{
					$buttonParams['USE_CATALOG_BUTTONS'] = $arResult['USE_CATALOG_BUTTONS'];
				}

				$buttons = CIBlock::GetPanelButtons(
					$this->arParams['IBLOCK_ID'],
					0,
					$arResult['ID'],
					$buttonParams
				);
				unset($buttonParams);

				if ($APPLICATION->GetShowIncludeAreas())
				{
					$this->addIncludeAreaIcons(CIBlock::GetComponentMenu($APPLICATION->GetPublicShowMode(), $buttons));
				}

				if (
					is_array($buttons['intranet'])
					&& is_object($INTRANET_TOOLBAR)
					&& $this->arParams['INTRANET_TOOLBAR'] !== 'N'
				)
				{
					Main\Page\Asset::getInstance()->addJs('/bitrix/js/main/utils.js');

					foreach ($buttons['intranet'] as $button)
					{
						$INTRANET_TOOLBAR->AddButton($button);
					}
				}

				if ($this->arParams['SET_TITLE'] || isset($arResult[$this->arParams['BROWSER_TITLE']]))
				{
					$this->storage['TITLE_OPTIONS'] = array(
						'ADMIN_EDIT_LINK' => $buttons['submenu']['edit_section']['ACTION'],
						'PUBLIC_EDIT_LINK' => $buttons['edit']['edit_section']['ACTION'],
						'COMPONENT_NAME' => $this->getName(),
					);
				}
			}
		}
	}
	
	protected function initMetaData()
	{
		global $APPLICATION;

		if ($this->arParams['SET_TITLE'])
		{
			if ($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '')
			{
				$APPLICATION->SetTitle($this->arResult['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $this->storage['TITLE_OPTIONS']);
			}
			elseif (isset($this->arResult['NAME']))
			{
				$APPLICATION->SetTitle($this->arResult['NAME'], $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_BROWSER_TITLE'] === 'Y')
		{
			$browserTitle = Main\Type\Collection::firstNotEmpty(
				$this->arResult, $this->arParams['BROWSER_TITLE'],
				$this->arResult['IPROPERTY_VALUES'], 'SECTION_META_TITLE'
			);
			if (is_array($browserTitle))
			{
				$APPLICATION->SetPageProperty('title', implode(' ', $browserTitle), $this->storage['TITLE_OPTIONS']);
			}
			elseif ($browserTitle != '')
			{
				$APPLICATION->SetPageProperty('title', $browserTitle, $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_META_KEYWORDS'] === 'Y')
		{
			$metaKeywords = Main\Type\Collection::firstNotEmpty(
				$this->arResult, $this->arParams['META_KEYWORDS'],
				$this->arResult['IPROPERTY_VALUES'], 'SECTION_META_KEYWORDS'
			);
			if (is_array($metaKeywords))
			{
				$APPLICATION->SetPageProperty('keywords', implode(' ', $metaKeywords), $this->storage['TITLE_OPTIONS']);
			}
			elseif ($metaKeywords != '')
			{
				$APPLICATION->SetPageProperty('keywords', $metaKeywords, $this->storage['TITLE_OPTIONS']);
			}
		}

		if ($this->arParams['SET_META_DESCRIPTION'] === 'Y')
		{
			$metaDescription = Main\Type\Collection::firstNotEmpty(
				$this->arResult, $this->arParams['META_DESCRIPTION'],
				$this->arResult['IPROPERTY_VALUES'], 'SECTION_META_DESCRIPTION'
			);
			if (is_array($metaDescription))
			{
				$APPLICATION->SetPageProperty('description', implode(' ', $metaDescription), $this->storage['TITLE_OPTIONS']);
			}
			elseif ($metaDescription != '')
			{
				$APPLICATION->SetPageProperty('description', $metaDescription, $this->storage['TITLE_OPTIONS']);
			}
		}

		if (!empty($this->arResult['BACKGROUND_IMAGE']) && is_array($this->arResult['BACKGROUND_IMAGE']))
		{
			$APPLICATION->SetPageProperty(
				'backgroundImage',
				'style="background-image: url(\''.\CHTTP::urnEncode($this->arResult['BACKGROUND_IMAGE']['SRC'], 'UTF-8').'\')"'
			);
		}

		if ($this->arParams['ADD_SECTIONS_CHAIN'] && is_array($this->arResult['PATH']))
		{
			foreach ($this->arResult['PATH'] as $path)
			{
				if ($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'] != '')
				{
					$APPLICATION->AddChainItem($path['IPROPERTY_VALUES']['SECTION_PAGE_TITLE'], $path['~SECTION_PAGE_URL']);
				}
				else
				{
					$APPLICATION->AddChainItem($path['NAME'], $path['~SECTION_PAGE_URL']);
				}
			}
		}

		if ($this->arParams['SET_LAST_MODIFIED'] && $this->arResult['ITEMS_TIMESTAMP_X'])
		{
			Main\Context::getCurrent()->getResponse()->setLastModified($this->arResult['ITEMS_TIMESTAMP_X']);
		}
	}

	protected function getElementList($iblockId, $products)
	{
		$elementIterator = parent::getElementList($iblockId, $products);

		if (
			!empty($elementIterator)
			&& $this->arParams['BY_LINK'] !== 'Y'
			&& !$this->arParams['SHOW_ALL_WO_SECTION']
			&& !$this->arParams['USE_MAIN_ELEMENT_SECTION']
		)
		{
			$elementIterator->SetSectionContext($this->arResult);
		}

		return $elementIterator;
	}

    /**
     * Fill various common fields for element.
     *
     * @param array &$element			Element data.
     * @return void
     */
    protected function modifyElementCommonData(array &$element)
    {
        $element['ID'] = (int)$element['ID'];
        $element['IBLOCK_ID'] = (int)$element['IBLOCK_ID'];

        if ($this->arParams['HIDE_DETAIL_URL'])
        {
            $element['DETAIL_PAGE_URL'] = $element['~DETAIL_PAGE_URL'] = '';
        }

        if ($this->isEnableCompatible())
        {
            $element['ACTIVE_FROM'] = (isset($element['DATE_ACTIVE_FROM']) ? $element['DATE_ACTIVE_FROM'] : null);
            $element['ACTIVE_TO'] = (isset($element['DATE_ACTIVE_TO']) ? $element['DATE_ACTIVE_TO'] : null);
        }

        $ipropValues = new Iblock\InheritedProperty\ElementValues($element['IBLOCK_ID'], $element['ID']);
        $element['IPROPERTY_VALUES'] = $ipropValues->getValues();

        Iblock\Component\Tools::getFieldImageData(
            $element,
            array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
            Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
            'IPROPERTY_VALUES'
        );

        if (isset($element['~TYPE']))
        {
            $productFields = $this->getProductFields($element['IBLOCK_ID']);
            $translateFields = $this->getCompatibleProductFields();

            $element['PRODUCT'] = array(
                'TYPE' => (int)$element['~TYPE'],
                'AVAILABLE' => $element['~AVAILABLE'],
                'BUNDLE' => $element['~BUNDLE'],
                'QUANTITY' => $element['~QUANTITY'],
                'QUANTITY_TRACE' => $element['~QUANTITY_TRACE'],
                'CAN_BUY_ZERO' => $element['~CAN_BUY_ZERO'],
                'MEASURE' => (int)$element['~MEASURE'],
                'SUBSCRIBE' => $element['~SUBSCRIBE'],
                'VAT_ID' => (int)$element['~VAT_ID'],
                'VAT_RATE' => 0,
                'VAT_INCLUDED' => $element['~VAT_INCLUDED'],
                'WEIGHT' => (float)$element['~WEIGHT'],
                'WIDTH' => (float)$element['~WIDTH'],
                'LENGTH' => (float)$element['~LENGTH'],
                'HEIGHT' => (float)$element['~HEIGHT'],
                'PAYMENT_TYPE' => $element['~PAYMENT_TYPE'],
                'RECUR_SCHEME_TYPE' => $element['~RECUR_SCHEME_TYPE'],
                'RECUR_SCHEME_LENGTH' => (int)$element['~RECUR_SCHEME_LENGTH'],
                'TRIAL_PRICE_ID' => (int)$element['~TRIAL_PRICE_ID']
            );

            $vatId = 0;
            $vatRate = 0;
            if ($element['PRODUCT']['VAT_ID'] > 0)
                $vatId = $element['PRODUCT']['VAT_ID'];
            elseif ($this->storage['IBLOCKS_VAT'][$element['IBLOCK_ID']] > 0)
                $vatId = $this->storage['IBLOCKS_VAT'][$element['IBLOCK_ID']];
            if ($vatId > 0 && isset($this->storage['VATS'][$vatId]))
                $vatRate = $this->storage['VATS'][$vatId];
            $element['PRODUCT']['VAT_RATE'] = $vatRate;
            unset($vatRate, $vatId);

            if ($this->isEnableCompatible())
            {
                foreach ($translateFields as $currentKey => $oldKey)
                    $element[$oldKey] = $element[$currentKey];
                unset($currentKey, $oldKey);
                $element['~CATALOG_VAT'] = $element['PRODUCT']['VAT_RATE'];
                $element['CATALOG_VAT'] = $element['PRODUCT']['VAT_RATE'];
            }
            else
            {
                // temporary (compatibility custom templates)
                $element['~CATALOG_TYPE'] = $element['PRODUCT']['TYPE'];
                $element['CATALOG_TYPE'] = $element['PRODUCT']['TYPE'];
                $element['~CATALOG_QUANTITY'] = $element['PRODUCT']['QUANTITY'];
                $element['CATALOG_QUANTITY'] = $element['PRODUCT']['QUANTITY'];
                $element['~CATALOG_QUANTITY_TRACE'] = $element['PRODUCT']['QUANTITY_TRACE'];
                $element['CATALOG_QUANTITY_TRACE'] = $element['PRODUCT']['QUANTITY_TRACE'];
                $element['~CATALOG_CAN_BUY_ZERO'] = $element['PRODUCT']['CAN_BUY_ZERO'];
                $element['CATALOG_CAN_BUY_ZERO'] = $element['PRODUCT']['CAN_BUY_ZERO'];
                $element['~CATALOG_SUBSCRIBE'] = $element['PRODUCT']['SUBSCRIBE'];
                $element['CATALOG_SUBSCRIBE'] = $element['PRODUCT']['SUBSCRIBE'];
            }

            foreach ($productFields as $field)
                unset($element[$field], $element['~'.$field]);
            unset($field);
        }
        else
        {
            $element['PRODUCT'] = array(
                'TYPE' => null,
                'AVAILABLE' => null
            );
        }

        $element['PROPERTIES'] = array();
        $element['DISPLAY_PROPERTIES'] = array();
        $element['PRODUCT_PROPERTIES'] = array();
        $element['PRODUCT_PROPERTIES_FILL'] = array();
        $element['OFFERS'] = array();
        $element['OFFER_ID_SELECTED'] = 0;

        if (!empty($this->storage['CATALOGS'][$element['IBLOCK_ID']]))
            $element['CHECK_QUANTITY'] = $this->isNeedCheckQuantity($element['PRODUCT']);

        if ($this->getAction() === 'bigDataLoad')
        {
            $element['RCM_ID'] = $this->recommendationIdToProduct[$element['ID']];
        }
    }

    /**
     * Process element prices.
     *
     * @param array &$element		Item data.
     * @return void
     */
    protected function modifyElementPrices(&$element)
    {
        $enableCompatible = $this->isEnableCompatible();
        $id = $element['ID'];
        $iblockId = $element['IBLOCK_ID'];
        $catalog = !empty($this->storage['CATALOGS'][$element['IBLOCK_ID']])
            ? $this->storage['CATALOGS'][$element['IBLOCK_ID']]
            : array();

        $element['ITEM_PRICE_MODE'] = null;
        $element['ITEM_PRICES'] = array();
        $element['ITEM_QUANTITY_RANGES'] = array();
        $element['ITEM_MEASURE_RATIOS'] = array();
        $element['ITEM_MEASURE'] = array();
        $element['ITEM_MEASURE_RATIO_SELECTED'] = null;
        $element['ITEM_QUANTITY_RANGE_SELECTED'] = null;
        $element['ITEM_PRICE_SELECTED'] = null;

        if (!empty($catalog))
        {
            if (!isset($this->productWithOffers[$iblockId]))
                $this->productWithOffers[$iblockId] = array();
            if ($element['PRODUCT']['TYPE'] == Catalog\ProductTable::TYPE_SKU)
            {
                $this->productWithOffers[$iblockId][$id] = $id;
                if ($this->storage['SHOW_CATALOG_WITH_OFFERS'] && $enableCompatible)
                {
                    $this->productWithPrices[$id] = $id;
                    $this->calculatePrices[$id] = $id;
                }
            }

            if (in_array(
                $element['PRODUCT']['TYPE'],
                array(Catalog\ProductTable::TYPE_PRODUCT, Catalog\ProductTable::TYPE_SET, Catalog\ProductTable::TYPE_OFFER)
            )) {
                $this->productWithPrices[$id] = $id;
                $this->calculatePrices[$id] = $id;
            }

            if (isset($this->productWithPrices[$id]))
            {
                if ($element['PRODUCT']['MEASURE'] > 0)
                {
                    $element['ITEM_MEASURE'] = array(
                        'ID' => $element['PRODUCT']['MEASURE'],
                        'TITLE' => '',
                        '~TITLE' => ''
                    );
                }
                else
                {
                    $element['ITEM_MEASURE'] = array(
                        'ID' => null,
                        'TITLE' => $this->storage['DEFAULT_MEASURE']['SYMBOL_RUS'],
                        '~TITLE' => $this->storage['DEFAULT_MEASURE']['~SYMBOL_RUS']
                    );
                }
                if ($enableCompatible)
                {
                    $element['CATALOG_MEASURE'] = $element['ITEM_MEASURE']['ID'];
                    $element['CATALOG_MEASURE_NAME'] = $element['ITEM_MEASURE']['TITLE'];
                    $element['~CATALOG_MEASURE_NAME'] = $element['ITEM_MEASURE']['~TITLE'];
                }
            }
        }
        else
        {
            $element['PRICES'] = \CIBlockPriceTools::GetItemPrices(
                $element['IBLOCK_ID'],
                $this->storage['PRICES'],
                $element,
                $this->arParams['PRICE_VAT_INCLUDE'],
                $this->storage['CONVERT_CURRENCY']
            );
            if (!empty($element['PRICES']))
            {
                $element['MIN_PRICE'] = \CIBlockPriceTools::getMinPriceFromList($element['PRICES']);
            }

            $element['CAN_BUY'] = !empty($element['PRICES']);
        }
    }

	protected function processElement(array &$element)
	{
		if ($this->arResult['ID'])
		{
			$element['IBLOCK_SECTION_ID'] = $this->arResult['ID'];
		}

        $this->modifyElementCommonData($element);
        $this->modifyElementPrices($element);
        $this->setElementPanelButtons($element);
		$this->checkLastModified($element);
	}

	protected function checkLastModified($element)
	{
		if ($this->arParams['SET_LAST_MODIFIED'])
		{
			$time = DateTime::createFromUserTime($element['TIMESTAMP_X']);
			if (
				!isset($this->arResult['ITEMS_TIMESTAMP_X'])
				|| $time->getTimestamp() > $this->arResult['ITEMS_TIMESTAMP_X']->getTimestamp()
			)
			{
				$this->arResult['ITEMS_TIMESTAMP_X'] = $time;
			}
		}
	}

	protected function initElementList()
	{
		parent::initElementList();

		// compatibility for old components
		if ($this->isEnableCompatible() && empty($this->arResult['NAV_RESULT']))
		{
			$this->initNavString(\CIBlockElement::GetList(
				array(),
				array_merge($this->globalFilter, $this->filterFields + array('IBLOCK_ID' => $this->arParams['IBLOCK_ID'])),
				false,
				array('nTopCount' => 1),
				array('ID')
			));
			$this->arResult['NAV_RESULT']->NavNum = Main\Security\Random::getString(6);
		}

		$this->storage['sections'] = array();

		if (!empty($this->elements) && is_array($this->elements))
		{
			foreach ($this->elements as &$element)
			{
				$this->modifyItemPath($element);
			}
		}
	}

	protected function modifyItemPath(&$element)
	{
		$sections =& $this->storage['sections'];

		if ($this->arParams['BY_LINK'] === 'Y')
		{
			if (!isset($sections[$element['IBLOCK_SECTION_ID']]))
			{
				$sections[$element['IBLOCK_SECTION_ID']] = array();
				$pathIterator = CIBlockSection::GetNavChain(
					$element['IBLOCK_ID'],
					$element['IBLOCK_SECTION_ID'],
					array(
						'ID', 'CODE', 'XML_ID', 'EXTERNAL_ID', 'IBLOCK_ID',
						'IBLOCK_SECTION_ID', 'SORT', 'NAME', 'ACTIVE',
						'DEPTH_LEVEL', 'SECTION_PAGE_URL'
					)
				);
				$pathIterator->SetUrlTemplates('', $this->arParams['SECTION_URL']);
				while ($path = $pathIterator->GetNext())
				{
					$sections[$element['IBLOCK_SECTION_ID']][] = $path;
				}
			}

			$element['SECTION']['PATH'] = $sections[$element['IBLOCK_SECTION_ID']];
		}
		else
		{
			$element['SECTION']['PATH'] = array();
		}
	}
}