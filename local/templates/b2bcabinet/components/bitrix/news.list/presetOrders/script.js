var deleteItem = function(item){
	$.ajax({
		url: "/ajax/presetOrder.php",
		method: "POST",
		data: {
			action: "delete",
			item: item
		},
		success: function (data) {
			BX.Main.gridManager.getById("presetsList").instance.reloadTable();
		}
	});
};

var add2basket = function(item){
	$.ajax({
		url: "/ajax/presetOrder.php",
		method: "POST",
		data: {
			action: "add2basket",
			item: item
		},
		success: function (data) {
			var ajaxData = JSON.parse(data),
				headerCart = $(".cart_header");
			BX.setCookie("ORGANIZATION_ID", ajaxData.ORGANIZATION, {expires: 3600, path: "/"});
			//headerCart.find(".badge").text(ajaxData.QUANTITY);
			window.location.href = headerCart.find(".navbar-nav-link").attr("href");
			//BX.Main.gridManager.getById("presetsList").instance.reloadTable();
		}
	});
};