<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

echo "<pre>";

$resSection = CIBlockSection::GetList(false, ['IBLOCK_ID' => 3, 'XML_ID' => "00-00000003"])->Fetch();
if ($resSection) {
	$arSectionsFilter = [
		"IBLOCK_ID"     => 3,
		"ACTIVE"        => "Y",
		">DEPTH_LEVEL"  => "1",
		">LEFT_MARGIN"  => $resSection['LEFT_MARGIN'],
		"<RIGHT_MARGIN" => $resSection['RIGHT_MARGIN']
	];
} else {
	$arSectionsFilter = false;
}

/*$arSectionsFilter = [
	"IBLOCK_ID"     => 3,
	"ACTIVE"        => "Y",
	">DEPTH_LEVEL"  => 1,
];*/

$arSectionsSelect = ["ID", "NAME", "IBLOCK_SECTION_ID", "DEPTH_LEVEL"];

$res = CIBlockSection::GetList(['LEFT_MARGIN' => 'ASC'], $arSectionsFilter, false, $arSectionsSelect);
while ($arRes = $res->Fetch()) {
	$result = "";
	for ($i=0;$i<=$arRes["DEPTH_LEVEL"];$i++) {
		$result .= "- ";
	}
	$result .= "{$arRes["NAME"]} - {$arRes["DEPTH_LEVEL"]}\n";
	echo $result;
}
echo "</pre>";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>