<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$this->setFrameMode(false);
$errors = implode($arResult["ERRORS"]);

if (strlen($arResult["MESSAGE"]) > 0):?>
    <?= 'Ваш отзыв отправлен, после модерации он появится здесь.' ?>
<? else: ?>
    <form name="iblock_add" action="<?= POST_FORM_ACTION_URI ?>" method="post" enctype="multipart/form-data" id="iblock_add">
        <?= bitrix_sessid_post() ?>
	    <?
	    $userID = $USER->GetID();
	    ?>
        <input type="hidden" name="PROPERTY[DATE_ACTIVE_FROM][0]" value="<?= date('d.m.Y H:i:s', time()); ?>">
        <input type="hidden" name="PROPERTY[NAME][0]" value="Отзыв пользователя <?=$userID?>">
        <input type="hidden" name="PROPERTY[257][0]" value="<?=$userID?>">
        <div class="reviews__subwrap">
            <div class="reviews__title template-top__title">Оставить отзыв</div>
	        <div class="form-group row">
		        <label class="col-lg-3 col-form-label">Сообщение *</label>
		        <div class="col-lg-9">
			        <textarea placeholder="Ваш отзыв*" name="PROPERTY[PREVIEW_TEXT][0]" class="form-control text-input text-area jsInput reviews__input" value="<?= htmlspecialchars($_REQUEST['PREVIEW_TEXT'][0]) ?>" required></textarea>
		        </div>
	        </div>
            <? if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0): ?>
                <label for="" class="text-label">
                    <input value="<?= $arResult["CAPTCHA_CODE"] ?>" name="captcha_sid" type="hidden">
                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>" width="180"
                         height="40" alt="CAPTCHA"/>
                </label>
                <label for="" class="text-label">
                    <input value="" name="captcha_word" type="text" class="text-input jsInput reviews__input"
                           autocomplete="off" requered="">
                    <span class="text-placeholder jsLabel">Введите слово с картинки*</span>
                </label>
            <? endif ?>
            <input type="submit" name="<?= $arParams['SUBMIT_NAME']; ?>" class="btn btn_b2b reviews__submit classic-button ticker__set ticker__hover ticker__timed" value="Отправить">
        </div>
    </form>
<? endif; ?>