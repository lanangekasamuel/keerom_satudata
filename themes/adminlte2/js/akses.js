function add_aai_notif(msg){
	console.log(msg);
}

function loadAnimationTo(obid){
	$('#'+obid).html('<div class="text-center"><h1><i class="fa fa-gear faa-spin animated"></i></h1></div>');
}

function editGroup(id){
	/* 
	 * open edit form for user with specified id
	 */

	$('#editModal').find('div.modal-dialog').addClass('modal-lg');

	$('#editModal').modal({
        show: 'true'
    }); 

    $('#editModal').on('hidden.bs.modal', function () {
  		$('#modal_content').html('&nbsp;');
	});

    loadAnimationTo('modal_content');

    $.get('#',{'cntmode':'form','id':id,'ajaxOn':1})
		.success(function(data) { 
// //alert(data);
// 					//do some advnced function here
// 					//printCheckServerError(data);
			//var $sdata = data.split(data_spliter);
			var $result = jQuery.parseJSON(data);
			$('#modal_content').html($result.content);

// 					// if ($taglist != undefined) {
// 					// 	response($taglist);
// 					// }
// 					// else {
// 					// 	add_aai_notif ('server error','e');
// 					// }

			$('#form_user_edit').on('submit', function(e){
			      e.preventDefault();

			      // $("#username").val( $.md5($.md5($("#username").val().toLowerCase())) ); //escape case sesitive username
        		// $("#pass").val($.md5($("#pass").val()) );

			      $.post(rootdir+'giadmin/user', $('#form_user_edit').serialize()+'&cntmode=upd&id='+id)
			         .success(function(data) { 
			           // do something here with response;
			           // alert(data);
			           var $result = jQuery.parseJSON(data);
						$('#modal_content').html($result.message);
			         });
			    });

		})
		.error(function(jqXHR, textStatus) {	
			add_aai_notif ('ajax error - autoCompleteGalleryTag(wallpaper.js)','e');
		});
}
