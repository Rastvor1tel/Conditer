<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<? if (!empty($arResult['ERRORS']['FATAL'])): ?>
	
	<? foreach ($arResult['ERRORS']['FATAL'] as $error): ?>
		<?= ShowError($error) ?>
	<? endforeach ?>

<? else: ?>
	
	<? if (!empty($arResult['ERRORS']['NONFATAL'])): ?>
		
		<? foreach ($arResult['ERRORS']['NONFATAL'] as $error): ?>
			<?= ShowError($error) ?>
		<? endforeach ?>
	
	<? endif ?>

	<div class="personal_list_order">
		<div class="order-control">
			<div class="main-ui-filter-search-wrapper">
				<?
				$APPLICATION->IncludeComponent(
					"bitrix:main.ui.filter",
					"b2bcabinet",
					[
						"FILTER_ID"          => "ORDER_LIST",
						"GRID_ID"            => "ORDER_LIST",
						"FILTER"             => [
							[
								"id"   => "ID",
								"name" => GetMessage('SPOL_ORDER_FIELD_NAME_ID')
							], [
								"id"   => "DATE_INSERT",
								"name" => GetMessage('SPOL_ORDER_FIELD_NAME_DATE'),
								"type" => "date",
							], [
								"id"   => "STATUS",
								"name" => GetMessage('SPOL_ORDER_FIELD_NAME_STATUS')
							], [
								"id"   => "PRICE",
								"name" => GetMessage('SPOL_ORDER_FIELD_NAME_FORMATED_PRICE'),
								"type" => "number"
							], [
								"id"     => "BUYER_ID",
								"name"   => GetMessage("SPOL_ORDER_FIELD_NAME_BUYER"),
								"type"   => "list",
								"params" => [
									"multiple" => "Y"
								],
								"items"  => $arResult["BUYERS"]
							], [
								"id"     => "PRODUCT_ID",
								"name"   => GetMessage("SPOL_ORDER_FIELD_NAME_PRODUCT"),
								"type"   => "list",
								"items"  => $arResult["PRODUCTS"],
								"params" => [
									"multiple" => "Y"
								],
							],
						],
						"ENABLE_LIVE_SEARCH" => true,
						"ENABLE_LABEL"       => true,
						"COMPONENT_TEMPLATE" => "b2bcabinet"
					],
					false
				);
				?>
			</div>
			<div class="card-body">
				<a href="/orders/products/">Статистика по товарам.</a>
				<? /*<div class="card-excel-button">
					<button type="button"
						class="btn btn-light btn-ladda btn-ladda-spinner"
						data-spinner-color="#333"
						data-style="slide-right"
						id="blank-export-in-excel"
					>
	                    <span class="ladda-label export_excel_preloader">
	                        <i class="icon-upload mr-2"></i>
	                        Выгрузить в Excel
	                    </span>
					</button>
				</div>*/ ?>
			</div>
		</div>
		<?
		$APPLICATION->IncludeComponent(
			'bitrix:main.ui.grid',
			'',
			[
				'GRID_ID'            => 'ORDER_LIST',
				'HEADERS'            => [
					["id" => "ID", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_ID'), "sort" => "ID", "default" => true, "editable" => false],
					["id" => "DATE_INSERT", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_DATE'), "sort" => "DATE_INSERT", "default" => true, "editable" => false],
					["id" => "STATUS", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_STATUS'), "sort" => "STATUS", "default" => true, "editable" => true],
					["id" => "FORMATED_PRICE", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_FORMATED_PRICE'), "default" => true, "sort" => "PRICE"],
					["id" => "PAYED", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_PAYED'), "sort" => "PAYED"],
					["id" => "PAYMENT_METHOD", "name" => GetMessage('B2B_SPOL_ORDER_FIELD_NAME_PAYMENT_METHOD'), "sort" => "PAY_SYSTEM_ID"],
					["id" => "SHIPMENT_METHOD", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_SHIPMENT_METHOD'), "sort" => "DELIVERY_ID"],
					["id" => "ITEMS", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_ITEMS')],
					["id" => "BUYER", "name" => GetMessage('SPOL_ORDER_FIELD_NAME_BUYER')],
				],
				'ROWS'               => $arResult['ROWS'],
				'FILTER_STATUS_NAME' => $arResult['FILTER_STATUS_NAME'],
				'AJAX_MODE'          => 'Y',
				
				"AJAX_OPTION_JUMP"    => "N",
				"AJAX_OPTION_STYLE"   => "N",
				"AJAX_OPTION_HISTORY" => "N",
				
				"ALLOW_COLUMNS_SORT"      => true,
				"ALLOW_ROWS_SORT"         => $arParams['ALLOW_COLUMNS_SORT'],
				"ALLOW_COLUMNS_RESIZE"    => true,
				"ALLOW_HORIZONTAL_SCROLL" => true,
				"ALLOW_SORT"              => true,
				"ALLOW_PIN_HEADER"        => true,
				"ACTION_PANEL"            => $arResult['GROUP_ACTIONS'],
				
				"SHOW_CHECK_ALL_CHECKBOXES" => false,
				"SHOW_ROW_CHECKBOXES"       => false,
				"SHOW_ROW_ACTIONS_MENU"     => true,
				"SHOW_GRID_SETTINGS_MENU"   => true,
				"SHOW_NAVIGATION_PANEL"     => true,
				"SHOW_PAGINATION"           => true,
				"SHOW_SELECTED_COUNTER"     => false,
				"SHOW_TOTAL_COUNTER"        => true,
				"SHOW_PAGESIZE"             => true,
				"SHOW_ACTION_PANEL"         => true,
				
				"ENABLE_COLLAPSIBLE_ROWS" => true,
				'ALLOW_SAVE_ROWS_STATE'   => true,
				
				"SHOW_MORE_BUTTON"  => false,
				'~NAV_PARAMS'       => $arResult['GET_LIST_PARAMS']['NAV_PARAMS'],
				'NAV_OBJECT'        => $arResult['NAV_OBJECT'],
				'NAV_STRING'        => $arResult['NAV_STRING'],
				"TOTAL_ROWS_COUNT"  => count($arResult['ROWS']),
				"CURRENT_PAGE"      => $arResult['CURRENT_PAGE'],
				"PAGE_SIZES"        => $arParams['ORDERS_PER_PAGE'],
				"DEFAULT_PAGE_SIZE" => 50
			],
			$component,
			['HIDE_ICONS' => 'Y']
		);
		?>
	</div>
<? endif ?>
<style>
	.main-grid-wrapper {
		padding: 5px;
	}
	.nicescroll-rails-hr {
		position: relative;
	}
</style>
<script>
	//$('.main-grid-container').niceScroll({emulatetouch: true, bouncescroll: false, cursoropacitymin: 1, enabletranslate3d: true, cursorfixedheight: '100', scrollspeed: 25, mousescrollstep: 10,  cursorwidth: '8px', horizrailenabled: true, cursordragontouch: true});

</script>