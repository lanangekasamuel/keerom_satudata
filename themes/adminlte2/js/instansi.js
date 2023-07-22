$(document).ready(function(){
	table = document.getElementById('table_instansi');

	if (table) {
		thead = table.querySelector('thead').children[0];
		tbody = table.querySelector('tbody');

		$(table).dataTable({
			columnDefs: [
				{
					orderable: false,
					targets: [thead.children.length-1,thead.children.length-2]
				},
			],
		});
	}
});
