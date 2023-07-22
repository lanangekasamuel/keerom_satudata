$(document).ready(function(){
	// auto complete pencarian kelompok
    $tbmode = $('#progis_data_content').data('tbmode');
	$('#kelompok_ac').autocomplete({
		source: function(request,response){
			$.post(PUSDAHOST+'ajax/kelompok/listkelompok/1?keyword='+request.term,{'tbmode':$tbmode})
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

    // loadDaftarKelompok_instansi(67);

});

function loadChild(id,trigger,target){
    if (id == 0) {
        return false
    }
    $tbmode = $('#progis_data_content').data('tbmode');
    disableElement(trigger);
    disableElement(target);
     $.get(PUSDAHOST+'ajax/kelompok/listsubkelompok/'+id+'?',{'ajaxOn':1,'tbmode':$tbmode})
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
    $tbmode = $('#progis_data_content').data('tbmode');
    loadAnimationTo('admin_container');
    $.get(PUSDAHOST+'ajax/kelompok/tabelkelompok/'+id+'?',{'ajaxOn':1,'type':'kelompok','tbmode':$tbmode})
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
    $tbmode = $('#progis_data_content').data('tbmode');
    loadAnimationTo('admin_container');
    $.get(PUSDAHOST+'ajax/kelompok/tabelkelompokinstansi/'+id+'?',{'ajaxOn':1,'type':'instansi','tbmode':$tbmode})
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
    $tbmode = $('#progis_data_content').data('tbmode');
    loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','id':id,'ajaxOn':1,'tbmode':$tbmode})
        .success(function(data) { 
            var editContent = jQuery.parseJSON(data);
            if (errorCheck(editContent.message)) {
                $.notify({message: editContent.message}, {type: "warning"} ); 
            } else {
                // $.notify({message: editContent.message}, {type: "success"} ); 
                $('#modal_content').html(editContent.content);
                refreshForm();
                if ($tbmode == 'matrik') $('div#maplegendeditor').hide();
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
    $tbmode = $('#progis_data_content').data('tbmode');
    loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','idparent':idparent,'ajaxOn':1,'tbmode':$tbmode})
        .success(function(data) { 
            var editContent = jQuery.parseJSON(data);
            if (errorCheck(editContent.message)) {
                $.notify({message: editContent.message}, {type: "warning"} ); 
            } else {
                // $.notify({message: editContent.message}, {type: "success"} ); 
                $('#modal_content').html(editContent.content);
                refreshForm(idparent);
                if ($tbmode == 'matrik') $('div#maplegendeditor').hide();
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    

    removeAnimationFrom('commonModal');
}

function addKelompokInInstansi(idinstansi) {
    $tbmode = $('#progis_data_content').data('tbmode');
    loadAnimationTo('commonModal');

    $('#commonModal').modal({show: 'true'}); 

    $('#commonModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    // 
    $.get('#',{'cntmode':'form','idinstansi':idinstansi,'ajaxOn':1,'tbmode':$tbmode})
        .success(function(data) { 
            var editContent = jQuery.parseJSON(data);
            if (errorCheck(editContent.message)) {
                $.notify({message: editContent.message}, {type: "warning"} ); 
            } else {
                // $.notify({message: editContent.message}, {type: "success"} ); 
                $('#modal_content').html(editContent.content);
                refreshForm(idinstansi,'instansi');
                if ($tbmode == 'matrik') $('div#maplegendeditor').hide();
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
    $tbmode = $('#progis_data_content').data('tbmode');
    $.get('#',{'cntmode':'del','id':id,'ajaxOn':1,'tbmode':$tbmode})
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

function moveUp(id) {
    $tbmode = $('#progis_data_content').data('tbmode');
    $idinstansi = $('#select_skpdinstansi').val();
    $.get('#',{'cntmode':'up','id':id,'ajaxOn':1,'tbmode':$tbmode, 'idinstansi':$idinstansi})
        .success(function(data) { 
            var orderingContent = jQuery.parseJSON(data);
            if (errorCheck(orderingContent.message)) {
                $.notify({message: orderingContent.message}, {type: "warning"} ); 
             } else {
                $.notify({message: orderingContent.message}, {type: "success"} ); 
                $('.btn_load_kelompok_instansi').click();
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
         });    
}

function moveDown(id) {
    $tbmode = $('#progis_data_content').data('tbmode');
    $idinstansi = $('#select_skpdinstansi').val();
    $.get('#',{'cntmode':'down','id':id,'ajaxOn':1,'tbmode':$tbmode, 'idinstansi':$idinstansi})
        .success(function(data) { 
            var orderingContent = jQuery.parseJSON(data);
            if (errorCheck(orderingContent.message)) {
                $.notify({message: orderingContent.message}, {type: "warning"} ); 
             } else {
                $.notify({message: orderingContent.message}, {type: "success"} ); 
                $('.btn_load_kelompok_instansi').click();
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
         });    
}

function trigger_removeKelompok(id) {
/*
 * menggunakan modal, menyebabkan looping penghapusan
 */
        // $('#confirm-delete').modal({
        //     show: 'true'
        // }); 
        // $('#confirm-delete').on('show.bs.modal', function(e) {
        //     $('.btn-ok').on('click', function() {
        //         $('.btn-cancel').click();
        //         removeKelompok(id);
        //     });

        // });

    if(confirm('Anda yakin akan menghapus data ini?')){
    // 
        $tbmode = $('#progis_data_content').data('tbmode');
        $.get('#',{'cntmode':'del','id':id,'ajaxOn':1,'tbmode':$tbmode})
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

function refreshForm(idparent,submitmode){
/**
 * refsehs object event dari form edit/add
 * id parent digunakan untuk membaca tab parent
 */
    if (submitmode == 'instansi') {
        // on submit event
        $('#frm_kelompok').on('submit', function(e){
            e.preventDefault();
            $.post(PUSDAHOST+'giadmin/kelompok', $('#frm_kelompok').serialize()+'&ajaxOn=1'+'&tab=0')
                .success(function(data) { 
                   // do something here with response;
                   var $result = jQuery.parseJSON(data);    
                   if (errorCheck($result.message)) {
                        $.notify({message: $result.message}, {type: "warning"} ); 
                        //
                    } else {
                        $.notify({message: $result.message}, {type: "success"} ); 
                        // jika edit form, update content
                        if ($result.data.cntmode = 'ins') {
                            $('#table_kelompok tbody').append($result.row_content);
                        }
                        // $('#modal_content').html($result.content);
                    }   
                    // hide edit modal
                    $('#commonModal').modal('hide');
                 });
        });
    } else {
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
                            $t_row.find('td.publish').html($result.data.publish);
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
    }

 	// formula autocomplete
 	// memisahkan berdasarkan karakter perhitungan (*/+-);
 	// memisahkan idkelompok dengan chracter {digit:idkelp}
    // typeahed & tagsinput
    parseFormula(); 
    autoCompleteIndikator();

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
        $.get(PUSDAHOST+'ajax/kelompok/sub_urusan/'+$idinstansi+'?',{'ajaxOn':1})
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

    // custom legend
    $('tr.row_legend').find('input#label,input#batas_atas,input#batas_bawah,input#warna,button.btn_update,button.btn_cancel').hide();
    // colorpicker
    $('tr.row_legend_add>td>input#warna').colorpicker({
                    format: 'hex'
                }).on('changeColor.colorpicker', function(a, b, c) {
                     $('tr.row_legend_add>td>input#warna').css('background-color',$(this).colorpicker('getValue'));
                });

    legendButtonEvent();

    $('button.btn_add').on('click',function(){
        saveLegend();
    });

}

function legendButtonEvent() {
    $('button.btn_edit').off('click').on('click',function(){
        $rowlegend = $(this).parents('tr.row_legend');
        $rowlegend.find('td>input').show();
        $rowlegend.find('td>input#warna')
            .colorpicker({format: 'hex'})
            .off('changeColor.colorpicker')
            .on('changeColor.colorpicker', function(a, b, c) {
                    $color = $(this).colorpicker('getValue');
                    $rowlegend.find('td.warna').css('background-color',$color);
                });
        $rowlegend.find('td>span').hide();
        $rowlegend.find('td button.btn_delete,td button.btn_edit').hide();
        $rowlegend.find('td button.btn_update,td button.btn_cancel').show();
    });

    $('button.btn_cancel').off('click').on('click',function(){
        $rowlegend = $(this).parents('tr.row_legend');
        $rowlegend.find('td>input').hide();
        $rowlegend.find('td>input#warna').colorpicker({format: 'hex'});
        $rowlegend.find('td>span').show();
        $rowlegend.find('td button.btn_delete,td button.btn_edit').show();
        $rowlegend.find('td button.btn_update,td button.btn_cancel').hide();
    });

    $('button.btn_update').off('click').on('click',function(){
        idlegend = $(this).parents('tr.row_legend').data('id');
        updateLegend(idlegend);
    });

    $('button.btn_delete').off('click').on('click',function(){
        if (confirm('hapus legenda ?')) {
            idlegend = $(this).parents('tr.row_legend').data('id');
            removeLegend(idlegend);
        }
    });
}

function cekLegend(id) {
    if (id > 0) {
        $currlegend = $('tr#legend_'+id);
    } else {
        $currlegend = $('tr.row_legend_add');
    }

    label = $currlegend.find('input#label');
    batas_atas = $currlegend.find('input#batas_atas');
    batas_bawah = $currlegend.find('input#batas_bawah');
    warna = $currlegend.find('input#warna');

    if (label.val() == '' || batas_atas.val() == '' || batas_bawah.val() == '' || warna.val() == '') {
        $.notify({message: 'pastikan semua input diisi dan warna dipilih'}, {type: "warning",z_index: 99999} ); 
        return false;
    } else {
        return true;
    }
}

function updateLegend(id) {
    if (!cekLegend(id)) return false;
    $currlegend = $('tr#legend_'+id);
    $ldata = $('form#frm_legend_'+id).serializeArray();
    $ldata.push({name:'ajaxOn',value:1});
    console.log($ldata);
    $.post(PUSDAHOST+'ajax/kelompok/legendupdate/'+id+'?',$ldata)
        .success(function(data) { 
            var ULegend = jQuery.parseJSON(data);
            if (errorCheck(ULegend.message)) {
                $.notify({message: ULegend.message}, {type: "warning",z_index: 99999} ); 
             } else {
                $.notify({message: ULegend.message}, {type: "success",z_index: 99999} ); 
                $currlegend.find('td>input').hide();
                $currlegend.find('td>input#warna').colorpicker({format: 'hex'});
                $currlegend.find('td>span').each(function(i){
                        $(this).show();
                        $(this).html($(this).next('input').val());
                    });
                $currlegend.find('td button.btn_delete,td button.btn_edit').show();
                $currlegend.find('td button.btn_update,td button.btn_cancel').hide();
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
         });  
}

function saveLegend() {
    if (!cekLegend(0)) return false;
    $ldata = $('form#frm_add').serializeArray();
    $ldata.push({name:'ajaxOn',value:1});
    console.log($ldata);
    $.post(PUSDAHOST+'ajax/kelompok/legendsave/1?',$ldata)
        .success(function(data) { 
            var ULegend = jQuery.parseJSON(data);
            if (errorCheck(ULegend.message)) {
                $.notify({message: ULegend.message}, {type: "warning",z_index: 99999} ); 
             } else {
                $.notify({message: ULegend.message}, {type: "success",z_index: 99999} ); 
                $( ULegend.content ).insertBefore('tr.row_legend_add');
                legendButtonEvent();
                $('tr.row_legend_add').find('input#label,input#batas_atas,input#batas_bawah,input#warna').val('');
               $('tr#legend_'+ULegend.initid).find('button.btn_cancel').click();
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
         });  
}

function removeLegend(id) {
    $currlegend = $('tr#legend_'+id);
    $ldata = $('form#frm_legend_'+id).serializeArray();
    $ldata.push({name:'ajaxOn',value:1});
    console.log($ldata);
    $.post(PUSDAHOST+'ajax/kelompok/legendremove/'+id+'?',$ldata)
        .success(function(data) { 
            var ULegend = jQuery.parseJSON(data);
            if (errorCheck(ULegend.message)) {
                $.notify({message: ULegend.message}, {type: "warning",z_index: 99999} ); 
             } else {
                $.notify({message: ULegend.message}, {type: "success",z_index: 99999} ); 
                $currlegend.remove();
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
         });  
}

function parseFormula() {

    // $('#formula').tagsinput({
    //         confirmKeys: [13, 44],
    //         maxTags: 1,
    //         typeahead: {                  
    //             source: function(query) {
    //                 return $.get(PUSDAHOST+'ajax/kelompok/listkelompok_analisa/1',{'keyword':query});
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
    //                 return $.get(PUSDAHOST+'ajax/kelompok/listkelompok_analisa/1',{'keyword':query});
    //             }
    //       },
    //       freeInput: true
    // });

    // ada error pada jSON page

  //   $.get(PUSDAHOST+'ajax/kelompok/listkelompok_analisa/1')
  //                  .success(function(data) {
  //                      // result = data;
  //                       // console.log(data);
  //                       var $source_data =  jQuery.parseJSON(data);
  //                       $("#formula").tagsinput({
  //                             itemValue: 'value',
  // itemText: 'text',
  //                           typeahead: {
  //                               source: $source_data
  //                              }
  //                       });
  //                  });

    $('button.btn-match').attr('onclick','');
    $('button.btn-match').on('click',function(){
        var sdata = $(this).data('key');
        var is_indikator = $(this).hasClass('btn-indikator');
        var is_operator = $(this).hasClass('btn-operator');
        var is_operator_child = $(this).hasClass('btn-operator-child');

        prev_value = $('#formula').val();
        var last_chr = prev_value.slice(-1);

        if (is_indikator && prev_value != '') {
            if (last_chr != '+' && last_chr != '-' && last_chr != '*' && last_chr != '/') {
                alert ('harus ada operator matematika-nya (+ - * /)');
                return false;
            }
        } else if (is_operator) {
            if (last_chr == '+' || last_chr == '-' || last_chr == '*' || last_chr == '/' || prev_value == '') {
                alert ('operator matematika-nya (+ - * /) tidak boleh berganda / saling bersanding \n\r atau ditempatkan di awal');
                return false;
            }
        } 

        if (is_operator_child) {
            resetFormulas();

            var indikator_label = new Array();
            var indikator = new Array();
            xin = -1;
            $('.btn-indikator').each(function(){
                xin++;
                var idata = $(this).data('key');
                indikator_label[xin] = idata.label;
                indikator[xin] = idata.key;
            });

            var fformula = sdata.key;
            switch (fformula) {
                case 'sum' :
                    label   = indikator_label.join('+');
                    formula = indikator.join('+');
                break;
                case 'avg' :
                    num_idk = indikator.length;
                    label   = '('+indikator_label.join('+')+')/'+num_idk;
                    formula = '('+indikator.join('+')+')/'+num_idk;
                break;
                case 'mlt' :
                    label   = indikator_label.join('*');
                    formula = indikator.join('*');
                break;
                case 'a-b' :
                    label   = indikator_label[0]+'-'+indikator_label[1];
                    formula = indikator[0]+'-'+indikator[1];
                break;
                case 'b-a' :
                    label   = indikator_label[1]+'-'+indikator_label[0];
                    formula = indikator[1]+'-'+indikator[0];
                break;
                case 'a/b' :
                    label   = indikator_label[0]+'/'+indikator_label[1];
                    formula = indikator[0]+'/'+indikator[1];
                break;
                case 'b/a' :
                    label   = indikator_label[1]+'/'+indikator_label[0];
                    formula = indikator[1]+'/'+indikator[0];
                break;
                case 'a/b*100%' :
                    label   = indikator_label[0]+'/'+indikator_label[1]+'*100';
                    formula = indikator[0]+'/'+indikator[1]+'*100';
                break;
                case 'b/a*100%' :
                    label   = indikator_label[1]+'/'+indikator_label[0]+'*100';
                    formula = indikator[1]+'/'+indikator[0]+'*100';
                break;
            } 
            $('#formula').val(formula);
            $('#formula_label').val(label);
            $('.btn-match.btn-indikator').hide();
        } else {
            prev_value = $('#formula').val();
            prev_label = $('#formula_label').val();
            slabel = (sdata.label) ? sdata.label : sdata.key ;
            // set input
            $('#formula').val(prev_value+sdata.key);
            $('#formula_label').val(prev_label+slabel);

            // hide what must be hidden
            if (sdata.hide) $(this).hide();
        }
        // tambahVariabel();
    });

    $('button.btn-match-reset').attr('onclick','');
    $('button.btn-match-reset').on('click',function(){
        resetFormulas();
    });
}

function autoCompleteIndikator() {
    // auto complete other indikator / pencarian kelompok
    $tbmode = $('#progis_data_content').data('tbmode');
    $initial_idkelompok = $('#id').val();
    $('input#indikator_ac').autocomplete({
        source: function(request,response){
            // result expect child
            $.post(PUSDAHOST+'ajax/kelompok/listkelompok_formula/1?keyword='+request.term,{'initial_idkelompok':$initial_idkelompok,'tbmode':$tbmode})
            // $.post(PUSDAHOST+'ajax/kelompok/listkelompok/1?keyword='+request.term,{'initial_idkelompok':$initial_idkelompok,'tbmode':$tbmode})
                .success(function(data) { 
                    //do some advanced function here
                    // alert(data);
                    var $listkelompok = jQuery.parseJSON(data);
                    response($listkelompok);
                    // console.log($listkelompok);
                })
                .error(function(jqXHR, textStatus) {    
                    add_aai_notif ('ajax error');
                });
        },
        select: function(event, ui) {
              var iid = ui.item.id;
              var ilabel = ui.item.label;
              // $(this).parent('div').find('input[type=hidden]').val(iid);
              $btn_indikator ="<button form='none' type='button' class='btn btn-flat btn-warning btn-match btn-indikator'"+
              " data-key='{\"key\":\"{"+iid+"}\",\"label\":\"("+ilabel+")\",\"hide\":true,\"id\":\""+iid+"\",\"urai\":\""+ilabel+"\"}'>- : "+ilabel+"</button>";
            $('#formula_subindikator').append($btn_indikator);
            parseFormula();
            $('input#indikator_ac').val('')
            return false;
        },
        minLength: 1,
        appendTo: "#commonModal",
       delay : 900
    });
    // $('input#indikator_ac').autocomplete( "option", "appendTo", "#commonModal" );
}

function resetFormulas(){
    $('#formula').val('');
    $('#formula_label').val('');

    $('.btn-match.btn-indikator').show();
}

function changePublish(id,publish) {
/*
 * ubah status publikasi indikator
 */
    $tbmode = $('#progis_data_content').data('tbmode');
    $.post(PUSDAHOST+'ajax/kelompok/ubahpublikasi/'+id+'?',{'ajaxOn':1,'publish':publish,'tbmode':$tbmode})
        .success(function(data) { 
            var changedPublish = jQuery.parseJSON(data);
            if (errorCheck(changedPublish.message)) {
                $.notify({message: changedPublish.message}, {type: "warning"} ); 
             } else {
                $.notify({message: changedPublish.message}, {type: "success"} ); 
                $t_row = $('tr#kelompok_'+id);
                $t_row.find('td.publish').html(changedPublish.content);
            }
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
         });    
}



