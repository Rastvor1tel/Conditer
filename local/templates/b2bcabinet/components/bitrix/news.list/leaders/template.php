<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$lang = [
	"EMAIL"	=> GetMessage("EMAIL"),
	"PHONE" => GetMessage("PHONE")
];

$printLeaderItem = function($item) use ($lang) {
	echo <<<ITEM
		<div class="contacts-section-item">
			<div class="contacts-section-item__position">
				{$item["POSITION"]}
			</div>
			<div class="contacts-section-item__img">
				<img src="{$item["IMAGE"]}" alt="">
			</div>
			<div class="contacts-section-item__name">
				{$item["NAME"]}
			</div>
			<div class="contacts-section-item__contacts">
				<div class="contacts-section-item__wrapper">
					<div class="contacts-section-item__contacts-name">
						{$lang["EMAIL"]}:
					</div>
					<a href="mailto:{$item["EMAIL"]}" class="contacts-section-item__mail">
						{$item["EMAIL"]}
					</a>
				</div>
				<div class="contacts-section-item__wrapper">
					<div class="contacts-section-item__contacts-name">
						{$lang["PHONE"]}:
					</div>
					<a href="tel:{$item["PHONE"]["FORMATTED"]}" class="contacts-section-item__phone">
						{$item["PHONE"]["VALUE"]}
					</a>
				</div>
			</div>
		</div>
	ITEM;
};

if ($arResult["LEADERS"]) {
	echo "<div class=\"contacts-section\"><div class=\"contacts-section__wrapper\">";
	array_map($printLeaderItem, $arResult["LEADERS"]);
	echo "</div></div>";
}