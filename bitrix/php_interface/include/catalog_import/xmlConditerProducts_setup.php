<?
//<title>Импорт товаров</title>
/** @global array $arOldSetupVars */
/** @global int $PROFILE_ID */

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/catalog/import_setup_templ.php');

$arSetupErrors = [];

//********************  ACTIONS  **************************************//
if (($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') && $STEP == 1) {
	if (isset($arOldSetupVars['URL_FILE_1C']))
		$URL_FILE_1C = $arOldSetupVars['URL_FILE_1C'];
	if (isset($arOldSetupVars['outFileAction']))
		$outFileAction = $arOldSetupVars['outFileAction'];
	if (isset($arOldSetupVars['SETUP_PROFILE_NAME']))
		$SETUP_PROFILE_NAME = $arOldSetupVars['SETUP_PROFILE_NAME'];
}
if ($STEP > 1) {
	if (strlen($URL_FILE_1C) > 0 && file_exists($_SERVER["DOCUMENT_ROOT"] . $URL_FILE_1C) && is_file($_SERVER["DOCUMENT_ROOT"] . $URL_FILE_1C))
		$DATA_FILE_NAME = $_SERVER["DOCUMENT_ROOT"] . $URL_FILE_1C;
	
	if (strlen($DATA_FILE_NAME) <= 0) {
		$arSetupErrors[] = GetMessage("CICML_ERROR_NO_DATAFILE");
	}
	
	$USE_TRANSLIT = (isset($USE_TRANSLIT) && 'Y' == $USE_TRANSLIT ? 'Y' : 'N');
	$ADD_TRANSLIT = (isset($ADD_TRANSLIT) && 'Y' == $ADD_TRANSLIT ? 'Y' : 'N');
	$keepExistingProperties = (isset($keepExistingProperties) && 'Y' == $keepExistingProperties ? 'Y' : 'N');
	$activateFileData = (isset($activateFileData) && 'Y' == $activateFileData ? 'Y' : 'N');
	
	if (!empty($arSetupErrors)) {
		$STEP = 1;
	}
}
//********************  END ACTIONS  **********************************//

$aMenu = [
	[
		"TEXT" => GetMessage("CATI_ADM_RETURN_TO_LIST"),
		"TITLE" => GetMessage("CATI_ADM_RETURN_TO_LIST_TITLE"),
		"LINK" => "/bitrix/admin/cat_import_setup.php?lang=" . LANGUAGE_ID,
		"ICON" => "btn_list",
	]
];

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!empty($arSetupErrors))
	ShowError(implode('<br />', $arSetupErrors));

$actionParams = "";
if ($adminSidePanelHelper->isSidePanel()) {
	$actionParams = "?IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER";
}
?>
<form method="POST" action="<? echo $APPLICATION->GetCurPage() . $actionParams; ?>" ENCTYPE="multipart/form-data" name="dataload">
	<?
	$aTabs = [
		["DIV" => "edit1", "TAB" => GetMessage("CAT_ADM_CML1_IMP_TAB1"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CML1_IMP_TAB1_TITLE")],
		["DIV" => "edit2", "TAB" => GetMessage("CAT_ADM_CML1_IMP_TAB2"), "ICON" => "store", "TITLE" => GetMessage("CAT_ADM_CML1_IMP_TAB2_TITLE")],
	];
	
	$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);
	
	$tabControl->Begin();
	
	$tabControl->BeginNextTab();
	
	if ($STEP == 1) {
		?>
		<tr class="heading">
		<td colspan="2"><? echo GetMessage("CICML_DATA_IMPORT"); ?></td>
		</tr>
		<tr>
			<td valign="top" width="40%"><? echo GetMessage("CICML_F_DATAFILE2"); ?></td>
			<td valign="top" width="60%">
				<input type="text" name="URL_FILE_1C" size="40" value="<? echo htmlspecialcharsbx($URL_FILE_1C); ?>">
				<input type="button" value="<? echo GetMessage("CICML_F_BUTTON_CHOOSE"); ?>" onclick="cmlBtnSelectClick();"><?
				CAdminFileDialog::ShowScript(
					[
						"event" => "cmlBtnSelectClick",
						"arResultDest" => ["FORM_NAME" => "dataload", "FORM_ELEMENT_NAME" => "URL_FILE_1C"],
						"arPath" => ["PATH" => "/upload/catalog", "SITE" => SITE_ID],
						"select" => 'F',// F - file only, D - folder only, DF - files & dirs
						"operation" => 'O',// O - open, S - save
						"showUploadTab" => true,
						"showAddToMenuTab" => false,
						"fileFilter" => 'xml',
						"allowAllFiles" => true,
						"SaveConfig" => true
					]
				);
				?></td>
		</tr>
		<tr class="heading">
			<td colspan="2"><? echo GetMessage('CATI_ADDIT_SETTINGS'); ?></td>
		</tr>
		<tr>
			<td valign="top" width="40%"><? echo GetMessage("CICML_F_OUTFILEACTION"); ?>:</td>
			<td valign="top" width="60%"><?
				if (!isset($outFileAction) || ($outFileAction != 'keep' && $outFileAction != 'deactivate' && $outFileAction != 'keep')) {
					$outFileAction = COption::GetOptionString("catalog", "default_outfile_action");
				}
				?><input type="radio" name="outFileAction" id="outFileAction_D" value="delete" <?
				if ($outFileAction == "delete") echo "checked"; ?>> <label for="outFileAction_D"><?
					echo GetMessage("CICML_OF_DEL") ?></label><br>
				<input type="radio" name="outFileAction" id="outFileAction_H" value="deactivate" <?
				if ($outFileAction == "deactivate") echo "checked"; ?>> <label for="outFileAction_H"><?
					echo GetMessage("CICML_OF_DEACT") ?></label><br>
				<input type="radio" name="outFileAction" id="outFileAction_F" value="keep" <?
				if ($outFileAction == "keep") echo "checked"; ?>> <label for="outFileAction_F"><?
					echo GetMessage("CICML_OF_KEEP") ?></label>
			</td>
		</tr>
		<?
		if ($ACTION == "IMPORT_SETUP" || $ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') {
			?>
			<tr class="heading">
			<td colspan="2"><? echo GetMessage("CICML_SAVE_SCHEME"); ?></td>
			</tr>
			<tr>
			<td valign="top" width="40%"><?
				echo GetMessage("CICML_SSCHEME_NAME") ?>:
			</td>
			<td valign="top" width="60%">
				<input type="text" name="SETUP_PROFILE_NAME" size="40" value="<? echo htmlspecialcharsbx($SETUP_PROFILE_NAME); ?>">
			</td>
			</tr><?
		}
	}
	
	$tabControl->EndTab();
	
	$tabControl->BeginNextTab();
	
	if ($STEP == 2) {
		$FINITE = true;
	}
	
	$tabControl->EndTab();
	
	$tabControl->Buttons();
	
	?>
	<? echo bitrix_sessid_post(); ?>
	<?
	if ($ACTION == 'IMPORT_EDIT' || $ACTION == 'IMPORT_COPY') {
		?><input type="hidden" name="PROFILE_ID" value="<? echo intval($PROFILE_ID); ?>"><?
	}
	
	if ($STEP < 2) {
		?><input type="hidden" name="STEP" value="<?
		echo intval($STEP) + 1; ?>">
		<input type="hidden" name="lang" value="<?
		echo LANGUAGE_ID; ?>">
		<input type="hidden" name="ACT_FILE" value="<?
		echo htmlspecialcharsbx($_REQUEST["ACT_FILE"]) ?>">
		<input type="hidden" name="ACTION" value="<?
		echo htmlspecialcharsbx($ACTION) ?>">
		<input type="hidden" name="SETUP_FIELDS_LIST" value="URL_FILE_1C,outFileAction">
		<input type="submit" value="<?
		echo (($ACTION == "IMPORT") ? GetMessage("CICML_NEXT_STEP_F") : GetMessage("CICML_SAVE")) . " &gt;&gt;" ?>" name="submit_btn"><?
	}
	$tabControl->End();
	
	?></form>
<script type="text/javascript">
	<?if ($STEP < 2):?>
    tabControl.SelectTab("edit1");
    tabControl.DisableTab("edit2");
	<?elseif ($STEP == 2):?>
    tabControl.SelectTab("edit2");
    tabControl.DisableTab("edit1");
	<?endif;?>
</script>