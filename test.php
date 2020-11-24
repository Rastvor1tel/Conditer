<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"b2b_search",
	[
		"RESTART"                                                 => "Y",
		"NO_WORD_LOGIC"                                           => "Y",
		"USE_LANGUAGE_GUESS"                                      => "N",
		"CHECK_DATES"                                             => "Y",
		"arrFILTER"                                               => [
			0 => "iblock_sotbit_b2bcabinet_type_catalog",
		],
		"={\"arrFILTER_iblock_\".\$arParams[1][\"IBLOCK_TYPE\"]}" => $searchIblocks,
		"USE_TITLE_RANK"                                          => "N",
		"DEFAULT_SORT"                                            => "rank",
		"FILTER_NAME"                                             => "",
		"SHOW_WHERE"                                              => "N",
		"arrWHERE"                                                => "",
		"SHOW_WHEN"                                               => "N",
		"PAGE_RESULT_COUNT"                                       => "9999",
		"DISPLAY_TOP_PAGER"                                       => "N",
		"DISPLAY_BOTTOM_PAGER"                                    => "N",
		"PAGER_TITLE"                                             => "",
		"PAGER_SHOW_ALWAYS"                                       => "N",
		"PAGER_TEMPLATE"                                          => "N",
		"COMPONENT_TEMPLATE"                                      => "b2b_search",
		"arrFILTER_iblock_sotbit_b2bcabinet_type_catalog"         => [
			0 => "3",
		],
		"AJAX_MODE"                                               => "N",
		"AJAX_OPTION_JUMP"                                        => "N",
		"AJAX_OPTION_STYLE"                                       => "Y",
		"AJAX_OPTION_HISTORY"                                     => "N",
		"AJAX_OPTION_ADDITIONAL"                                  => "",
		"CACHE_TYPE"                                              => "A",
		"CACHE_TIME"                                              => "3600",
		"USE_SUGGEST"                                             => "N",
		"SHOW_RATING"                                             => "",
		"RATING_TYPE"                                             => "",
		"PATH_TO_USER_PROFILE"                                    => ""
	],
	$component
);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>