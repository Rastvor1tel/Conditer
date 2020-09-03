<?php
$this->addExternalCss($this->GetFolder()."/css/fast.css");
$this->addExternalCss($this->GetFolder()."/css/index.css");
$this->addExternalCss($this->GetFolder()."/css/popup.css");
$this->addExternalCss($this->GetFolder()."/css/custom.css");
$this->addExternalJS($this->GetFolder()."/js/fast.js");
$this->addExternalJS($this->GetFolder()."/js/vendor.js");
?>

<div class="popup" tabindex="-1" role="dialog" aria-labelledby="modal">
	<div class="popup__overlay js-popupWatch">
		<div class="popup__body js-popupBody">
			<div class="popup__close js-popupClose">
				<svg width="100%" height="100%" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="0.221832" y="14.5459" width="20.5704" height="2.05704" transform="rotate(-45 0.221832 14.5459)" fill="black"/>
					<rect x="1.67621" width="20.5704" height="2.05704" transform="rotate(45 1.67621 0)" fill="black"/>
				</svg>
			</div>
			<div class="popup__wrap js-popupWrap" data-simplebar>
				<div class="popup-loader">
					<div class="popup-loader__spin"></div>
				</div>
				<div class="popup-target">
				</div>
			</div>
		</div>
	</div>
</div>