function reloadChart(title,cat,sumber,y_title,series,type) {
    $('#chart1').highcharts({
      chart: {
            type: type
        },
        title: {
            text: title,
            x: -20 //center
        },
        subtitle: {
            text:'sumber : '+sumber,
        },
        xAxis: {
            categories: cat
        },
        yAxis: {
            title: {
                text: ''+y_title
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
            }
        },
        tooltip: {
            valueSuffix: ' '+y_title
        },
        legend: {
            // layout: 'vertical',
            // align: 'right',
            // verticalAlign: 'middle',
            // borderWidth: 0
            backgroundColor: '#FCFFC5',
            borderColor: '#C98657',
            borderWidth: 1
        },
        series: series
    });
}

function loadSubElement(id){
    loadAnimationTo('sub_element');

    $.get(PUSDAHOST+'ajax/kabupaten/elemen/'+id,{'ajaxOn':1})
                .success(function(data) { 
                    $('#sub_element_content').html(data);
                    removeAnimationFrom('sub_element');
                })
                .error(function(jqXHR, textStatus) {    
                    add_aai_notif ('ajax error - autoCompleteGalleryTag(wallpaper.js)','e');
                    removeAnimationFrom('sub_element');
                });
}

function loadChart(id,idkabupaten){
    loadAnimationTo('chart_option');
    loadAnimationTo('chart_content');

    var tahun_chart = $('.tahun_chart:checked').serialize();

    // alert(tahun_chart);
    $.get(PUSDAHOST+'ajax/kabupaten/chart/'+id+'?'+tahun_chart,{'ajaxOn':1,'idkabupaten':idkabupaten})
                .success(function(data) { 
                    var $chart = jQuery.parseJSON(data);
                    reloadChart($chart.judul,$chart.kategori,$chart.sumber,$chart.satuan,$chart.series,$chart.type);
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

function openChart(id,idkabupaten){

    loadAnimationTo('chartModal');
    $('#modal_content').html('<div id="chart1" class="bg-gray" style="min-width: 400px; height: 400px; margin: 0 auto"></div>');
    $('#chartModal').modal({
        show: 'true'
    }); 

    $('#chartModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    loadChart(id,idkabupaten);
    removeAnimationFrom('chartModal');
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

function updateData(id) {

    // console.log(id);

    idkelompok = $('#'+id).data('idkelompok');
    tahun = $('#'+id).data('tahun');
    nilai = $('#'+id).val();
    kodekabupaten = $('#'+id).data('kodekabupaten');

    disableElement(id);

    $.post(PUSDAHOST+'ajax/kabupaten/updatedetail/'+idkelompok,{'kodekabupaten':kodekabupaten,'idkelompok':idkelompok,'tahun':tahun,'nilai':nilai,'ajaxOn':1})
                .success(function(data) { 
                    enableElement(id);   
                    var $update = jQuery.parseJSON(data);
                    if (errorCheck($update.message)) {
                        $.notify({message: $update.message}, {type: "warning"} ); 
                        resetValue(id);
                    } else {
                        $.notify({message: $update.message}, {type: "success"} ); 
                        $('#'+id).data('default',nilai);
                    }        
                })
                .error(function(jqXHR, textStatus) {    
                    add_aai_notif ('','e');
                    enableElement(id);  
                    resetValue(id);
                });
}

function resetValue(id){
    /*
     * reset value dari input ke nilai awal
     * digunakan jika gagal melakukan update
     */
    $res_value = $('#'+id).data('default');
    $('#'+id).val($res_value);
}

function loadChild(id,trigger,target){
    if (id == 0) {
        return false
    }
    disableElement(trigger);
    disableElement(target);
     $.get(PUSDAHOST+'ajax/kabupaten/jenis_urusan/'+id+'?',{'ajaxOn':1})
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

function reloadTableDetail(id){
    loadAnimationTo('admin_container');
    var tahun_awal = $('#tahun_awal').val();
    var tahun_akhir = $('#tahun_akhir').val();

    $.get(PUSDAHOST+'ajax/kabupaten/loadtable/'+id+'?',{'tahun_awal':tahun_awal,'tahun_akhir':tahun_akhir,'ajaxOn':1})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
                $('#table_kelompok_input').find('thead').html(tableData.header);
                $('#table_kelompok_input').find('tbody').html(tableData.body);
                $('div.ie_option').html(tableData.opsidata);
                $('span.table-title').html(tableData.title);
                //reload cellNav
                cellNavigation();
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    
}

function reloadTableDetail_skpd(id){
    loadAnimationTo('admin_container');
    var tahun_awal = $('#tahun_awal').val();
    var tahun_akhir = $('#tahun_akhir').val();

    $.get(PUSDAHOST+'ajax/kabupaten/loadtableskpd/'+id+'?',{'tahun_awal':tahun_awal,'tahun_akhir':tahun_akhir,'ajaxOn':1})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
                $('#table_kelompok_input').find('thead').html(tableData.header);
                $('#table_kelompok_input').find('tbody').html(tableData.body);
                $('div.ie_option').html(tableData.opsidata);
                $('span.table-title').html(tableData.title);
                //reload cellNav
                cellNavigation();
            }
            removeAnimationFrom('admin_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('admin_container');
        });    
}

function reloadTableDetail_kabupaten(id){
    loadAnimationTo('admin_container');
    var tahun_awal = $('#tahun_awal').val();
    var tahun_akhir = $('#tahun_akhir').val();

    $.get(PUSDAHOST+'ajax/kabupaten/loadtablekabupaten/'+id+'?',{'tahun_awal':tahun_awal,'tahun_akhir':tahun_akhir,'ajaxOn':1})
        .success(function(data) { 
            var tableData = jQuery.parseJSON(data);
            if (tableData.header != null) {
                $('#table_kelompok_input').find('thead').html(tableData.header);
                $('#table_kelompok_input').find('tbody').html(tableData.body);
                $('div.ie_option').html(tableData.opsidata);
                $('span.table-title').html(tableData.title);
                //reload cellNav
                cellNavigation();
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

    $link = PUSDAHOST+'ajax/kabupaten/export/'+id+'?type='+jenis_data+'&tahun_awal='+tahun_awal+'&tahun_akhir='+tahun_akhir;
    console.log($link);
    location.href = $link;
}


function excelImport(id,jenis_data,tahun_awal,tahun_akhir){
/*
 * 
 */
    if (jenis_data == undefined) jenis_data = 'kelompok';

    $link = PUSDAHOST+'giadmin/kabupaten?cntmode=import&id='+id+'&type='+jenis_data+'&tahun_awal='+tahun_awal+'&tahun_akhir='+tahun_akhir;
    console.log($link);
    location.href = $link;
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
            reloadTableDetail_skpd($load_id);
        }
    });

    $('.btn_load_data_kabupaten').on('click',function(){
        //trigger load ajax
        near_select = $(this).parents('div.input-group').find('select');
        $_id = near_select.attr('id');
        $load_id = near_select.val();

        if ($load_id == 0) {
            alert('pilih item terlebih dahulu');
        } else {
            reloadTableDetail_kabupaten($load_id);
        }
    });

    cellNavigation();

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
});