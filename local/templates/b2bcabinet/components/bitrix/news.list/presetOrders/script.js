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