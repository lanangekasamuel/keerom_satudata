
function activateSearch(){
	$('#frm_search').submit(function(event) {
		var kw = $('#search').val().trim();
		event.preventDefault();

		if (kw.length > 0) {
			fData = $(this).serialize();
			 $('#search_result').html('');
			loadAnimationTo('search_panel');
	        // alert(fData); 
	        $.get(PUSDAHOST+'ajax/search/urusan/'+1+'?'+fData,{'ajaxOn':1})
	                .success(function(data) { 
	                    removeAnimationFrom('search_panel');
	                    var jsSearch = jQuery.parseJSON(data);
	                    $('#search_result').html(jsSearch.content);
	                    if (jsSearch.numfound > 0) {
	                    	// serachDataTable();
	                    	$('#tb_serach_result').DataTable({
							  "columnDefs": [
							  	{ "paging":   false, "pageLength": 50},
							  	{ "orderable": false, "targets": jsSearch.lastcoloum }, //disabling 4th index column
							  ]
							} 
							);
	                    }
	                })
	                .error(function(jqXHR, textStatus) {    
	                    removeAnimationFrom('search_panel');
	                    add_aai_notif ('ajax error');
	                });
		} else {
			alert('masukkan kata kunci!');
		}
	});
}

function serachDataTable(){
		$('#tb_serach_result').DataTable({
					  "columnDefs": [
					    { "orderable": false, "targets": 3 }, //disabling 4th index column
					  ]
					} 
					);
}

function openChart(id){

	loadAnimationTo('chartModal');
	$('#modal_content').html('<div id="chart1" class="bg-gray" style="min-width: 400px; height: 400px; margin: 0 auto"></div>');
	$('#chartModal').modal({
        show: 'true'
    }); 

    $('#chartModal').on('hidden.bs.modal', function () {
  		$('#modal_content').html('&nbsp;');
	});

	loadChart(id);
	removeAnimationFrom('chartModal');
}

$(document).ready(function(){
	activateSearch();

	$('#kelompok_urusan').on('change',function(index){
		// alert($(this).val());
		disableElement('jenis_urusan');
		disableElement('search');
		$.get(PUSDAHOST+'ajax/progis/jenis_urusan/'+$(this).val()+'?',{'ajaxOn':1})
	                .success(function(data) { 
	                    // removeAnimationFrom('search_panel');
	                    enableElement('jenis_urusan');
	                    enableElement('search');
	                    var jsSearch = jQuery.parseJSON(data);
	                    $('#jenis_urusan').html('<option value="0">--semua jenis--</option>'+jsSearch.options);
	                })
	                .error(function(jqXHR, textStatus) {    
	                    // removeAnimationFrom('search_panel');
	                    enableElement('jenis_urusan');
	                    enableElement('search');
	                    add_aai_notif ('ajax error - autoCompleteGalleryTag(wallpaper.js)','e');
	                });
	});
	$('#kelompok_urusan').change();
});


