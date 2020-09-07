function setExcelOutIcon(icon) {
	let iconContainer = document.querySelector('.export_excel_preloader > i');
	iconContainer.setAttribute('class', icon);
}

function excelOut() {
	//BX.showWait();
	setExcelOutIcon('icon-spinner2 spinner mr-2');
	//setTimeout(function () {
	var headerData = {},
		headerTable = $('.main-grid-container .main-grid-row-head .main-grid-draggable');

	headerTable.each(function (key, item) {
		headerData[key] = {
			id: $(this).data('name'),
			value: $(this).find('.main-grid-head-title').text()
		};
	});

	$.ajax({
		type: 'POST',
		async: false,
		url: '',
		data: headerData,
		success: function (data) {
			console.log(data);
			/*if (data !== undefined && data !== '') {
				try {
					data = JSON.parse(data);
				} catch (e) {

				}
			}

			if (data.TYPE !== undefined) {
				console.log(data.MESSAGE);
			} else if (data !== undefined && data !== '') {
				file = data;
			}*/
		},
		complete: function () {
			setExcelOutIcon('icon-upload mr-2');
		}
	});

	/*if (file !== undefined && file !== '') {
		var now = new Date();

		var dd = now.getDate();
		if (dd < 10) dd = '0' + dd;
		var mm = now.getMonth() + 1;
		if (mm < 10) mm = '0' + mm;
		var hh = now.getHours();
		if (hh < 10) hh = '0' + hh;
		var mimi = now.getMinutes();
		if (mimi < 10) mimi = '0' + mimi;
		var ss = now.getSeconds();
		if (ss < 10) ss = '0' + ss;

		var rand = 0 - 0.5 + Math.random() * (999999999 - 0 + 1);
		rand = Math.round(rand);

		var name = 'blank_' + now.getFullYear() + '_' + mm + '_' + dd + '_' + hh + '_' + mimi + '_' + ss + '_' + rand + '.xlsx';

		var link = document.createElement('a');
		link.setAttribute('href', file);
		link.setAttribute('download', name);
		var event = document.createEvent('MouseEvents');
		event.initMouseEvent(
			'click', true, false, window, 0, 0, 0, 0, 0
			, false, false, false, false, 0, null
		);
		link.dispatchEvent(event);
	}*/
	//BX.closeWait();
	//}, 15);
}

$(document).ready(function () {
	$(document).on('click touchstart', '#blank-export-in-excel', this, excelOut);
});