<?
//<title>Импорт прайсов</title>/** @global string $URL_FILE_1C */

/** @global int $IBLOCK_ID */

use Bitrix\Main\Diag\Debug, Bitrix\Catalog\Model\Price, Bitrix\Catalog\Model\Product;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/import_setup_templ.php');

$max_execution_time = 400;

if (defined("BX_CAT_CRON") && true == BX_CAT_CRON) {
	$max_execution_time = 0;
}

\Bitrix\Main\Loader::includeModule('iblock');

if (!function_exists("XMLCheckTimeout")) {
	function XMLCheckTimeout($max_execution_time) {
		return ($max_execution_time <= 0) || (getmicrotime() - START_EXEC_TIME <= $max_execution_time);
	}
}

$logFile = '/import.log';

if (!function_exists("importLog")) {
	function importLog($text) {
		$logFile = '/import.log';
		Debug::writeToFile(mb_convert_encoding($text, "Windows-1251", "UTF-8"), '', $logFile);
	}
}

function formatPeriod($endtime, $starttime) {
	$duration = $endtime - $starttime;
	$hours = (int)($duration / 60 / 60);
	$minutes = (int)($duration / 60) - $hours * 60;
	$seconds = (int)$duration - $hours * 60 * 60 - $minutes * 60;
	return ($hours == 0 ? "00" : $hours) . ":" . ($minutes == 0 ? "00" : ($minutes < 10 ? "0" . $minutes : $minutes)) . ":" . ($seconds == 0 ? "00" : ($seconds < 10 ? "0" . $seconds : $seconds));
}

class xmlCatalogPriceImport {
	//xml_id раздела с товарами
	public $xml_id = '';
	
	public function prepareSection($IBLOCK_ID, $iblockID) {
		$sectionPriceID = $this->getSectionTemp($IBLOCK_ID, $this->xml_id);
		
		if ($sectionPriceID) {
			$this->updSectionPrice($sectionPriceID, ['NAME' => $this->xml_id]);
		} else {
			$sectionPriceID = $this->addSectionPrice([
				"MODIFIED_BY"       => 5,
				'ACTIVE'            => 'Y',
				'NAME'              => $this->xml_id,
				"XML_ID"            => $this->xml_id,
				"IBLOCK_SECTION_ID" => false,
				"IBLOCK_ID"         => $IBLOCK_ID
			]);
		}
		
		if ($sectionPriceID) {
			$sectRes = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockID]);
			while ($arRes = $sectRes->Fetch()) {
				$section = $this->sectionByIblockFetch([
					'IBLOCK_ID' => $IBLOCK_ID,
					'XML_ID'    => $arRes['XML_ID'] . '_' . $this->xml_id
				]);
				$this->addSectionParent($IBLOCK_ID, $arRes, $section['ID'], $sectionPriceID, $this->xml_id);
			}
			unset($sectRes, $sectionPriceID);
		}
	}
	
	private function getSectionTemp($IBLOCK_ID, $XML_ID) {
		$obImport = new CIBlockCMLImport();
		return $obImport->GetSectionByXML_ID($IBLOCK_ID, $XML_ID);
	}
	
	private function updSectionPrice($sectionPriceID, $arFields) {
		$ob = new CIBlockSection;
		if ($sectionPriceID) {
			$ob->Update($sectionPriceID, $arFields);
			return $sectionPriceID;
		}
		return false;
	}
	
	private function addSectionPrice($arFields) {
		$ob = new CIBlockSection;
		if (is_array($arFields)) {
			return $ob->Add($arFields);
		}
		return false;
	}
	
	private function sectionByIblockFetch($arFields) {
		return CIBlockSection::GetList(false, $arFields)->Fetch();
	}
	
	private function addSectionParent($IBLOCK_ID, $arRes, $sectionID, $parentID, $XML_ID) {
		$arFields = [
			'ACTIVE'      => 'Y',
			"MODIFIED_BY" => 5,
			"XML_ID"      => $arRes['XML_ID'] . '_' . $XML_ID,
			"NAME"        => $arRes['NAME']
		];
		
		if ($sectionID) {
			$this->updSectionPrice($sectionID, $arFields);
		} else {
			$arFields["IBLOCK_ID"] = $IBLOCK_ID;
			if ($arRes["IBLOCK_SECTION_ID"]) {
				$res = CIBlockSection::GetByID($arRes['IBLOCK_SECTION_ID'])->Fetch();
				
				$sectionID = $this->sectionByIblockFetch([
					'IBLOCK_ID' => $IBLOCK_ID,
					'XML_ID'    => $res['XML_ID'] . '_' . $this->xml_id
				]);
				
				$arFields["IBLOCK_SECTION_ID"] = $sectionID['ID'];
			} else {
				$arFields["IBLOCK_SECTION_ID"] = $parentID;
			}
			$this->addSectionPrice($arFields);
		}
		unset($arFields);
	}
	
	public function prepareElement($arProduct, $IBLOCK_ID, $iblockID) {
		$bs = new CIBlockElement;
		$obImport = new CIBlockCMLImport();
		
		$itemProps = unserialize($arProduct['ATTRIBUTES']);
		
		$arrElementTemp = $this->getElementTemp(['IBLOCK_ID' => $iblockID, 'XML_ID' => $itemProps['ВнешнийКод']]);
		$elementTemp = $this->prepareElementTemp($arrElementTemp, $IBLOCK_ID);
		
		$elementIDSKU = $obImport->GetElementByXML_ID($IBLOCK_ID, $elementTemp['FIELDS']['XML_ID']);
		$formatPrice = str_replace(",", '.', $itemProps['Цена']);
		
		if (is_array($elementTemp['FIELDS'])) {
			if ($elementIDSKU) {
				$bs->Update($elementIDSKU, $elementTemp['FIELDS']);
				$this->elementQuantity($elementIDSKU, $elementTemp['PRICE'], true);
			} else {
				$elementIDSKU = $bs->Add($elementTemp['FIELDS']);
				$this->elementQuantity($elementIDSKU, $elementTemp['PRICE']);
			}
			$this->elementPrice($elementIDSKU, $formatPrice);
		}
		$result = [
			"ID"        => $elementIDSKU,
			"PRICELIST" => $elementTemp["PRICELIST_CODE"],
			"FIELDS"    => $elementTemp["FIELDS"],
			"PRICE"     => $elementTemp["PRICE"]
		];
		return $result;
	}
	
	public function deleteSKU($arElement, $iblockID) {
		$rsOffers = CIBlockElement::GetList([], ["IBLOCK_ID" => $iblockID, "CML2_LINK" => $arElement["ID"]], false, false, ["IBLOCK_ID", "ID"]);
		while ($arOffer = $rsOffers->Fetch()) {
			CIBlockElement::Delete($arOffer["ID"]);
		}
	}
	
	public function prepareSKU($arElement, $iblockID, $arOffer) {
		$obImport = new CIBlockCMLImport();
		$elementXML_ID = "{$arOffer["ВнешнийКод"]}_{$arElement["PRICELIST"]}";
		$result = $obImport->GetElementByXML_ID($iblockID, $elementXML_ID);
		$formatPrice = str_replace(",", '.', $arOffer['Цена']);
		Product::update($arElement["ID"], [
			'TYPE'     => \Bitrix\Catalog\ProductTable::TYPE_SKU
		]);
		if (!$result) {
			$fields = [
				"IBLOCK_ID"       => $iblockID,
				"NAME"            => $arElement["FIELDS"]["NAME"],
				"XML_ID"          => $elementXML_ID,
				"ACTIVE"          => "Y",
				"PROPERTY_VALUES" => [
					"CML2_LINK" => $arElement["ID"]
				]
			];
			$element = new CIBlockElement;
			$result = $element->Add($fields);
			Product::add([
				"ID"       => $result,
				"QUANTITY" => $arElement["PRICE"]["QUANTITY"],
				"TYPE"     => \Bitrix\Catalog\ProductTable::TYPE_OFFER
			]);
		}
		$this->elementPrice($result, $formatPrice);
		/*Debug::writeToFile($arElement, "", "/local/php_interface/import.log");
		Debug::writeToFile($arOffer, "", "/local/php_interface/import.log");*/
		return $result;
	}
	
	private function getElementTemp($arFields) {
		$result = [];
		$res = CIBlockElement::GetList(false, $arFields)->GetNextElement();
		if ($res) {
			$result = [
				'FIELDS'     => $res->GetFields(),
				'PROPERTIES' => $res->GetProperties(),
			];
		}
		return $result;
	}
	
	private function prepareElementTemp($objElementTemp, $IBLOCK_ID) {
		$arFields = $objElementTemp['FIELDS'];
		$arFields['PROPS'] = $objElementTemp['PROPERTIES'];
		$sectionIDTemp = CIBlockSection::GetByID($arFields['IBLOCK_SECTION_ID'])->Fetch();
		$sectionID = $this->sectionByIblockFetch([
			'IBLOCK_ID' => $IBLOCK_ID,
			'XML_ID'    => $sectionIDTemp['XML_ID'] . '_' . $this->xml_id
		]);
		$dbQuantity = Product::getList([
			"filter" => ["ID" => $arFields['ID']],
			"limit"  => 1
		])->fetch();
		
		if (!$sectionID || !$dbQuantity) {
			return false;
		}
		
		$price = [
			'QUANTITY' => $dbQuantity['QUANTITY'],
			'MEASURE'  => $dbQuantity['MEASURE'],
			'TYPE'     => $dbQuantity['TYPE']
		];
		$hit = $arFields['PROPS']['CML2_HIT']['VALUE'];
		$new = $arFields['PROPS']['CML2_NEW']['VALUE'];
		$action = $arFields['PROPS']['CML2_ACTION']['VALUE'];
		$discount = $arFields['PROPS']['CML2_DISCOUNT']['VALUE'];
		
		return [
			'FIELDS'         => [
				'MODIFIED_BY'       => $arFields['MODIFIED_BY'],
				'ACTIVE'            => $arFields['ACTIVE'],
				'NAME'              => $arFields['NAME'],
				'IBLOCK_ID'         => $IBLOCK_ID,
				'XML_ID'            => $arFields['XML_ID'] . '_' . $this->xml_id,
				'IBLOCK_SECTION_ID' => $sectionID['ID'],
				'PREVIEW_PICTURE'   => CFile::MakeFileArray($arFields['PREVIEW_PICTURE']),
				'DETAIL_PICTURE'    => CFile::MakeFileArray($arFields['DETAIL_PICTURE']),
				'PREVIEW_TEXT'      => $arFields['PREVIEW_TEXT'],
				'PREVIEW_TEXT_TYPE' => $arFields['PREVIEW_TEXT_TYPE'],
				'PROPERTY_VALUES'   => [
					'CML2_ARTICLE'  => $arFields['PROPS']['CML2_ARTICLE']['VALUE'],
					'CML2_RATIO'    => $arFields['PROPS']['CML2_RATIO']['VALUE'],
					'CML2_HIT'      => $hit ? $this->getProperty($IBLOCK_ID, $hit, 'CML2_HIT', 225) : false,
					'CML2_NEW'      => $new ? $this->getProperty($IBLOCK_ID, $new, 'CML2_NEW', 226) : false,
					'CML2_ACTION'   => $action ? $this->getProperty($IBLOCK_ID, $action, 'CML2_ACTION', 227) : false,
					'CML2_DISCOUNT' => $discount ? $this->getProperty($IBLOCK_ID, $discount, 'CML2_DISCOUNT', 228) : false,
					'EXPIRATION'    => $arFields['PROPS']['EXPIRATION']['~VALUE'],
					'VENDOR'        => $arFields['PROPS']['VENDOR']['~VALUE'],
					'CONDITIONS'    => $arFields['PROPS']['CONDITIONS']['~VALUE'],
					'CONSIST'       => $arFields['PROPS']['CONSIST']['~VALUE'],
					'WEIGTH'        => $arFields['PROPS']['WEIGTH']['~VALUE'],
					'BREND'         => $arFields['PROPS']['BREND']['~VALUE'] //ЕПЮ
				]
			],
			'PRICE'          => $price,
			"PRICELIST_CODE" => $this->xml_id
		];
	}
	
	private function getProperty($IBLOCK_ID, $value, $propCode, $propID) {
		$property_enums = CIBlockPropertyEnum::GetList([
			"DEF"  => "DESC",
			"SORT" => "ASC"
		], [
			"IBLOCK_ID" => $IBLOCK_ID,
			"CODE"      => $propCode,
			"XML_ID"    => $value
		])->Fetch();
		
		$ibpenum = new CIBlockPropertyEnum();
		
		if ($property_enums['ID']) {
			$ibpenum::Update($property_enums['ID'], ['VALUE' => $value, 'XML_ID' => $value]);
			$valueId = $property_enums['ID'];
		} else {
			$valueId = $ibpenum::Add([
				'PROPERTY_ID' => (int)$propID,
				'VALUE'       => $value,
				'XML_ID'      => $value
			]);
		}
		
		return $valueId;
	}
	
	private function elementQuantity($elementID, $arField, $isUPD = false) {
		if ($isUPD) {
			Product::update($elementID, $arField);
		} else {
			Product::add([
				'ID'       => $elementID,
				'QUANTITY' => $arField['QUANTITY'],
				'MEASURE'  => $arField['MEASURE'],
				'TYPE'     => $arField['TYPE']
			]);
		}
	}
	
	private function elementPrice($elementID, $price) {
		$arFieldsPrice = [
			"PRODUCT_ID"       => $elementID,
			"CATALOG_GROUP_ID" => 1,
			"PRICE"            => $price,
			"CURRENCY"         => 'RUB',
		];
		
		$dbPrice = Price::getList([
			"filter" => [
				"PRODUCT_ID"       => $elementID,
				"CATALOG_GROUP_ID" => 1
			]
		])->fetch();
		
		if ($dbPrice["ID"]) {
			Price::update($dbPrice["ID"], $arFieldsPrice);
		} else {
			Price::add($arFieldsPrice);
		}
	}
}

$iblockID = 4;
$iblockOfferID = 7;

$NS = $NS ?? [];

$NS['STEP'] = $NS['STEP'] ?? 0;
$lastId = $lastId ?? 0;
$lastOfferId = $lastOfferId ?? 0;
$productCount = $productCount ?? 0;
$productUpdCount = $productUpdCount ?? 0;
$outFileAction = $outFileAction ?? 'keep';
$startImportDate = ConvertDateTime(ConvertTimeStamp(time(), "FULL"), "YYYY-MM-DD HH:MI:SS");
$startImport = $startImport ?? 0;
$CUR_FILE_POS = $CUR_FILE_POS ?? 0;

$fileName = $fileName ?? $_SERVER['DOCUMENT_ROOT'] . $URL_FILE_1C;

if ($arParams["USE_TEMP_DIR"] === "Y" && strlen($_SESSION["BX_CATALOG_IMPORT"]["TEMP_DIR"]) > 0) {
	$DIR_NAME = $_SESSION["BX_CATALOG_IMPORT"]["TEMP_DIR"];
} else {
	$DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/1c_catalog/";
}

if ($NS['STEP'] < 1 || $max_execution_time == 0) {
	$startImport = time();
	unlink($_SERVER['DOCUMENT_ROOT'] . $logFile);
	$obXMLFile = new CIBlockXMLFile();
	$obXMLFile->DropTemporaryTables();
	importLog('Время старта: ' . $startImportDate);
	importLog('Временные таблицы удалены.');
	$NS['STEP']++;
	$bAllDataLoaded = false;
	$bAllLinesLoaded = false;
	$SETUP_VARS_LIST = 'NS,startImport,fileName';
	$CUR_FILE_POS++;
}

if ($NS['STEP'] == 1 || $max_execution_time == 0) {
	$obXMLFile = new CIBlockXMLFile();
	if ($obXMLFile->CreateTemporaryTables()) {
		importLog('Временные таблицы созданы.');
		$NS['STEP']++;
	} else {
		importLog('Ошибка создания временных таблиц.');
	}
	$bAllDataLoaded = false;
	$bAllLinesLoaded = false;
	$SETUP_VARS_LIST = 'NS,startImport,fileName,outFileAction,startImportDate';
	$CUR_FILE_POS++;
}

if ($NS['STEP'] == 2 || $max_execution_time == 0) {
	$obXMLFile = new CIBlockXMLFile();
	importLog('Befor read $fileName. $fileName = ' . $fileName);
	$fp = fopen($fileName, "rb");
	$total = filesize($fileName);
	
	if (($total > 0) && is_resource($fp)) {
		if ($obXMLFile->ReadXMLToDatabase($fp, $NS, $max_execution_time)) {
			$NS['STEP']++;
			importLog('Файл импорта прочитан.');
		} else {
			$percent = $total > 0 ? round($obXMLFile->GetFilePosition() / $total * 100, 2) : 0;
			importLog('Обработано ' . $percent . '% файла.');
		}
		fclose($fp);
	} else {
		importLog('Ошибка открытия файла импорта.');
	}
	$bAllDataLoaded = false;
	$bAllLinesLoaded = false;
	$SETUP_VARS_LIST = 'NS,startImport,fileName,outFileAction,startImportDate';
	$CUR_FILE_POS++;
}

if ($NS['STEP'] == 3 || $max_execution_time == 0) {
	$obXMLFile = new CIBlockXMLFile();
	$obXMLFile->IndexTemporaryTables();
	
	/*$rsProducts = CIBlockElement::GetList(['ID' => 'DESC'], [
				'IBLOCK_ID' => $IBLOCK_ID,
			], false, false, ['ID']);
	
		if ($rsProducts->SelectedRowsCount() > 0) {
			$element = new CIBlockElement;
			while ($arProduct = $rsProducts->Fetch()) {
				$element->Update($arProduct["ID"], ["ACTIVE" => "N"]);
			}
			importLog('Деактивировано ' . $rsProducts->SelectedRowsCount() . ' товаров');
	}*/
	
	$NS['STEP']++;
	$bAllDataLoaded = false;
	$bAllLinesLoaded = false;
	$SETUP_VARS_LIST = 'NS,startImport,outFileAction';
	$CUR_FILE_POS++;
	$importTimeSec = time() - $startImport;
	$importTimeMin = formatPeriod(time(), $startImport);
	importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
}

if ($NS['STEP'] == 4 || $max_execution_time == 0) {
	$obXMLFile = new CIBlockXMLFile();
	$rsProductPrice = $obXMLFile->GetList([], ['>ID' => $lastId, 'NAME' => 'ПрайсЛист']);
	$bs = new CIBlockElement;
	$import = new xmlCatalogPriceImport;
	$obImport = new CIBlockCMLImport();
	
	if ($rsProductPrice->SelectedRowsCount() > 0) {
		while ($arProductPrice = $rsProductPrice->Fetch()) {
			$rsProduct = $obXMLFile->GetList([], ['PARENT_ID' => $arProductPrice['ID'], 'NAME' => 'Товар']);
			if ($rsProduct->SelectedRowsCount() > 0) {
				$itemPropsPrice = unserialize($arProductPrice['ATTRIBUTES']);
				$import->xml_id = $itemPropsPrice['ВнешнийКод'];
				$import->prepareSection($IBLOCK_ID, $iblockID);
				while ($arProduct = $rsProduct->Fetch()) {
					$element = $import->prepareElement($arProduct, $IBLOCK_ID, $iblockID);
					$import->deleteSKU($element, $iblockOfferID);
					$rsOffers = $obXMLFile->GetList([], ['PARENT_ID' => $arProduct['ID'], 'NAME' => 'Предложение']);
					if ($rsOffers->SelectedRowsCount() > 0) {
						while ($arOffer = $rsOffers->Fetch()) {
							$arOfferProps = unserialize($arOffer["ATTRIBUTES"]);
							$offerID = $import->prepareSKU($element, $iblockOfferID, $arOfferProps);
						}
					}
				}
			}
			$lastId = $arProductPrice['ID'];
			if (!$timeout = XMLCheckTimeout($max_execution_time))
				break;
		}
		importLog('Импортировано ' . $rsProduct->SelectedRowsCount() . ' свойств');
	} else {
		$NS['STEP']++;
		unset($lastId);
		importLog('Группы импортированы.');
		$importTimeSec = time() - $startImport;
		$importTimeMin = formatPeriod(time(), $startImport);
		importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
	}
	$bAllDataLoaded = false;
	$bAllLinesLoaded = false;
	$SETUP_VARS_LIST = 'NS,lastId,productCount,startImport,outFileAction,startImportDate';
	$CUR_FILE_POS++;
}

if ($NS['STEP'] == 6 || $max_execution_time == 0) {
	$bAllDataLoaded = true;
	$importTimeSec = time() - $startImport;
	$importTimeMin = number_format($importTimeSec / 60, 2, ',', ' ');
	importLog('Импорт завершен.');
	$importTimeSec = time() - $startImport;
	$importTimeMin = formatPeriod(time(), $startImport);
	importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
}