
$(document).ready(function(){
  	$('#map-canvas').height($('#map-canvas').width()-100);

			// reset
			$('#nama').val('');
			$('#email').val('');
			$('#pesan').val('');
			
	$('form#frm_kontak').on('submit',function(e){
			// $sample_row_rd_add = '<tr><td>'+$('#bidang').val()+'</td><td>'+$('#program').val()+'</td><td>1</td><td nowrap="">'+$action_rd+'</td></tr>';
			// $('#program').val('');
			// $('#tbl_daftarprogramrd').find('tbody').append($sample_row_rd_add);

	        // var json = { "uid": "user123", "firstName": "User", "lastName": "Theuser" };
	        // t.row.fnAddData(json);

			e.preventDefault();
	        frmData = $('#frm_kontak').serializeArray();

	    	loadAnimationTo('div_kontak');

			// alert(frmData);

			// reset
			$('#nama').val('');
			$('#email').val('');
			$('#pesan').val('');

			// 
			frmData.push({'cntmode':'kirimpesan','ajaxOn':1});
		    $.post(PUSDAHOST+'ajax/kontak/kirimpesan/1?',frmData)
		        .success(function(data) { 
		            var addResult = jQuery.parseJSON(data);
		            if (errorCheck(addResult.message)) {
		                $.notify({message: addResult.message}, {type: "warning"} ); 
		            } else {
		                $.notify({message: addResult.message}, {type: "success"} ); 
		            }
		            removeAnimationFrom('div_kontak');
		        })
		        .error(function(jqXHR, textStatus) {    
		            add_aai_notif ('error','e');
		            removeAnimationFrom('div_kontak');
		        });    

		    // removeAnimationFrom('div_kontak');

		});
});

function initialize() {
	var myLatlng = new google.maps.LatLng(-2.9090118,140.7728903);
	var mapOptions = {
	zoom: 15,
	center: myLatlng}

	var map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

	var marker = new google.maps.Marker({
	position: myLatlng,
	map: map,
	title: 'Kantor Pusdalisbang Papua'
	});
}

google.maps.event.addDomListener(window, 'load', initialize);


