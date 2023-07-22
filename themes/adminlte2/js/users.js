
// function editUser(id){
// 	/* 
// 	 * open edit form for user with specified id
// 	 */
// 	$('#editModal').modal({
//         show: 'true'
//     }); 

//     $('#editModal').on('hidden.bs.modal', function () {
//   		$('#modal_content').html('&nbsp;');
// 	});

//     loadAnimationTo('modal_content');

//     $.get('#',{'cntmode':'form','id':id,'ajaxOn':1})
// 				.success(function(data) { 
// 					var $result = jQuery.parseJSON(data);
// 					$('#modal_content').html($result.content);

// 					$('#form_user_edit').on('submit', function(e){
// 					      e.preventDefault();
// 					      $.post(rootdir+'giadmin/user', $('#form_user_edit').serialize()+'&cntmode=upd&id='+id)
// 					         .success(function(data) { 
// 					           // do something here with response;
// 					           // alert(data);
// 					           var $result = jQuery.parseJSON(data);
// 								$('#modal_content').html($result.message);
// 					         });
// 					    });

// 				})
// 				.error(function(jqXHR, textStatus) {	
// 					add_aai_notif ('ajax error - autoCompleteGalleryTag(wallpaper.js)','e');
// 				});
// }

$(document).ready(function(){
	$('#tb_user').DataTable({
		"bPaginate": false, 
		// "bFilter": false, 
		"sScrollY": "400", 
		"sScrollX": "100%", 
		"sScrollXInner": "100%", 
		"bScrollCollapse": true,
		search: {caseInsensitive: true },
		fixedHeader: true,
        aoColumnDefs: [
	      { "sClass": "text-right text-nowrap", "aTargets": [ 7 ] },
	      { "sClass": "text-center text-nowrap", "aTargets": [ 4 ] },
	      { "sClass": "text-center", "aTargets": [ 0 ] },
	      { bSortable: false, "aTargets": [ 8 ] }
	    ]
		// "columnDefs": [
		//   { "orderable": false, "targets": 7 }, //disabling 4th index column
		// ]
		});

	// event saat idgroup input di select
	$('#idgroup').on('change',function(){
		$idgroup = $(this).val();

		$('#idinstansi').val(0);
		$('#idbidang_instansi').val(0);

		if ($idgroup == 1) {
			disableElement('idinstansi');
			disableElement('idbidang_instansi');
		} else if ($idgroup == 2) {
			enableElement('idinstansi');
			disableElement('idbidang_instansi');
		} else if ($idgroup == 3) {
			enableElement('idinstansi');
			enableElement('idbidang_instansi');
		}
	});

	// event saat idinstansi input di select, load bidang
	$('#idinstansi').on('change',function(){
		$idinstansi = $(this).val();
		$idgroup 	= $('#idgroup').val();

		disableElement('idbidang_instansi');
	    $.get(PUSDAHOST+'ajax/user/bidang_instansi/'+$idinstansi+'?',{'ajaxOn':1})
	        .success(function(data) { 
	            if ($idgroup == 3) enableElement('idbidang_instansi');
	            var bidang = jQuery.parseJSON(data);
	            if (bidang.options != null) {
	                $('#idbidang_instansi').html('<option value="0">-- Pilih Bidang --</option>'+bidang.options);
	            } else {
	                $('#idbidang_instansi').html('<option value="0">-- Pilih Bidang --</option>');
	            }
	            })
	        .error(function(jqXHR, textStatus) {    
	           	if ($idgroup == 3) enableElement('idbidang_instansi');
	            add_aai_notif ('error','e');
	        });		
		});

});
