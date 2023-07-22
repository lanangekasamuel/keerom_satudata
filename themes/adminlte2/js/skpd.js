$(document).ready(function(){
	$('#table_skpd').DataTable({
					  "columnDefs": [
					    { "orderable": false, "targets": 5 }, //disabling 4th index column
					  ]
					} 
					);
});

function editSKPD(id){
	/* 
	 * open edit form for user with specified id
	 */
	$('#editModal').modal({
        show: 'true'
    }); 

    $('#editModal').on('hidden.bs.modal', function () {
  		$('#modal_content').html('&nbsp;');
	});

    loadAnimationTo('editModal');

    $.get('#',{'cntmode':'form','id':id,'ajaxOn':1})
				.success(function(data) { 

					var $result = jQuery.parseJSON(data);
					$('#modal_content').html($result.content);

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

function displaySkpdKelompok(skpdid){
/* 
 * open edit form for user with specified id
 */
	// alert(skpdid);
	$('#detailModal').modal({
        show: 'true'
    }); 

    $('#detailModal').find('div.modal-dialog').addClass('modal-lg').css('width','96%');
    // get skpd name
    // $skpd_name = $('#skpd_'+skpdid).length;//    find('span.nama_skpd')
    // alert($skpd_name);
    $('#detailModal').find('h4.modal-title').html('Kelompok Data');

    $('#detailModal').on('hidden.bs.modal', function () {
  		$('#modal_content').html('&nbsp;');
	});

    $('#modal_content').html(setAnimation());

    $.get(PUSDAHOST+'ajax/skpd/kelompokskpd/'+skpdid,{'idskpd':skpdid,'ajaxOn':1})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.body != null) {
                $('#modal_content').html(tableData.body);
                $('#detailModal').find('h4.modal-title').html('Data : '+tableData.nama_skpd);
                // cellNavigation();
            }
            // removeAnimationFrom('modal_content');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            // removeAnimationFrom('modal_content');
        });   
}

function openSkpdChart(id) {
	loadAnimationTo('chartModal');
	$('#chart_content').html('<div id="chart1" class="bg-gray" style="min-width: 400px; height: 400px; margin: 0 auto"></div>');

    loadChart(id,'matrik');
    removeAnimationFrom('chartModal');
}
