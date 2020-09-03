$(function () {
	var calcContactHeight = function() {
		var maxHeight = 0;
		var title = $(".contacts-section-item__position")

		title.each(function(){
			var thisH = $(this).height();
			if (thisH > maxHeight) { maxHeight = thisH; }
		});
		title.height(maxHeight);
	};
	calcContactHeight();
});

$(function () {
	var table = $('.report-section-table');
	var row = $('.report-section-table__row')[0];
	var items = row.querySelectorAll('.report-section-table__item');
	table.css("min-width", "" + items.length * 72 + "px");
});
