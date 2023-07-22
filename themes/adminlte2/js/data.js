$(document).ready(function(){
		// loadChart('.$test_id.');
		  $('input').iCheck({
		    checkboxClass: 'icheckbox_square',
		    radioClass: 'iradio_square',
		    increaseArea: '-10%' // optional
		  });

    // autoCompleteSearch();
	fixContent();
});

function fixContent() {
    // lebar dokumen
    $(document).css('overflow-x','scroll');
    $('div.container').width('auto');
    $('.data-navigation').height('600');
    $('.data-content').css('min-height','600');
}

function autoCompleteSearch_Profil(){
    $.get(PUSDAHOST+'ajax/data/list_kelompok_profil/1', function (data) {
       $('#search_elemen').autocomplete({
            source: $.parseJSON(data),
            minLength: 1,
            select: function(event, ui) {
              var iid = ui.item.id;
              var kode_urusan = ui.item.kode_urusan;
              var kode_suburusan = ui.item.kode_suburusan;
              var type = 'profil';
              // call load data function
              loadContent_profil(iid,kode_urusan,kode_suburusan,type);
            },
            html:true,
            delay : 100
        })
            .data("ui-autocomplete")
            ._renderItem = function(ul, item) {
                var $a = $("<a></a>").text(item.label);
                highlightText(this.term, $a);
                return $("<li></li>").append($a).appendTo(ul);
        };
    });
}

function autoCompleteSearch_SUPD(){
    $.get(PUSDAHOST+'ajax/data/list_kelompok_supd/1', function (data) {
       $('#search_elemen').autocomplete({
            source: $.parseJSON(data),
            minLength: 1,
            select: function(event, ui) {
              var iid = ui.item.id;
              var idsupd = ui.item.idsupd;
              var type = 'supd';
              // call load data function
              loadContent_supd(iid,idsupd,type);
            },
            html:true,
            delay : 100
        })
            .data("ui-autocomplete")
            ._renderItem = function(ul, item) {
                var $a = $("<a></a>").text(item.label);
                highlightText(this.term, $a);
                return $("<li></li>").append($a).appendTo(ul);
        };
    });
}

function autoCompleteSearch_indikator(){
    $.get(PUSDAHOST+'ajax/data/list_kelompok_indikator/1', function (data) {
       $('#search_elemen').autocomplete({
            source: $.parseJSON(data),
            minLength: 1,
            select: function(event, ui) {
              var iid = ui.item.id;
              var type = 'indikator';
              // call load data function
              loadContent_indikator(iid,type);
            },
            html:true,
            delay : 100
        })
            .data("ui-autocomplete")
            ._renderItem = function(ul, item) {
                var $a = $("<a></a>").text(item.label);
                highlightText(this.term, $a);
                return $("<li></li>").append($a).appendTo(ul);
        };
    });
}

function element_Print(elm,elm_remove) {
	/* req :
	 * element yg harus dicetak
	 * element yg dihapuskan dari element : .no-print
	 */
    $print_title = $(document).data('print_title');
    $('#print_title').html($print_title);
    $print_content = $('#data_container').html();

    // $('div#modal_content').html($print_content);
    $('div#print_area').show();
    $('#print_content').html($print_content);
    $('#print_content').find('.no-print').remove();

    // delai print
    // setTimeout(function(){window.print();},1555);
    $('div.wrapper').hide();
}

function cancel_print(){
	$('div#print_area').hide();
	$('#print_content').html('');
    $('div.wrapper').show();
}

function loadContent_profil(id,kode_urusan,kode_suburusan,type){
	// content type : urusan, aspek, supd, penggunaan
    loadAnimationTo('data_container');

    $.get(PUSDAHOST+'ajax/data/loadcontent/'+id+'?',{'ajaxOn':1,'kode_urusan':kode_urusan,'kode_suburusan':kode_suburusan,'type':type})
        .success(function(data) { 
            var contentData = jQuery.parseJSON(data);
            if (contentData.header != null) {
                $('#data_header').html(contentData.header);
                $('#data_content').html(contentData.content);
                // $('div.ie_option').html(contentData.opsidata);
            }
            removeAnimationFrom('data_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('data_container');
        });    
}

function loadContent_supd(id,idsupd,type){
    // content type : urusan, aspek, supd, penggunaan
    loadAnimationTo('data_container');

    $.get(PUSDAHOST+'ajax/data/loadcontent/'+id+'?',{'ajaxOn':1,'idsupd':idsupd,'type':type})
        .success(function(data) { 
            var contentData = jQuery.parseJSON(data);
            if (contentData.header != null) {
                $('#data_header').html(contentData.header);
                $('#data_content').html(contentData.content);
                // $('div.ie_option').html(contentData.opsidata);
            }
            removeAnimationFrom('data_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('data_container');
        });    
}

function loadContent_indikator(id,type){
    // content type : urusan, aspek, supd, penggunaan
    loadAnimationTo('data_container');

    $.get(PUSDAHOST+'ajax/data/loadcontent/'+id+'?',{'ajaxOn':1,'type':type})
        .success(function(data) { 
            var contentData = jQuery.parseJSON(data);
            if (contentData.header != null) {
                $('#data_header').html(contentData.header);
                $('#data_content').html(contentData.content);
                // $('div.ie_option').html(contentData.opsidata);
            }
            removeAnimationFrom('data_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('data_container');
        });    
}

function loadContent_wilayahadat(id,type){
    // content type : urusan, aspek, supd, penggunaan
    loadAnimationTo('data_container');

    $.get(PUSDAHOST+'ajax/data/loadcontent/'+id+'?',{'ajaxOn':1,'type':type})
        .success(function(data) { 
            var contentData = jQuery.parseJSON(data);
            if (contentData.header != null) {
                $('#data_header').html(contentData.header);
                $('#data_content').html(contentData.content);
                // $('div.ie_option').html(contentData.opsidata);
            }
            removeAnimationFrom('data_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('data_container');
        });    
}

/*function loadContent_penggunaan(id,type){
    // content type : urusan, aspek, supd, penggunaan
    loadAnimationTo('data_container');

    $.get(PUSDAHOST+'ajax/data/loadcontent/'+id+'?',{'ajaxOn':1,'type':type})
        .success(function(data) { 
            var contentData = jQuery.parseJSON(data);
            if (contentData.header != null) {
                $('#data_header').html(contentData.header);
                $('#data_content').html(contentData.content);
                // $('div.ie_option').html(contentData.opsidata);
            }
            removeAnimationFrom('data_container');
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('data_container');
        });    
}*/

function loadContent_penggunaan(id,type){
    // content type : urusan, aspek, supd, penggunaan
    loadAnimationTo('data_container_'+id);

    ctl = $('#data_content_'+id).html().length;
    if (ctl > 200) {
        // jika sudah ada data / dah diload
        return false; // untuk mencegah pmanggilan ulang ajax jika tabel sudah diload
    } else {
        // jiak belum 
        $('#data_content_'+id).html('Memuat Data ... <i class="fa fa-refresh fa-spin"></i>'); // awal
    }

    $.get(PUSDAHOST+'ajax/data/loadcontent/'+id+'?',{'ajaxOn':1,'type':type})
        .success(function(data) { 
            var contentData = jQuery.parseJSON(data);
            if (contentData.header != null) {
                $('#data_header_'+id).html(contentData.header);
                $('#data_content_'+id).html(contentData.content);
                // $('div.ie_option').html(contentData.opsidata);
            }
            removeAnimationFrom('data_container_'+id);
        })
        .error(function(jqXHR, textStatus) {    
            add_aai_notif ('error','e');
            removeAnimationFrom('data_container_'+id);
        });    
}

function openChart(obid) {

    loadAnimationTo('chartModal');
    $('#modal_content').html('<div id="chart1" class="bg-gray" style="position:relative; width: 500px; height: 400px; margin: 0"></div>');
    $('#chartModal').modal({
        show: 'true'
    }); 

    $('#chartModal').on('hidden.bs.modal', function () {
        $('#modal_content').html('&nbsp;');
    });

    removeAnimationFrom('chartModal');
    
    var chartstrge = $('#strg_'+obid).data('chart');
    console.log(chartstrge.title);

    // {"series":[{"name":"buyut 1","data":[0,0,2,3,2,4,1]}],
    // "judul":"buyut 1",
    // "sumber":"CONTOH Instansi",
    // "satuan":"",
    // "kategori":[2010,2011,2012,2013,2014,2015,2016],
    // "type":"column",
    // "data":[0,0,2,3,2,4,1]}

    // chartstrge.series = [{'name':'as','data':[1,2,3,4,6]}];
    // loadChart(id);
    reloadChart(chartstrge.title,chartstrge.cat,chartstrge.sumber,chartstrge.y_title,chartstrge.series,'column');
   
}

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
            layout: 'vertical',
            align: 'center',
            verticalAlign: 'bottom',
            borderWidth: 0
        },
        series: series
    });
}

function highlightText(text, $node) {
                var searchText = $.trim(text).toLowerCase(), currentNode = $node.get(0).firstChild, matchIndex, newTextNode, newSpanNode;
                while ((matchIndex = currentNode.data.toLowerCase().indexOf(searchText)) >= 0) {
                    newTextNode = currentNode.splitText(matchIndex);
                    currentNode = newTextNode.splitText(searchText.length);
                    newSpanNode = document.createElement("span");
                    newSpanNode.className = "highlight";
                    currentNode.parentNode.insertBefore(newSpanNode, currentNode);
                    newSpanNode.appendChild(newTextNode);
                }
            }
