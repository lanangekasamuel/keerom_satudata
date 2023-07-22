

function detailTahunanInstansi(idinstansi,tahun) {
	alert(a);
}

$(document).ready(function(){
	$('#filter-instansi').select2();

	$('#filter-instansi').on('change',function(){
		$idinstansi = $(this).val();
		filterIntansi($idinstansi);
	});

	// create list of instansi-progress object
	var $ipo = [];
	$('div.instansi-progress').each(function(index){
		// $(this).appendTo($('#testtc'));
		var sectiondata = {};
		sectiondata.id = $(this).data('id');
		sectiondata.obid = $(this).attr('id');
		sectiondata.jmlindikator = $(this).data('jumlah-indikator');
		sectiondata.jmlentry = $(this).data('progress-entry');
		// sectiondata.content = $(this).html();
		$ipo.push(sectiondata);
	});

	$('#filter-progress').on('change',function(){
		method = $(this).val();
		$('#filter-instansi').val(0);
		$('#filter-instansi').change();
		$('#filter-indikator').val(0);
		// $('#filter-indikator').change();
		// $('#instansi-progress').hide();
		orderByProgress($ipo,method,10);
	});

	$('#filter-indikator').on('change',function(){
		method = $(this).val();
		$('#filter-instansi').val(0);
		$('#filter-instansi').change();
		$('#filter-progress').val(0);
		// $('#filter-progress').change();
		// $('#instansi-progress').hide();
		orderByIndikator($ipo,method,10);
	});

});

function filterIntansi(idinstansi) {
	// filter list % berdasarkan instansi
	if ($idinstansi == 0) {
		$('div.instansi-progress').show();
	} else {
		$('div.instansi-progress').hide();
		$('div#instansi-'+$idinstansi).show();
	}
}

function orderByIndikator($ipo,method,shouwnum) {
	/*
	 | order/filter list % berdasarkan indikator
	 | method = ASC | DESC
	 | shownum = jumlah ditampilkan
	 */
	if (method == 0) {return false;}
	var arrayOfObjects = $ipo;

	// use slice() to copy the array and not just make a reference
	var oByIndikator = arrayOfObjects.slice(0);
	oByIndikator.sort(function(a,b) {
	    // ordering method
	    if (method == 'ASC') {
		    return a.jmlindikator - b.jmlindikator; 
	    } else {
	    	return b.jmlindikator - a.jmlindikator; 
	    }
	});	
	$.each(oByIndikator,function(index,data){
		$('#'+data.obid).appendTo($('#testtc'));
		// $('#filter-instansi').fadeIn();
	});
}

function orderByProgress($ipo,method,shouwnum) {
	/*
	 | order/filter list % berdasarkan % progress
	 | method = ASC | DESC
	 | shownum = jumlah ditampilkan
	 */
	if (method == 0) {return false;}
	var arrayOfObjects = $ipo;

	// use slice() to copy the array and not just make a reference
	var oByProgress = arrayOfObjects.slice(0);
	oByProgress.sort(function(a,b) {
	    // ordering method
	    if (method == 'ASC') {
		    return a.jmlentry - b.jmlentry; 
	    } else {
	    	return b.jmlentry - a.jmlentry; 
	    }
	});
	
	$.each(oByProgress,function(index,data){
		$('#'+data.obid).appendTo($('#testtc'));
		// $('#filter-instansi').fadeIn();
	});
}



