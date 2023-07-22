$(document).ready(function(){
	  // notfikasi
	$.notifyDefaults({
        // type: 'warning',
        allow_dismiss: true,
        placement: {
                from: "top",
                align: "left"
        }
    });

});


function editUrusan(id) {
    // id yg dikirmkan adalah complex id
	loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','formmode':'edit','id':id,'ajaxOn':1})
        .success(function(data) { 
            var editContent = jQuery.parseJSON(data);
            if (errorCheck(editContent.message)) {
            	$.notify({message: editContent.message}, {type: "warning"} ); 
                $('#commonModal').modal('hide');
            } else {
            	// $.notify({message: editContent.message}, {type: "success"} ); 
                $('#modal_content').html(editContent.content);
                refreshForm();
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    

    removeAnimationFrom('commonModal');
}

function addUrusan(idparent) {
	loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','idparent':idparent,'ajaxOn':1})
        .success(function(data) { 
            var addContent = jQuery.parseJSON(data);
            if (errorCheck(addContent.message)) {
            	$.notify({message: addContent.message}, {type: "warning"} ); 
            } else {
            	// $.notify({message: addContent.message}, {type: "success"} ); 
                $('#modal_content').html(addContent.content);
                refreshForm(idparent);
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    

    removeAnimationFrom('commonModal');
}

function removeUrusan(id) {

    if(confirm('Anda yakin akan menghapus data ini?')){
    // 
    $.get('#',{'cntmode':'del','id':id,'ajaxOn':1})
        .success(function(data) { 
            var removedContent = jQuery.parseJSON(data);
            if (errorCheck(removedContent.message)) {
                $.notify({message: removedContent.message}, {type: "warning"} ); 
            } else {
                $.notify({message: removedContent.message}, {type: "success"} ); 
                $('#urusan_'+id.replace('.','_')).remove();
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
        });    
    }
}

function refreshForm(idparent){
/**
 * refsehs object event dari form edit/add
 * id parent digunakan untuk membaca tab parent
 */

 	// on submit event
	$('#frm_urusan').on('submit', function(e){
		e.preventDefault();

		$.post(PUSDAHOST+'giadmin/urusan', $('#frm_urusan').serialize()+'&ajaxOn=1')
			.success(function(data) { 
		       // do something here with response;
		       var $result = jQuery.parseJSON(data);	
		       if (errorCheck($result.message)) {
	            	$.notify({message: $result.message}, {type: "warning"} ); 
                    //
	            } else {
	            	$.notify({message: $result.message}, {type: "success"} ); 
                    // jika edit form, update content
                    if ($result.data.cntmode == 'upd') {
                        $t_row = $('tr#urusan_'+$result.data.id.replace('.','_'));
                        // alert($result.data.id);
                        $t_row.addClass('bg-warning text-green');
                        $t_row.find('td.urusan').html($result.data.urusan);
                    }
                    // jika add form, add content to parent->lastchild
                    else if ($result.data.cntmode = 'ins') {
                        $t_row = $('tr#urusan_'+$result.data.after_idurusan.replace('.','_'));
                        $t_row.after($result.row_content);
                    }
	                // $('#modal_content').html($result.content);
	            }	
	            // hide edit modal
	            $('#commonModal').modal('hide');
		     });
	});	
}

