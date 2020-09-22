<?php

use \Bitrix\Main\Web\Json;

class DialHelper {
	static public function checkActiveOrganization($arOrganizations) {
		if ($_REQUEST['ORGANIZATION_ID']) {
			if ($_REQUEST['ORGANIZATION_ID'] == "empty") {
				unset($_SESSION['PRICE_ID'], $_SESSION['ORGANIZATION_ID']);
			} else {
				foreach ($arOrganizations as $arItem) {
					if ($arItem['ID'] == $_REQUEST['ORGANIZATION_ID']) {
						$_SESSION['PRICE_ID'] = $arItem['PRICE'];
						$_SESSION['ORGANIZATION_ID'] = $arItem['ID'];
						setcookie("ORGANIZATION_ID", $arItem['ID'], time() + 3600, "/");
					}
				}
			}
		} elseif ($_SESSION['ORGANIZATION_ID'] != $_COOKIE['ORGANIZATION_ID']) {
			foreach ($arOrganizations as $arItem) {
				if ($arItem['ID'] == $_COOKIE['ORGANIZATION_ID']) {
					$_SESSION['PRICE_ID'] = $arItem['PRICE'];
					$_SESSION['ORGANIZATION_ID'] = $arItem['ID'];
					setcookie("ORGANIZATION_ID", $arItem['ID'], time() + 3600, "/");
				}
			}
		}
	}
	
	static public function bildOrganizationList() {
		global $USER;
		$result = [];
		$rsUserProfile = (new CSaleOrderUserProps)->GetList([], ["USER_ID" => $USER->GetID()]);
		while ($arUserProfile = $rsUserProfile->Fetch()) {
			$orgItem = [
				"ID" => $arUserProfile['ID']
			];
			$rsUserProfileValue = (new CSaleOrderUserPropsValue)->GetList(["ID" => "ASC"], ["USER_PROPS_ID" => $arUserProfile['ID']]);
			while ($arUserProfileValue = $rsUserProfileValue->Fetch()) {
				if ($arUserProfileValue['PROP_ID'] == 35) {
					$orgItem['STOP'] = $arUserProfileValue['VALUE'];
				}
				if ($arUserProfileValue['PROP_ID'] == 14) {
					$orgItem['NAME'] = str_replace('"', "'", $arUserProfileValue["VALUE"]);
				}
				if ($arUserProfileValue['PROP_ID'] == 27) {
					$orgItem['PRICE'] = $arUserProfileValue['VALUE'];
				}
			}
			if ($orgItem['STOP'] != 'Y') {
				$result[] = $orgItem;
			}
		}
		return $result;
	}
	
	static public function checkAjax() {
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		} else
			return false;
	}
	
	static public function buildAbc() {
		return [
			0  => 'A',
			1  => 'B',
			2  => 'C',
			3  => 'D',
			4  => 'E',
			5  => 'F',
			6  => 'G',
			7  => 'H',
			8  => 'I',
			9  => 'J',
			10 => 'K',
			11 => 'L',
			12 => 'M',
			13 => 'N',
			14 => 'O',
			15 => 'P',
			16 => 'Q',
			17 => 'R',
			18 => 'S',
			19 => 'T',
			20 => 'U',
			21 => 'V',
			22 => 'W',
			23 => 'X',
			24 => 'Y',
			25 => 'Z',
		];
	}
	
	/**
	 * @param array $header Массив заголовков
	 * @param array $rows Массив строк
	 * @param string $listTitle Название листа
	 * @throws
	 */
	public function export2Excel(array $header, array $rows, $listTitle = "Лист") {
		$errMsg = [];
		$arrLib = get_loaded_extensions();
		if (!in_array('xmlwriter', $arrLib)) {
			$errMsg['TYPE'] = 'error';
			$errMsg['MESSAGE'] = GetMessage('BLANK_EXCEL_EXPORT_LIB_ERROR');
			echo Json::encode($errMsg);
		} else {
			$excelData = $this->buildExcelData($header, $rows);
			$path = $this->excelExport($excelData, $listTitle);
			echo $path;
		}
	}
	
	/**
	 * @param array $header Массив заголовков
	 * @param array $rows Массив строк
	 * @return array
	 */
	private function buildExcelData(array $header, array $rows) {
		$excelData = [];
		$abc = self::buildAbc();
		foreach ($abc as $key => $column) {
			$index = 1;
			if ($header[$key]) {
				$excelData[$column][$index] = $header[$key]["value"];
			}
			foreach ($rows as $row) {
				if ($row["data"][$header[$key]["id"]]) {
					$index++;
					$excelData[$column][$index] = strip_tags(str_replace(["&nbsp;", "<br>"], [" ", "\n"], $row["data"][$header[$key]["id"]]));
				}
			}
		}
		return $excelData;
	}
	
	/**
	 * @param array $excelData Массив подготовленный для выгрузки
	 * @param string $listTitle Название листа
	 * @return string
	 */
	private function excelExport(array $excelData, string $listTitle) {
		$xls = new PHPExcel();
		$xls->setActiveSheetIndex(0);
		$sheet = $xls->getActiveSheet();
		$sheet->setTitle($listTitle);
		foreach ($excelData as $column => $items) {
			foreach ($items as $row => $item) {
				if ($row == 1) {
					$sheet->getColumnDimension($column)->setAutoSize(true);
					$sheet->getStyle($column . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$sheet->getStyle($column . $row)->getFont()->setBold(true);
					$bg = [
						"fill" => [
							"type"  => PHPExcel_Style_Fill::FILL_SOLID,
							"color" => [
								"rgb" => "dadada"
							]
						]
					];
					$sheet->getStyle($column . $row)->applyFromArray($bg);
				} else {
					$sheet->getStyle($column . $row)->getAlignment()->setWrapText(true);
				}
				$sheet->getStyle($column . $row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				$sheet->setCellValue($column . $row, $item);
			}
		}
		
		$path = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tmp/' . rand() . '.xlsx';
		$objWriter = new PHPExcel_Writer_Excel2007($xls);
		$objWriter->save($path);
		$http = 'http://';
		if ($_SERVER['HTTPS'] && 'off' !== strtolower($_SERVER['HTTPS'])) {
			$http = 'https://';
		}
		$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
		$path = $http . $_SERVER['SERVER_NAME'] . $path;
		return $path;
	}
}