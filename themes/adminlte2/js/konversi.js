$(document).ready(function(){
	// auto complete pencarian kelompok
	$('#kelompok_ac').autocomplete({
		source: function(request,response){
			$.post(PUSDAHOST+'ajax/konversi/listkelompok/1?keyword='+request.term)
				.success(function(data) { 
					//do some advanced function here
					var $listkelompok = jQuery.parseJSON(data);
					response($listkelompok);
				})
				.error(function(jqXHR, textStatus) {	
					add_aai_notif ('ajax error');
				});
		},
        minLength: 1,
        select: function(event, ui) {
              var iid = ui.item.id;
              $(this).parent('div').find('input[type=hidden]').val(iid);
        },
       delay : 900
	});

	 $('.btn_load_kelompok_ac').on('click',function(){
        //trigger load ajax
        
        near_select = $(this).parents('div.input-group').find('input[type=hidden]');

        $_id = near_select.attr('id');
        $load_id = near_select.val();

        if ($load_id == 0) {
            alert('pilih kelompok terlebih dahulu');
        } else {
            loadDaftarKelompok($load_id);
        }
    });

     $('.btn_load_kelompok_instansi').on('click',function(){
        //trigger load ajax
        near_select = $(this).parents('div.input-group').find('select');
        $_id = near_select.attr('id');
        $load_id = near_select.val();

        if ($load_id == 0) {
            alert('pilih instansi terlebih dahulu');
        } else {
            loadDaftarKelompok_instansi($load_id);
        }
    });

     $('.btn_load_kelompok').on('click',function(){
        //trigger load ajax
        near_select = $(this).parents('div.input-group').find('select');
        $_id = near_select.attr('id');
        $load_id = near_select.val();

        if ($load_id == 0) {
            alert('pilih item terlebih dahulu');
        } else {
            loadDaftarKelompok($load_id);
        }
    });

	// seleksi jenis kelompok

 	$('.sub_kelompok').hide();	
	loadChild(719,'select_subkelompok1','select_subkelompok2');

	$('#select_jenis').on('change',function(){
    $('.sub_kelompok').hide();
	    loadChild($(this).val(),'select_jenis','select_kelompok');
	 });

	  $('#select_kelompok').on('change',function(){
	    $('.sub_kelompok').hide();
	    loadChild($(this).val(),'select_kelompok','select_subkelompok1');
	 });

	  $('#select_subkelompok1').on('change',function(){
	    loadChild($(this).val(),'select_subkelompok1','select_subkelompok2');
	 });

	  $('#select_subkelompok2').on('change',function(){
	    loadChild($(this).val(),'select_subkelompok2','select_subkelompok3');
	 });


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

function loadChild(id,trigger,target){
    if (id == 0) {
        return false
    }
    disableElement(trigger);
    disableElement(target);
     $.get(PUSDAHOST+'ajax/konversi/listsubkelompok/'+id+'?',{'ajaxOn':1})
        .success(function(data) { 
            enableElement(trigger);
            enableElement(target);
            var jsSearch = jQuery.parseJSON(data);
            if (jsSearch.options != null) {
                $('#'+target).parents('div.col.sub_kelompok').show();
                $('#'+target).html('<option value="0">-- Pilih Kelompok Data --</option>'+jsSearch.options);
            } else {
                $('#'+target).parents('div.col.sub_kelompok').hide();
                $('#'+target).html('<option value="0">-- Pilih Kelompok Data --</option>');
            }
            })
        .error(function(jqXHR, textStatus) {    
            enableElement(trigger);
            enableElement(target);
            add_aai_notif ('error','e');
        });
}

function loadDaftarKelompok(id){
    loadAnimationTo('admin_container');
    $select_table = $('#select_table').val();
    $.get(PUSDAHOST+'ajax/konversi/tabelkelompok/'+id+'?',{'ajaxOn':1,'type':'kelompok','select_table':$select_table})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
                $('#table_kelompok').find('thead').html(tableData.header);
                $('#table_kelompok').find('tbody').html(tableData.body);
                // $('div.ie_option').html(tableData.opsidata);
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    
}

function loadDaftarKelompok_instansi(id){
    loadAnimationTo('admin_container');
    $.get(PUSDAHOST+'ajax/konversi/tabelkelompokinstansi/'+id+'?',{'ajaxOn':1,'type':'instansi'})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
                $('#table_kelompok').find('thead').html(tableData.header);
                $('#table_kelompok').find('tbody').html(tableData.body);
                // $('div.ie_option').html(tableData.opsidata);
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    
}

function editKelompok(id) {
	loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','id':id,'ajaxOn':1})
        .success(function(data) { 
            var editContent = jQuery.parseJSON(data);
            if (errorCheck(editContent.message)) {
            	$.notify({message: editContent.message}, {type: "warning"} ); 
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

function addKelompokChild(idparent) {
	loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','idparent':idparent,'ajaxOn':1})
        .success(function(data) { 
            var editContent = jQuery.parseJSON(data);
            if (errorCheck(editContent.message)) {
            	$.notify({message: editContent.message}, {type: "warning"} ); 
            } else {
            	// $.notify({message: editContent.message}, {type: "success"} ); 
                $('#modal_content').html(editContent.content);
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

function removeKelompok(id) {

    if(confirm('Anda yakin akan menghapus data ini?')){
    // 
    $.get('#',{'cntmode':'del','id':id,'ajaxOn':1})
        .success(function(data) { 
            var removedContent = jQuery.parseJSON(data);
            if (errorCheck(removedContent.message)) {
                $.notify({message: removedContent.message}, {type: "warning"} ); 
            } else {
                $.notify({message: removedContent.message}, {type: "success"} ); 
                $('#kelompok_'+id).remove();
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
	$('#frm_kelompok').on('submit', function(e){
		e.preventDefault();
        $tab = $('#kelompok_'+idparent).data('tab');
		$.post(PUSDAHOST+'giadmin/kelompok', $('#frm_kelompok').serialize()+'&ajaxOn=1'+'&tab='+$tab)
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
                        $t_row = $('tr#kelompok_'+$result.data.id);
                        $t_row.addClass('bg-warning text-green');
                        $t_row.find('td.urai span').html($result.data.urai);
                        $t_row.find('td.satuan').html($result.data.satuan);
                        $t_row.find('td.formula').html($result.data.formula);
                        $t_row.find('td.nama_instansi').html($result.data.nama_instansi);
                        $t_row.find('td.bidang').html($result.data.bidang);
                        $t_row.find('td.sub_urusan').html($result.data.sub_urusan);
                        $t_row.find('td.penggunaan').html($result.data.penggunaan);
                    }
                    // jika add form, add content to parent->lastchild indikator
                    else if ($result.data.cntmode = 'ins') {
                        $t_row = $('tr#kelompok_'+$result.data.after_idkelompok);
                        $t_row.after($result.row_content);
                    }
	                // $('#modal_content').html($result.content);
	            }	
	            // hide edit modal
	            $('#commonModal').modal('hide');
		     });
	});

 	// formula autocomplete
 	// memisahkan berdasarkan karakter perhitungan (*/+-);
 	// memisahkan idkelompok dengan chracter {digit:idkelp}
    // typeahed & tagsinput
    parseFormula();

	// event saat idinstansi input di select, load bidang & ganti sub_urusan
	$('#idinstansi').on('change',function(){
		$idinstansi = $(this).val();

		disableElement('idbidang_instansi');
	    $.get(PUSDAHOST+'ajax/user/bidang_instansi/'+$idinstansi+'?',{'ajaxOn':1})
	        .success(function(data) { 
	            var bidang = jQuery.parseJSON(data);
	            if (bidang.options != null) {
	                $('#idbidang_instansi').html('<option value="0">-- Pilih Bidang --</option>'+bidang.options);
	            } else {
	                $('#idbidang_instansi').html('<option value="0">-- Pilih Bidang --</option>');
	            }
	            enableElement('idbidang_instansi');
	        })
	        .error(function(jqXHR, textStatus) {    
	            add_aai_notif ('error','e');
	            enableElement('idbidang_instansi');
	        });		

        disableElement('sub_urusan');
        $.get(PUSDAHOST+'ajax/konversi/sub_urusan/'+$idinstansi+'?',{'ajaxOn':1})
            .success(function(data) { 
                var sub_urusan = jQuery.parseJSON(data);
                if (sub_urusan.options != null) {
                    $('#sub_urusan').html('<option value="0">-- Pilih Sub Urusan --</option>'+sub_urusan.options);
                } else {
                    $('#sub_urusan').html('<option value="0">-- Pilih Sub Urusan --</option>');
                }
                enableElement('sub_urusan');
            })
            .error(function(jqXHR, textStatus) {    
                add_aai_notif ('error','e');
                enableElement('sub_urusan');
            }); 

	});
}

function parseFormula() {

    // $('#formula').tagsinput({
    //         confirmKeys: [13, 44],
    //         maxTags: 1,
    //         typeahead: {                  
    //             source: function(query) {
    //                 return $.get(PUSDAHOST+'ajax/konversi/listkelompok_analisa/1',{'keyword':query});
    //             }
    //         }
    //     });
    //     $('#formula').on('itemAdded', function(event) {
    //         setTimeout(function(){
    //             $(">input[type=text]",".bootstrap-tagsinput").val("");
    //         }, 1);
    //     });

    // $('input#formula').tagsinput({
    //     typeahead: {
    //         source: function(query) {
    //                 return $.get(PUSDAHOST+'ajax/konversi/listkelompok_analisa/1',{'keyword':query});
    //             }
    //       },
    //       freeInput: true
    // });

    $.get(PUSDAHOST+'ajax/konversi/listkelompok_analisa/1')
                   .success(function(data) {
                       // result = data;
                        // console.log(data);
                        var $source_data =  jQuery.parseJSON(data);
                        $("#formula").tagsinput({
                              itemValue: 'value',
  itemText: 'text',
                            typeahead: {
                                source: $source_data
                               }
                        });
                   });

}
