<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

echo "<pre>";
$arSectionsFilter = [
	"IBLOCK_ID"     => 3,
	"ACTIVE"        => "Y",
	">DEPTH_LEVEL"  => 1,
];

$arSectionsSelect = ["ID", "NAME", "IBLOCK_SECTION_ID", "DEPTH_LEVEL"];

$res = CIBlockSection::GetList(['LEFT_MARGIN' => 'ASC'], $arSectionsFilter, false, $arSectionsSelect);
while ($arRes = $res->Fetch()) {
	echo "{$arRes["NAME"]} - {$arRes["DEPTH_LEVEL"]}\n";
}
echo "</pre>";

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>