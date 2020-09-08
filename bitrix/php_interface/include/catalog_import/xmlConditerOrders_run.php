<?
//<title>Импорт рекомендованных заказов</title>

/** @global string $URL_FILE_1C */

/** @global int $IBLOCK_ID */

use Bitrix\Main\Diag\Debug, \Bitrix\Main\Loader;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/import_setup_templ.php');

$max_execution_time = 400;

if (defined("BX_CAT_CRON") && true == BX_CAT_CRON) {
    $max_execution_time = 0;
}

Loader::includeModule('iblock');
Loader::includeModule('sale');

if (!function_exists("XMLCheckTimeout")) {
    function XMLCheckTimeout($max_execution_time) {
        return ($max_execution_time <= 0) || (getmicrotime() - START_EXEC_TIME <= $max_execution_time);
    }
}

if (!function_exists("importLog")) {
	function importLog($text) {
		$logFile = '/import.log';
		Debug::writeToFile($text, '', $logFile);
	}
}

function formatPeriod($endtime, $starttime) {
    $duration = $endtime - $starttime;
    $hours = (int)($duration / 60 / 60);
    $minutes = (int)($duration / 60) - $hours * 60;
    $seconds = (int)$duration - $hours * 60 * 60 - $minutes * 60;
    return ($hours == 0 ? "00" : $hours) . ":" . ($minutes == 0 ? "00" : ($minutes < 10 ? "0" . $minutes : $minutes)) . ":" . ($seconds == 0 ? "00" : ($seconds < 10 ? "0" . $seconds : $seconds));
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
    importLog('Befor read $fileName. $fileName = ' . $fileName);
    $fp = fopen($fileName, "rb");
    $total = filesize($fileName);

    if (($total > 0) && is_resource($fp)) {
        if ($obXMLFile->ReadXMLToDatabase($fp, $NS, $max_execution_time)) {
            $obXMLFile->IndexTemporaryTables();
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
    $rsProfile = $obXMLFile->GetList([], ['>ID' => $lastId, 'NAME' => 'Организация']);

    if ($rsProfile->SelectedRowsCount() > 0) {
        while ($arProfile = $rsProfile->Fetch()) {
            $arUserParams = unserialize($arProfile['ATTRIBUTES']);
            $userFilter = [
                "EMAIL" => $arUserParams["Email"]
            ];
            $arUser = CUser::GetList(($by = "id"), ($order = "asc"), $userFilter)->Fetch();
            $userId = $arUser['ID'];

            $arUserProfile = CSaleOrderUserProps::GetList([], [
                "XML_ID"  => $arUserParams['ВнешнийКод'],
                "USER_ID" => $userId
            ])->Fetch();
            $arProfileFields = [
                "NAME"           => $arUserParams['Название'],
                "XML_ID"         => $arUserParams['ВнешнийКод'],
                "USER_ID"        => $userId,
                "PERSON_TYPE_ID" => 2
            ];
            if ($arUserProfile['ID']) {
                $userProfileId = $arUserProfile['ID'];
                CSaleOrderUserProps::Update($arUserProfile['ID'], $arProfileFields);
            } else {
                $userProfileId = CSaleOrderUserProps::Add($arProfileFields);
            }
            $PROPS = [
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 14,
                    "NAME"           => "Название компании",
                    "VALUE"          => $arUserParams['Название']
                ],
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 20,
                    "NAME"           => "Телефон",
                    "VALUE"          => $arUserParams['Телефон']
                ],
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 27,
                    "NAME"           => "Прайслист",
                    "VALUE"          => $arUserParams['ПрайсЛист']
                ],
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 31,
                    "NAME"           => "ВнешнийКод",
                    "VALUE"          => $arUserParams['ВнешнийКод']
                ],
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 33,
                    "NAME"           => "ДЗ",
                    "VALUE"          => $arUserParams['Долг']
                ],
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 34,
                    "NAME"           => "Просроченная ДЗ",
                    "VALUE"          => $arUserParams['Просрочка']
                ],
                [
                    "USER_PROPS_ID"  => $userProfileId,
                    "ORDER_PROPS_ID" => 35,
                    "NAME"           => "Стоп-лист",
                    "VALUE"          => $arUserParams['Стоп'] ? "Y" : "N"
                ],
            ];
            //добавляем значения свойств к созданному ранее профилю
            foreach ($PROPS as $prop) {
                CSaleOrderUserPropsValue::Add($prop);
            }

            $lastId = $arProfile['ID'];
            if (!$timeout = XMLCheckTimeout($max_execution_time)) {
                break;
            }
        }
    } else {
        $NS['STEP']++;
        unset($lastId);
        importLog('Пользователи импортированы.');
        $importTimeSec = time() - $startImport;
        $importTimeMin = formatPeriod(time(), $startImport);
        importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
    }
    $bAllDataLoaded = false;
    $bAllLinesLoaded = false;
    $SETUP_VARS_LIST = 'NS,lastId,productCount,startImport,outFileAction,startImportDate';
    $CUR_FILE_POS++;
}

if ($NS['STEP'] == 4 || $max_execution_time == 0) {
    $bAllDataLoaded = true;
    $importTimeSec = time() - $startImport;
    $importTimeMin = number_format($importTimeSec / 60, 2, ',', ' ');
    importLog('Импорт завершен.');
    $importTimeSec = time() - $startImport;
    $importTimeMin = formatPeriod(time(), $startImport);
    importLog('Время импорта: ' . $importTimeMin . ' (' . $importTimeSec . 's)');
}
