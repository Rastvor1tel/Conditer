<?
//<title>Импорт товаров</title>/** @global string $URL_FILE_1C */

use Bitrix\Main\Diag\Debug, Bitrix\Catalog\Model\Price, Bitrix\Catalog\Model\Product, Bitrix\Main\Entity;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/import_setup_templ.php');

$max_execution_time = 60;

if (defined("BX_CAT_CRON") && true == BX_CAT_CRON) {
    $max_execution_time = 0;
}

CModule::IncludeModule('iblock');

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

function getSectionId($iblockID, $sectionCode) {
    $sectRes = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockID, '=XML_ID' => $sectionCode])->Fetch();
    return $sectRes['ID'];
}

function addSection($iblockID, $xmlID, $name, $parentID = 0) {
    $bs = new CIBlockSection;
    $arFields = [
        "ACTIVE"            => 'Y',
        "IBLOCK_SECTION_ID" => $parentID,
        "IBLOCK_ID"         => $iblockID,
        "XML_ID"            => $xmlID,
        "NAME"              => $name
    ];
    if ($sectRes = CIBlockSection::GetList([], ['IBLOCK_ID' => $iblockID, '=XML_ID' => $xmlID])->Fetch()) {
        $bs->Update($sectRes['ID'], $arFields);
    } else {
        $bs->Add($arFields);
    }
}

function search_file($folderName) {
    $folderName = (strpos($folderName, '/') === 0) ? $folderName : '/' . $folderName;
    $file = $_SERVER['DOCUMENT_ROOT'] . '/upload/1c_catalog' . $folderName;
    $fileArray = [];
    if (is_file($file) && file_exists($file)) {
        $fileArray = CFile::MakeFileArray($file);
    }

    return $fileArray;
}

function addElement($iblockID, $itemProps) {
    $bs = new CIBlockElement;
    $img = $itemProps['Картинка'] ? search_file($itemProps['Картинка']) : false;
    $arFields = [
        'ACTIVE'            => 'Y',
        "MODIFIED_BY"       => 5,
        "IBLOCK_SECTION_ID" => getSectionId($iblockID, $itemProps['КодРаздела']),
        "IBLOCK_ID"         => $iblockID,
        "XML_ID"            => $itemProps['ВнешнийКод'],
        'PREVIEW_TEXT_TYPE' => 'html',
        'PREVIEW_TEXT'      => str_replace('\n', '<br>', $itemProps['Описание']),
        "PROPERTY_VALUES"   => [
            'CML2_ARTICLE'  => $itemProps['Артикул'],
            'CML2_RATIO'    => $itemProps['Упаковка'],
            'CML2_HIT'      => $itemProps['Хит'] ? getProperty($iblockID, $itemProps['Хит'], 'CML2_HIT', 232) : false,
            'CML2_NEW'      => $itemProps['Новинка'] ? getProperty($iblockID, $itemProps['Новинка'], 'CML2_NEW', 233) : false,
            'CML2_ACTION'   => $itemProps['Акция'] ? getProperty($iblockID, $itemProps['Акция'], 'CML2_ACTION', 234) : false,
            'CML2_DISCOUNT' => $itemProps['Уценка'] ? getProperty($iblockID, $itemProps['Уценка'], 'CML2_DISCOUNT', 235) : false,
            'EXPIRATION'    => $itemProps['СрокГодности'],
            'VENDOR'        => $itemProps['Производитель'],
            'CONDITIONS'    => $itemProps['УсловияХранения'],
            'CONSIST'       => $itemProps['Состав'],
            'WEIGTH'        => $itemProps['Вес'],
			'BREND'        => $itemProps['Бренд'] //ЕПЮ
        ],
        "NAME"              => $itemProps['Название'],
        'PREVIEW_PICTURE'   => $img,
        'DETAIL_PICTURE'    => $img
    ];

    $arCatalogProduct = \Bitrix\Catalog\ProductTable::TYPE_PRODUCT;

    if ($elemRes = CIBlockElement::GetList([], [
        'IBLOCK_ID' => $iblockID,
        '=XML_ID'   => $itemProps['ВнешнийКод']
    ])->Fetch()) {
        $bs->Update($elemRes['ID'], $arFields);

        Product::update($elemRes['ID'], [
            'QUANTITY' => (int)$itemProps['Количество'],
            'MEASURE'  => (int)$itemProps['Количество'] > 1000 ? 3 : 5,
            'TYPE'     => $arCatalogProduct
        ]);
        $dbPrice = Price::getList([
            "filter" => [
                "PRODUCT_ID"       => $elemRes['ID'],
                "CATALOG_GROUP_ID" => 1
            ]
        ])->fetch();

        $arFieldsPrice = [
            "PRODUCT_ID"       => $elemRes['ID'],
            "CATALOG_GROUP_ID" => 1,
            "PRICE"            => 0,
            "CURRENCY"         => 'RUB',
        ];
        if ($dbPrice["ID"]) {
            Price::update($dbPrice["ID"], $arFieldsPrice);
        } else {
            Price::add($arFieldsPrice);
        }
    } else {
        $res = $bs->Add($arFields);

        Product::add([
            'ID'       => $res,
            'QUANTITY' => (int)$itemProps['Количество'],
            'MEASURE'  => (int)$itemProps['Количество'] > 1000 ? 3 : 5,
            'TYPE'     => $arCatalogProduct
        ]);
        $arFieldsPrice = [
            "PRODUCT_ID"       => $res,
            "CATALOG_GROUP_ID" => 1,
            "PRICE"            => 0,
            "CURRENCY"         => 'RUB',
        ];
        Price::add($arFieldsPrice);
    }
}

function getProperty($IBLOCK_ID, $value, $propCode, $propID) {
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

$iblockID = 4;

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

$translitParams = [
    "max_len"               => "100",
    "change_case"           => "L",
    "replace_space"         => "_",
    "replace_other"         => "_",
    "delete_repeat_replace" => "true",
    "use_google"            => "false",
];

if ($arParams["USE_TEMP_DIR"] === "Y" && strlen($_SESSION["BX_CATALOG_IMPORT"]["TEMP_DIR"]) > 0) {
    $DIR_NAME = $_SESSION["BX_CATALOG_IMPORT"]["TEMP_DIR"];
} else {
    $DIR_NAME = $_SERVER["DOCUMENT_ROOT"] . "/" . COption::GetOptionString("main", "upload_dir", "upload") . "/catalog_upload/";
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
    importLog('Before read $fileName. $fileName = ' . $fileName);
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
        'IBLOCK_ID' => $iblockID,
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
    $importTimeSec = time() - $startImport;
    $importTimeMin = formatPeriod(time(), $startImport);
    importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
}

if ($NS['STEP'] == 4 || $max_execution_time == 0) {
    $obXMLFile = new CIBlockXMLFile();
    $rsProduct = $obXMLFile->GetList([], ['>ID' => $lastId, 'NAME' => 'Раздел']);
    if ($rsProduct->SelectedRowsCount() > 0) {
        while ($arProduct = $rsProduct->Fetch()) {
            $itemProps = unserialize($arProduct['ATTRIBUTES']);

            $parentID = 0;

            if (array_key_exists('КодРодителя', $itemProps)) {
                $arFilter = ['IBLOCK_ID' => $iblockID, '=XML_ID' => $itemProps['КодРодителя']];
                $sectRes = CIBlockSection::GetList(['ID' => 'asc'], $arFilter);
                while ($ar_result = $sectRes->GetNext()) {
                    $parentID = $ar_result['ID'];
                }
            }

            addSection($iblockID, $itemProps['ВнешнийКод'], $itemProps['Название'], $parentID);
            $lastId = $arProduct['ID'];
            if (!$timeout = XMLCheckTimeout($max_execution_time)) {
                break;
            }
        }
        importLog('Импортировано ' . $rsProduct->SelectedRowsCount() . ' разделов');
    } else {
        $NS['STEP']++;
        unset($lastId);
        importLog('Разделы импортированы.');
        $importTimeSec = time() - $startImport;
        $importTimeMin = formatPeriod(time(), $startImport);
        importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
    }
    $bAllDataLoaded = false;
    $bAllLinesLoaded = false;
    $SETUP_VARS_LIST = 'NS,lastId,productCount,startImport,outFileAction,startImportDate';
    $CUR_FILE_POS++;
}

if ($NS['STEP'] == 5 || $max_execution_time == 0) {
    $obXMLFile = new CIBlockXMLFile();

    $rsProduct = $obXMLFile->GetList([], ['>ID' => $lastId, 'NAME' => 'Товар']);
    if ($rsProduct->SelectedRowsCount() > 0) {
        while ($arProduct = $rsProduct->Fetch()) {
            $itemProps = unserialize($arProduct['ATTRIBUTES']);

            addElement($iblockID, $itemProps);

            $lastId = $arProduct['ID'];
            if (!$timeout = XMLCheckTimeout($max_execution_time)) {
                break;
            }
        }
        importLog('Импортировано ' . $rsProduct->SelectedRowsCount() . ' элементов');
    } else {
        $NS['STEP']++;
        unset($lastId);
        importLog('Элементы импортированы.');
        $importTimeSec = time() - $startImport;
        $importTimeMin = formatPeriod(time(), $startImport);
        importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
    }
    $bAllDataLoaded = false;
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