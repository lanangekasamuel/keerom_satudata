

function load3AxisChart(title,sumber,cat,series1,series2,series3) {
    //3 y-axis chart
    
    $('#chart2').highcharts({
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: title
        },
        subtitle: {
            text: ('Analisa oleh : Pusdalisbang ' + window.ENV_DEFINED_VARS.SITE_PEMDA) //+sumber
        },
        xAxis: [{
            categories: cat,
            crosshair: true
        }],
        yAxis: [{ // Primary yAxis
            title: {
                text: series3.satuan,
                style: {
                    color: Highcharts.getOptions().colors[2]
                }
            },
            labels: {
                format: '{value}', //
                style: {
                    color: Highcharts.getOptions().colors[2]
                }
            },
            opposite: true

        }, { // Secondary yAxis
            gridLineWidth: 0,
            title: {
                text: series1.satuan,
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            labels: {
                format: '{value}', // mm
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            }

        }, { // Tertiary yAxis
            gridLineWidth: 0,
            title: {
                text: series2.satuan,
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            labels: {
                format: '{value} ', // mb
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            opposite: true
        }],
        tooltip: {
            shared: true
        },
        legend: {
            backgroundColor: '#FCFFC5',
            borderColor: '#C98657',
            borderWidth: 1
            // verticalAlign: 'top'
            // layout: 'vertical',
            // align: 'left',
            // x: 80,
            // y: 55,
            // floating: true,
            // backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        series: [{
            name: series1.name,
            type: 'column',
            yAxis: 1,
            data: series1.data,
            tooltip: {
                valueSuffix: ' '+series1.satuan
            }

        }, {
            name: series2.name,
            type: 'spline',
            yAxis: 2,
            data: series2.data,
            marker: {
                enabled: false
            },
            dashStyle: 'shortdot',
            tooltip: {
                valueSuffix: ' '+series2.satuan
            }

        }, {
            name: series3.name,
            type: 'spline',
            yAxis: 0,
            data: series3.data,
            tooltip: {
                valueSuffix: ' '+series3.satuan
            }
        }]
    });
   $('.highcharts-legend').find('rect:first-child').attr('fill-opacity','0.7');
}

function load2AxisChart(title,sumber,cat,series1,series2) {
    //2 y-axis chart
    $('#chart2').highcharts({
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: title
        },
        subtitle: {
            text: ('Analisa oleh : Pusdalisbang ' + window.ENV_DEFINED_VARS.SITE_PEMDA) //+sumber
        },
        xAxis: [{
            categories: cat,
            crosshair: true
        }],
        yAxis: [{ // Primary yAxis
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            title: {
                text: series2.satuan,
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            }
        }, { // Secondary yAxis
            title: {
                text: series1.satuan,
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            opposite: true
        }],
        tooltip: {
            shared: true
        },
        legend: {
            backgroundColor: '#FCFFC5',
            borderColor: '#C98657',
            borderWidth: 1
            // layout: 'vertical',
            // align: 'left',
            // x: 120,
            // verticalAlign: 'top',
            // y: 100,
            // floating: true,
            // backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        series: [{
            name: series1.name,
            type: 'column',
            yAxis: 1,
            data: series1.data,
            tooltip: {
                valueSuffix: ' '+series1.satuan
            }

        }, {
            name: series2.name,
            type: 'spline',
            data: series2.data,
            tooltip: {
                valueSuffix: ' '+series2.satuan
            }
        }]
    });
   $('.highcharts-legend').find('rect:first-child').attr('fill-opacity','0.7');
}

function loadLegend(chartdata) {
   var serieses = Array('series1','series2','series3');
   var boxes = Array('sb1','sb2','sb3');

   // reset view
   $('.chart_legend').hide();

   numarr = 0;
   for(var skey in serieses) {
        series = serieses[skey];
        box = boxes[skey];
        if(chartdata[series] != null){
            numarr++;

            $('.'+series+' .smax .series-value-num').html(chartdata[series].maxdata);   
            $('.'+series+' .smax span').html('Tahun : '+chartdata[series].maxtahun);  

            $('.'+series+' .smin .series-value-num').html(chartdata[series].mindata);   
            $('.'+series+' .smin span').html('Tahun : '+chartdata[series].mintahun);    
           
            $('.'+series+' .savg .series-value-num').html(chartdata[series].avgdata);
            $('.'+series+' .series-author>span').html(chartdata[series].source);
            $('.'+series+' .series-info>span').html('Last update : '+chartdata[series].lastupdate);

            $('.'+series+' h1').html(chartdata[series].name);
            $('.'+series+' h3').html('('+chartdata[series].satuan+')');
                    
            $('.'+box).show();
        }     
   }

   // $('.chart_legend').hide();

   // detect windows width
   winwidth = $(window).width();

   if (winwidth > 480) {
        if(numarr == 3){
            // $leg_height = $('.chart_legend.sb1').height()-32;
            // $('#chart2').find('div.highcharts-container').height($leg_height*3);
            $('.chart_legend').addClass('col-sm-4').removeClass('col-sm-6');
            $('.series-info h1').css('font-size','14px');

        } else if(numarr == 2){
            //  $leg_height = $('.chart_legend.sb1').height()-32;
            // $('#chart2').find('div.highcharts-container').height($leg_height*2);
            $('.chart_legend').addClass('col-sm-6').removeClass('col-sm-4');
            $('.series-info h1').css('font-size','24px');
        }
   } else {
         // $('#chart2').find('div.highcharts-container').height(winwidth+'px');
   }

}    

function checkYear(){
    $('.btn_tahun_analisa').on('click',function(){
        $('.btn-refresh-analisa-chart').click();
    });
}

function loadAnalisaChart(id){
    
    loadAnimationTo('chart_option');
    loadAnimationTo('chart_content');

    var tahun_chart = $('.tahun_analisa:checked').serialize();
    $.get(PUSDAHOST+'ajax/analisa/chart_analisa/'+id+'?'+tahun_chart,{'ajaxOn':1})
                .success(function(data) { 
                    var $chart = jQuery.parseJSON(data);
                    removeAnimationFrom('chart_option');
                    removeAnimationFrom('chart_content');
                    console.log($chart);

                    if ($chart.series3 == null) {
                        load2AxisChart($chart.title,$chart.sumber,$chart.kategori,$chart.series1,$chart.series2);
                    } else {
                        load3AxisChart($chart.title,$chart.sumber,$chart.kategori,$chart.series1,$chart.series2,$chart.series3);
                    }

                    $('#table_content div.box-body').html($chart.table);
                    $('#table_content h4.judul_analisa span').html($chart.title);

                    //alert()
                    if($('.series1').length){
                        loadLegend($chart);
                    }
                })
                .error(function(jqXHR, textStatus) {    
                    removeAnimationFrom('chart_option');
                    removeAnimationFrom('chart_content');
                    add_aai_notif ('ajax error - autoCompleteGalleryTag(wallpaper.js)','e');
                });

    $('.btn-refresh-analisa-chart').attr('onclick','loadAnalisaChart(\''+id+'\');');
}

function autoCompleteKelompok(){
// $.get( PUSDAHOST+'files/kelompok-complete.json', function (data) 
//       {});
//         // elements = data;
//          // response(results.slice(0, 10));

       $('.kelompok').autocomplete(
       {
            source: function(request,response){
            $.post(PUSDAHOST+'ajax/analisa/listkelompok_analisa/1?keyword='+request.term)
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
              // alert(iid);
              $(this).parent('.kparent').find('.kelp').val(iid);
              // $('#'+id).data('anime_id',iid);
            },
            delay : 900
       });
     
}

function changeKelompok(){
       $('.kelompok').on('change',function()
       {
            $val = $(this).val();
            if ($val.trim() == '') {
                $(this).parent('.kparent').find('.kelp').val('');
            }
       });
}

function clearKelompok(){
       $('.clear_kelompok').on('click',function()
       {
            $(this).parent('.kparent').find('.kelp').val('');
            $(this).parent('.kparent').find('.kelompok').val('');
       });
}

$(document).ready(function(){
    $('#tbl_analisa').DataTable({
                      "columnDefs": [
                        { "orderable": false, "targets": 3 }, //disabling 4th index column
                      ]
                    } 
                    );
});