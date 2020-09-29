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
			window.location.href = headerCart.find(".navbar-nav-link").attr("href");
		}
	});
};

$(function(){
	$(".preset-title-link").click(function(e){
		e.preventDefault();
		var id = $(this).data("id");
		add2basket(id);
	});
});