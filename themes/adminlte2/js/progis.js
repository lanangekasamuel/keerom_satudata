function reloadChart(title,cat,sumber,y_title,series,type,chart_area) {
    chart_area = (chart_area == undefined) ? "chart1" : chart_area ; 
    $('#'+chart_area).highcharts({
      chart: {
            type: type
        },
        title: {
            text: title,
            x: -20 //center
        },
        subtitle: {
            text: ['Sumber : ', sumber].join(''),
        },
        xAxis: {
            categories: cat
        },
        yAxis: {
            title: {
                text: String(y_title)
            }
            // plotLines: [{
            //     value: 0,
            //     width: 1,
            //     color: '#808080'
            // }]
        },
        plotOptions: {
            area: {
                pointStart: 1940,
                marker: {
                    enabled: false,
                    symbol: 'circle',
                    radius: 2,
                    states: {
                        hover: {
                            enabled: true
                        }
                    }
                }
            },
            series: {
                dataLabels: {
                    enabled: true
                },
                cursor: 'pointer',
                point: {
                    events: {
                        click: function () {
                            if (chart_area == 'chart1') { 
                                // alert('Category: ' + this.x + ', value: ' + this.y);
                                navigateChart(this.category);
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            valueSuffix: ' '+y_title
        },
        legend: {
            layout: 'horizontal',
            align: 'center',
            verticalAlign: 'bottom',
            borderWidth: 0
        },
        series: series
    });
}

function loadSubElement(id){
    loadAnimationTo('sub_element');

    $.get(PUSDAHOST+'ajax/progis/elemen/'+id,{'ajaxOn':1})
                .success(function(data) { 
                    $('#sub_element_content').html(data);
                    removeAnimationFrom('sub_element');
                })
                .error(function(jqXHR, textStatus) {    
                    add_aai_notif ('ajax error - autoCompleteGalleryTag(wallpaper.js)','e');
                    removeAnimationFrom('sub_element');
                });
}

function loadChart(id,page){
	loadAnimationTo('chart_option');
	loadAnimationTo('chart_content');
	window.localStorage.clear();

	if (page == undefined) page = 'sipd';

	var tahun_chart = $('.tahun_chart:checked').serialize();
	var href = [PUSDAHOST,'ajax/progis/chart/',id,'?',tahun_chart].join('');

	$.get(href,{ 'ajaxOn':1, 'page':page})
	.success(function(data) {
		var $chart = jQuery.parseJSON(data);

		/* [anovedit][workaround] handle supaya "publish" bisa bekerja */
		if (!ADMINPANEL) for (var i = 0; i < $chart.series.length; i++) if (!$chart.series[i].is_publish) $chart.series.splice(i,1);

		reloadChart($chart.judul,$chart.kategori,$chart.sumber,$chart.satuan,$chart.series,$chart.type);

		// load kabupaten data & chart
		$chart_kab = $chart.chart_kab;

		if ($chart_kab.havedata == 1) {
			reloadChart($chart_kab.judul,$chart_kab.kategori,$chart_kab.sumber,$chart_kab.satuan,$chart_kab.series,$chart_kab.type,'chart_kabupaten');
			$('.chartnav').show(); //show navigation

			// Put the object into storage
			window.localStorage.clear();
			localStorage.setItem('chart_kab', JSON.stringify($chart_kab));
			localStorage.setItem('current_year', JSON.stringify($chart_kab.current_year));

			navigateChart($chart_kab.current_year);

		} else {
			$('#chart_kabupaten').html($chart_kab.message);
			$('.chartnav').hide();
		}

		removeAnimationFrom('chart_option');
		removeAnimationFrom('chart_content');
	})
	.error(function(jqXHR, textStatus) {
		add_aai_notif ('','e');
		removeAnimationFrom('chart_option');
		removeAnimationFrom('chart_content');
	});

	$('.btn-refresh-chart').attr('onclick','loadChart('+id+');');
}

function navigateChart(yar) {
    // var $chart_kab, $datakabupaten;

    $chartkab = JSON.parse(localStorage.getItem('chart_kab'));
    current_year = JSON.parse(localStorage.getItem('current_year'));
    ntahun = parseInt(current_year);
    // console.log(ntahun);

    if (yar == 'next') {tahun = ntahun+1;}
    else if (yar == 'previous') {tahun = ntahun-1;}
    else {tahun = yar;}

    localStorage.setItem('current_year', JSON.stringify(tahun));
    // console.log(tahun);
    tahun = tahun.toString();

    // console.log($chartkab.judul);

    $datakab        = $chartkab.data[tahun];
    $datakategori   = $chartkab.datakategori[tahun];

    $chartkab.series[0]['data'] = $datakab;

    var chart = $('#chart_kabupaten').highcharts();
    // chart.setOptions(series: {
    //                     cursor: 'pointer',
    //                     point: {
    //                         events: {
    //                         click: function () {alert('Category: ' + this.category + ', value: ' + this.y);}
    //                         }
    //                     }
    //         });
        chart.setTitle({text : $chartkab.judul+' ('+tahun+')'});
        chart.xAxis[0].setCategories($datakategori);
        chart.series[0].setData($datakab, true);

    // reloadChart($chart_kab.judul,$chart_kab.kategori,$chart_kab.sumber,$chart_kab.satuan,$chart_kab.series,$chart_kab.type,'chart_kabupaten');
}

function openChart(id,page){

    loadAnimationTo('chartModal');
    $('#modal_content').html('<div id="chart1" class="bg-gray" style="min-width: 400px; height: 400px; margin: 0 auto"></div>');
    $('#chartModal').modal({
        show: 'true'
    }); 

    $('#chartModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    loadChart(id,page);
    removeAnimationFrom('chartModal');
}

function checkDataTableReady() {
    $isi = $('table#table_kelompok_input').find('tbody').html();
    // alert($isi);
    if ($isi != undefined) {
        if ($isi.length > 10) {return true;}
        else {return false;}
    }
}

function reInitializeDataTable() {
    // var $ma;
    // if ($ma != undefined) $ma.destroy();

    // $('table#table_kelompok_input').dataTable().fnDestroy();

    if (checkDataTableReady()) {
    // if (!$ma) {
        $ma = $('table#table_kelompok_input').DataTable({
                "bPaginate": false, 
                // "bFilter": false, 
                "sScrollY": "400", 
                "sScrollX": "100%", 
                "sScrollXInner": "100%", 
                "bScrollCollapse": true,
                search: {caseInsensitive: true },
                fixedHeader: true
                // aoColumnDefs: [
                //   // { "sClass": "text-right text-nowrap", "aTargets": [ 5 ] },
                //   // { "sClass": "text-center text-nowrap", "aTargets": [ 0,3,4,5,6 ] },
                //   // { "sClass": "text-center", "aTargets": [ 0 ] },
                //   // { bSortable: false, "aTargets": [ 1 ] }
                // ]
                });
    }
    // }
}

function cellNavigation(){
/** Begin of EXCEL LIKE TYPING
 * konsepnya menggunakan arrow atau event keyboard khusus 
 * untuk bergeser ke input selanjutnya
 * q : bergeser ke empat arah (f), cond jika object tidak ditemukan (f)
 * q : jika object input telah habis dalam satu baris, lanjut kebaris selanjutnya kolom pertama (f)
 * q : jika object input telah habis dalam satu colom, lanjut mulai dari kolom selanjutnya baris pertama (f)
 * q : select all inside on focus 
 */

    // console.log('dsadsdadsdadsadsadsada');

    // $ma = $('table#table_kelompok_input').DataTable({
    //     "bPaginate": false, 
    //     // "bFilter": false, 
    //     "sScrollY": "400", 
    //     "sScrollX": "100%", 
    //     "sScrollXInner": "100%", 
    //     "bScrollCollapse": true,
    //     search: {caseInsensitive: true },
    //     fixedHeader: true,
    //     aoColumnDefs: [
    //       // { "sClass": "text-right text-nowrap", "aTargets": [ 5 ] },
    //       { "sClass": "text-center text-nowrap", "aTargets": [ 0,3,4,5,6 ] },
    //       // { "sClass": "text-center", "aTargets": [ 0 ] },
    //       { bSortable: false, "aTargets": [ 1 ] }
    //     ]
    //     // "columnDefs": [
    //     //   { "orderable": false, "targets": 7 }, //disabling 4th index column
    //     // ]
    //     });

    $('.detail_input').on('focus',function(){ $(this).select(); });
    $('.detail_input').keydown(function (e) {

        // settings
        var input_class = '.detail_input';
        var row_class   = '.rows_data';
        var enter_next  = 'row'; //opsi : row/colom (aksi setelah tombol enter), default excel = row
        // common using variable
        $this_row       = $(this).parents('tr');
        $rid            = $this_row.data('row-id');
        $this_col       = $(this).data('tahun');
        $last_row       = $(row_class+':last-child');
        $last_row_id    = $last_row.data('row-id');

        e = e || window.event;

        if (e.keyCode == '38') {
            // up arrow
            // console.log('up');
            $rid--;
            $upper_input = $('#rows_'+$rid).find(input_class+'.'+$this_col);
            if ($upper_input.length > 0) { //cek object existence
                $(this).blur();   
                $upper_input.focus();
            }
            return false;
        }
        else if (e.keyCode == '40' || (e.keyCode == '13' && enter_next == 'row')) {
            // down arrow || enter
            // console.log('down');
            // next row in same coloum
            $rid++;
            $lower_input = $('#rows_'+$rid).find(input_class+'.'+$this_col);
            // 1st row but in next coloum
            $rid=1; $this_col++;
            $next_col_1st_row = $('#rows_'+$rid).find(input_class+'.'+$this_col);
            if ($lower_input.length > 0) { //cek object existence
                $(this).blur();   
                $lower_input.focus();
            } else if ($next_col_1st_row.length > 0) { //cek object existence
                $(this).blur();   
                $next_col_1st_row.focus();
            }
            return false;
        }
        else if (e.keyCode == '37') {
            // left arrow
            // console.log('left');
            // previous coloum input
            $this_col--;
            $prev_input = $('#rows_'+$rid).find(input_class+'.'+$this_col);
            // previews row and last colum input
            $rid--;
            $prev_row_input = $('#rows_'+$rid).find(input_class+':last');
            if ($prev_input.length > 0) { //cek object existence
                $(this).blur();   
                $prev_input.focus();
            } else if ($prev_row_input.length > 0) { //cek object existence
                $(this).blur();   
                $prev_row_input.focus();
            }
            return false;
        }
        else if (e.keyCode == '39'|| (e.keyCode == '13' && enter_next == 'colom')) {
            // right arrow || enter
            // console.log('right');
            // next coloumn input
            $this_col++;
            $next_input = $('#rows_'+$rid).find(input_class+'.'+$this_col);
            // next row 1st coloumn
            $rid++;
            $next_row_input = $('#rows_'+$rid).find(input_class+':first');
            if ($next_input.length > 0) { //cek object existence
                $(this).blur();   
                $next_input.focus();
            } else if ($next_row_input.length > 0) { //cek object existence
                $(this).blur();   
                $next_row_input.focus();
            }
            return false;
        }
        else if (e.keyCode == '27') {
            // escape
            $(this).blur();   
            return false;
        }
    });
/*End of EXCEL LIKE TYPING*/
}

function removeMaskFromNilai(nilai) {
   /*
    | menghapus titik dan mengganti koma di input nilai indikator
    */ 
    console.log(nilai);
    new_nilai = nilai.replace(/\./g,'').replace(',','.');
    console.log(new_nilai);
    return new_nilai;
}

function updateData(id) {

    idkelompok = $('#'+id).data('idkelompok');
    tahun = $('#'+id).data('tahun');
    nilai = $('#'+id).val();

    nilai = removeMaskFromNilai(nilai);
    // return false;

    disableElement(id);


    $.post(PUSDAHOST+'ajax/progis/updatedetail/'+idkelompok,{'idkelompok':idkelompok,'tahun':tahun,'nilai':nilai,'ajaxOn':1})
                .success(function(data) { 
                    enableElement(id);   
                    var $update = jQuery.parseJSON(data);
                    if (errorCheck($update.message)) {
                        $.notify({message: $update.message}, {type: "warning"} ); 
                    } else {
                        $.notify({message: $update.message}, {type: "success"} ); 
                        // set ulang label parent
                        $.each($update.parent,function(index,data) {
                             $('#trparent_'+index).find('td.nilai_formula.'+$update.tahun).html(data);
                        }) ;                     
                    }        
                })
                .error(function(jqXHR, textStatus) {    
                    add_aai_notif ('','e');
                    enableElement(id);  
                });
}

function loadChild(id,trigger,target){
    if (id == 0) {
        return false
    }
    disableElement(trigger);
    disableElement(target);
     $.get(PUSDAHOST+'ajax/progis/jenis_urusan/'+id+'?',{'ajaxOn':1})
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

function destryDataTable(){
    if(checkDataTableReady()){
        $('table#table_kelompok_input').dataTable().fnDestroy();
    }
}

function reloadTableDetail(id){
    loadAnimationTo('admin_container');
    var tahun_awal = $('#tahun_awal').val();
    var tahun_akhir = $('#tahun_akhir').val();

    destryDataTable();

    $.get(PUSDAHOST+'ajax/progis/loadtable/'+id+'?',{'tahun_awal':tahun_awal,'tahun_akhir':tahun_akhir,'ajaxOn':1})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
               $('#table_kelompok_input').find('thead').html(tableData.header);
                $('#table_kelompok_input').find('tbody').html(tableData.body);
                $('div.ie_option').html(tableData.opsidata);
                //reload cellNav
                cellNavigation();
                setMaskedInput();
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    
}

function reloadTableDetail_instansi(id){
    loadAnimationTo('admin_container');
    var tahun_awal = $('#tahun_awal').val();
    var tahun_akhir = $('#tahun_akhir').val();

    destryDataTable();

    $.get(PUSDAHOST+'ajax/progis/loadtableskpd/'+id+'?',{'tahun_awal':tahun_awal,'tahun_akhir':tahun_akhir,'ajaxOn':1})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
                $('#table_kelompok_input').find('thead').html(tableData.header);
                $('#table_kelompok_input').find('tbody').html(tableData.body);
                $('div.ie_option').html(tableData.opsidata);
                //reload cellNav
                cellNavigation();
                setMaskedInput();
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    
}

function excelExport(id,jenis_data,tahun_awal,tahun_akhir){
/*
 * exporting displayed table data to excell format
 * req : 
 * - type data (skpd,instansi,kelompok), 
 * - id (type=kelompok>idkelompok, type=skpd.instnsi>idskpd/instansi), tahun
 */
    if (jenis_data == undefined) jenis_data = 'kelompok';
    $.notify({message: 'silakan menunggu!, file akan otomatis di download oleh browser'}, {type: "info"} );

    $link = PUSDAHOST+'ajax/progis/export/'+id+'?type='+jenis_data+'&tahun_awal='+tahun_awal+'&tahun_akhir='+tahun_akhir;
    // console.log($link);
    location.href = $link;
}

function excelImport(id,jenis_data,tahun_awal,tahun_akhir){
/*
 * 
 */
    if (jenis_data == undefined) jenis_data = 'kelompok';

    $link = PUSDAHOST+'giadmin/progis?cntmode=import&id='+id+'&type='+jenis_data+'&tahun_awal='+tahun_awal+'&tahun_akhir='+tahun_akhir;
    // console.log($link);
    location.href = $link;
}

function cetakTabelDetail() {
    /* req :
     * element yg harus dicetak
     * element yg dihapuskan dari element : .no-print
     */

    // $('#print_content').find('.no-print').remove();
    destryDataTable();
    // return false;
    $print_content = $('#progis_data_content').html();

    // $('div#modal_content').html($print_content);
    $('div#print_area').show();
    $('#print_content').html($print_content);

    // $('table#table_kelompok_input').dataTable().fnDestroy();


    // delai print
    // setTimeout(function(){window.print();},1555);
    $('div.wrapper').hide();
}

function cancel_print(){
    $('div#print_area').hide();
    reInitializeDataTable();
    $('#print_content').html('');
    $('div.wrapper').show();
}

$(document).ready(function() {

    /** DATA FILTER SELECTION
     *
     */
     $('.sub_kelompok').hide();
     // loadChild(719,'select_subkelompok1','select_subkelompok2');

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

    $('.btn_load_data').on('click',function(){
        //trigger load ajax
        near_select = $(this).parents('div.input-group').find('select');
        $_id = near_select.attr('id');
        $load_id = near_select.val();
        if ($load_id == 0) {
            alert('pilih item terlebih dahulu');
        } else {
            reloadTableDetail($load_id);
        }
    });

    $('.btn_load_data_skpd_instansi').on('click',function(){
        //trigger load ajax
        near_select = $(this).parents('div.input-group').find('select');
        $_id = near_select.attr('id');
        $load_id = near_select.val();

        if ($load_id == 0) {
            alert('pilih item terlebih dahulu');
        } else {
            reloadTableDetail_instansi($load_id);
        }
    });

    $('.btn_load_by_tahun').on('click',function(){
        // trigger load ajax
        // near_select = $(this).parents('div.input-group').find('select');
        // $_id = near_select.attr('id');
        $load_id = $('#select_skpdinstansi').val();

        if ($load_id == 0) {
            alert('pilih item terlebih dahulu');
        } else {
            reloadTableDetail_instansi($load_id);
        }
    });

    // console.log('dsadsdadsdadsadsadsada');
    destryDataTable();
    cellNavigation();
    setMaskedInput();

    $.notifyDefaults({
        // type: 'warning',
        allow_dismiss: true,
        placement: {
                from: "top",
                align: "left"
        }
    });
     
      $.get( PUSDAHOST+'files/kelompok-complete.json', function (data) 
      {
        $('#elemen').autocomplete(
       {
            source: data,
            minLength: 1,
            select: function(event, ui) {
              var iid = ui.item.id;
              var ilabel = ui.item.label;

              loadChart(iid);

             },
       delay : 900
       });
    });

      // chart kabupaten navigation
    $('.chartnav-l').on('click',function(){navigateChart('previous');});
    $('.chartnav-r').on('click',function(){navigateChart('next');});

});

function setMaskedInput() {
    $('input.detail_input').inputmask("numeric", {
        radixPoint: ",",
        groupSeparator: ".",
        digits: 2,
        autoGroup: true,
        prefix: '', //Space after $, this will not truncate the first character.
        rightAlign: false,
        oncleared: function () { self.Value(''); }
    });
}
