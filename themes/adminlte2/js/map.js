
$('div.container').width('auto');

function haltFile(id) {

	// cek ukuran file
	// id = $('#jenis_analisa').val();
	$file_size = $('#jenis_analisa').find('option:selected').data('filesize');
	// console.log(id+$file_size);
	$max_size = 5*1024*1024; //50MB

	if ($file_size  > $max_size) {
		$('#confirm-delete').modal({
	        show: 'true'
	    }); 
		$('#confirm-delete').on('show.bs.modal', function(e) {
		    $('.btn-ok').on('click', function(ed) {
				$('.btn-cancel').click();
				loadProvinsiMap(id);
		    });

		});
		$('div.modal-backdrop').hide();
	}
	else {
		loadProvinsiMap(id);
	}
}

function transform(extent) {
    return ol.proj.transformExtent(extent, 'EPSG:4326', 'EPSG:3857');
}

// function initiateMap(){
			
			//roatte button
			var RotateNorthControl = function(opt_options) {
			  var options = opt_options || {};
			  var button = document.createElement('button');
			  button.innerHTML = 'N';
			  var this_ = this;
			  var btnHandle = function(e) {
				this_.getMap().getView().setRotation(0);
			  };

			  button.addEventListener('click', btnHandle, false);
			  button.addEventListener('touchstart', btnHandle, false);
			  var element = document.createElement('div');
			  element.className = 'map-btn ol-unselectable ol-control';
			  element.appendChild(button);
			  ol.control.Control.call(this, {element: element,target: options.target  });
			}; 
			ol.inherits(RotateNorthControl, ol.control.Control);	
			
			//save button
			var SavePNG = function(opt_options) {
			  var options = opt_options || {};
			  var button = document.createElement('button');
			  button.innerHTML = '<i class=\'fa fa-save\'></i>';
			  var this_ = this;
			  var btnHandle = function(e) {
				this_.getMap().once('postcompose', function(event) {
					  var canvas = event.context.canvas;
					  window.open(canvas.toDataURL('image/png'));
					});
				this_.getMap().renderSync();
			  };
			  
			  button.addEventListener('click', btnHandle, false);
			  button.addEventListener('touchstart', btnHandle, false);
			  var element = document.createElement('div');
			  element.className = 'map-save ol-unselectable ol-control';
			  element.appendChild(button);
			  ol.control.Control.call(this, {element: element,target: options.target });
			};
			ol.inherits(SavePNG, ol.control.Control);			
			
			var Legend = function(opt_options) {
			  var options = opt_options || {};
			  var this_ = this;
			  var element = document.createElement('div');
			  element.className = 'map-legend ol-unselectable';
			  ol.control.Control.call(this, {element: element,target: options.target });
			};
			ol.inherits(Legend, ol.control.Control);			
			
			var container = document.getElementById('popup');
			var content = document.getElementById('popup-content');	
			
			var overlay = new ol.Overlay(({
			  element: container,
			  autoPan: true,
			  autoPanAnimation: {
				duration: 250
			  }
			}));				
			
			var view = new ol.View({
					center: ol.proj.transform([137.393801,-5.267262], 'EPSG:4326', 'EPSG:3857'),
					zoom: 6
				})

			var papua_map = new ol.Map({
				controls: [
					new ol.control.Zoom({className: 'map-zoom'}),
					new ol.control.ScaleLine({className: 'map-scale'}),
					new RotateNorthControl(),
					new ol.control.MousePosition({className: 'map-mousepos', coordinateFormat: ol.coordinate.createStringXY(4), projection: 'EPSG:4326',}),
					new ol.control.FullScreen({source: 'fullscreen'}),
					new SavePNG(),
					new Legend(),
					],
					layers: [new ol.layer.Tile({ source: new ol.source.OSM()})],
				overlays: [overlay],	
				target: 'papua_map',
				view : view
				
			});	
			
			papua_map.getView().on('zoom', function(e) {
				$('#reset_btn').removeClass('disabled');
			});			

			papua_map.getView().on('moveend', function(e) {
				$('#reset_btn').removeClass('disabled');
			});

			var tooltip = function(feature) { return ''};			
			
			var displayFeatureInfo = function(pixel) {
			  var feature = papua_map.forEachFeatureAtPixel(pixel, function(feature, layer) {
				return feature;
			  });
			  
			  if (feature) {
				var coordinate = papua_map.getCoordinateFromPixel(pixel);
				content.innerHTML = tooltip(feature);
				overlay.setPosition(coordinate);				
			  } else {
			    overlay.setPosition(undefined);
			  }
			};		

			var displayMore = function(pixel) {
			  var feature = papua_map.forEachFeatureAtPixel(pixel, function(feature, layer) {
				return feature;
			  });
			  
			  if (feature) {
				var coordinate = papua_map.getCoordinateFromPixel(pixel);
				content.innerHTML = tooltip(feature);
				overlay.setPosition(coordinate);				
				// console.log(feature.attributes);
				if (feature.attributes.tdata != undefined) {
					getFocusOnMap(feature);
					// console.log(feature.getGeometry());
				}
			  }
			};				
			
			papua_map.on('pointermove', function(evt) {
			  if (evt.dragging) {
			    overlay.setPosition(undefined);
				return;
			  }
			  displayFeatureInfo(papua_map.getEventPixel(evt.originalEvent));
			});		
			
			papua_map.on('click', function(evt) {
				displayFeatureInfo(evt.pixel);
				displayMore(evt.pixel);
			});			

			$(function(){
				papua_map.updateSize();
			});


	var extents = {
        papua: transform([133, -10, 143, 1.4]),
        pulau_papua: transform([128, -11.4, 154, 2]),
        indonesia: transform([93, -13.75, 147.5, 11.8]),
        world: transform([-180, -85, 180, 85])
      };
// }

function loadKabupatenMap(id){

    // extent: extents.scale1,
	source = new ol.source.OSM();
	papua_map.getLayers().clear();
	papua_map.addLayer(new ol.layer.Tile({ extent: extents.indonesia, source: source}));
	tooltip = function(feature) {
		// return '<table border=0 class="table"><tr><th nowrap>'+feature.attributes.urai+'</th></tr><tr><td>'+feature.attributes.tdata+'<br>'+feature.attributes.kelompok+' : '+feature.attributes.data+'</td></tr></table>';
		return '<table class="tooltip-table"><tr><th nowrap>'+feature.attributes.urai+'</th></tr><tr><td>'+feature.attributes.data+' '+feature.attributes.satuan+'</td></tr></table>';
	};

	// var boundingExtent = extents.pulau_papua;  
	// boundingExtent = ol.proj.transformExtent(boundingExtent);
	// view.fit(extents.pulau_papua, papua_map.getSize()); 
	// papua_map.setOptions({restrictedExtent: extents.indonesia});

	// indikator & tahun
	$tahun = $('#tahun').val();
				
	$.post(PUSDAHOST+'ajax/map/kabupaten/'+id,{'cmd':'map_gerbangmas','id':id,'tahun':$tahun}, function(data) {

		// table of data
		$('#table_data div.table-container').html(data.table_data);

		// chart
		$chart = data.chart;
		load2AxisChart($chart.judul,$chart.sumber,$chart.kategori,$chart.series1,$chart.series2);

		// judul / map title
		// console.log(data.map);
		$('#map_title').find('td.judul').html(data.data.namapeta);
		$('#map_title').find('td.sumber').html(data.data.sumberpeta);

		// console.log(data.legend);

		// GIS - map
		$('.map-legend').html('<table><tr><td><b>Legenda</b></td></tr>');
		$.each(data.legend,function(i,e){
			if (e.hexcolor == '') {
				$('.map-legend').append('<tr><td bgcolor=\''+e.hexcolor+'\'>&nbsp;&nbsp;&nbsp;</td><td colspan=2>&nbsp;'+e.urai+'</td></tr>');
			} else {
				$('.map-legend').append('<tr><td bgcolor=\''+e.hexcolor+'\'>&nbsp;&nbsp;&nbsp;</td><td>&nbsp;'+e.urai+'</td></tr>');
			}
		});
		$('.map-legend').append('</table>');

		// if (id == 0) {resetMap(view);}

		var k = 0;
		$.each(data.map,function(i,e){
		// source = e.wkt_1;

		var format = new ol.format.WKT();
		var feature = format.readFeature(e.wkt_1);
		nfeature = feature;
		// console.log(nfeature);
		feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
		feature.attributes = {'id' : e.id, 'urai': e.urai, 'tdata': e.tdata, 'kelompok':e.kelompok_1, 'data': e.data_1, 'satuan': e.data_1_satuan};
						
		var layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				fill: new ol.style.Fill({
				  color: e.color_1
				}),
				stroke: new ol.style.Stroke({
				  color: e.stroke_1,
				  width: 2
				}),
			  })
			});

			layer.on('click',function(e){alert('a');})

			layer.setZIndex(0);
			papua_map.addLayer(layer);

			// resetMap();
			// if(id > 0) {getFocusOnMap(feature);} 
			// text nama kabupaten
			
/**					feature = format.readFeature(e.wkt_2);
			feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
			// feature.attributes = {'id' : e.id, 'urai': e.urai, 'kelompok':e.kelompok_2, 'data': e.data_2};
			layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				  text: new ol.style.Text({
			          text: e.urai,
			          scale: 1.1,
			          fill: new ol.style.Fill({
			            color: '#000000'
			          }),
			          stroke: new ol.style.Stroke({
			            color: '#dddddd',
			            width: 3.3
			          })
			      })
			  })
			});
			layer.setZIndex(k++);
			papua_map.addLayer(layer);	
*/	
			feature = format.readFeature(e.wkt_2);
			feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
			feature.attributes = {'id' : e.id, 'urai': e.urai, 'kelompok':e.kelompok_2, 'data': e.data_2, 'satuan': e.data_2_satuan};
			layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				  image: new ol.style.Circle({
					radius: 5+Math.floor(e.pct_2/10),
					snapToPixel: false,
					fill: new ol.style.Fill({
						color: e.color_2
					}),
					stroke: new ol.style.Stroke({
						color: e.stroke_2,
						width: 1
					})
				  }),
				  // text: new ol.style.Text({
			   //        text: '\n\r'+e.urai,
			   //        scale: 1.3,
			   //        fill: new ol.style.Fill({
			   //          color: '#000000'
			   //        }),
			   //        stroke: new ol.style.Stroke({
			   //          color: '#dddddd',
			   //          width: 2
			   //        })
			   //    })
			  })
			});
			layer.setZIndex(k++);
			papua_map.addLayer(layer);	
	
		// end each
		});
		
	},'json');	
}
			
function loadProvinsiMap(id){
	/*
	 | menampilkan peta simtaru
	 */
	papua_map.getLayers().clear();
	papua_map.addLayer(new ol.layer.Tile({ source: new ol.source.OSM()}));
	tooltip = function(feature) {
		var msg = '<table border=1 width=100%>';
		$.each(feature.attributes.data,function(i,e){
			msg += '<tr><td>'+i+'</td><td>'+e+'</tr>';
		});
		msg += '</table>';
		return msg;
	};

	loadAnimationTo('judul_peta');

	$.post(PUSDAHOST+'ajax/map/provinsi/1',{'cmd':'map_provinsi','id':id}, function(data) {
		$('.map-legend').html('<table><tr><td><b>Legenda</b></td></tr></table>');
		$.each(data.legend,function(i,e){
			$('.map-legend').html($('.map-legend').html()+'<table><tr><td bgcolor=\''+e.hexcolor+'\'>&nbsp;&nbsp;</td><td>&nbsp;&nbsp;'+e.urai+'</td></tr></table>');
		});

		// judul / map title
		// console.log(data.map);
		$('#map_title').hide();
		// $('#map_title').find('td.judul').html(data.data.namapeta);
		// $('#map_title').find('td.sumber').html(data.data.sumberpeta);
					
		var k=0;
		$.each(data.map,function(i,e){
			var format = new ol.format.WKT();
			var feature = format.readFeature(e.wkt);
			feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
			feature.attributes = {data : e.data};
			var layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				fill: new ol.style.Fill({
				  color: e.color
				}),
				stroke: new ol.style.Stroke({
				  color: e.stroke,
				  width: 2
				}),
				image: new ol.style.Circle({
					radius: 5,
					snapToPixel: false,
					fill: new ol.style.Fill({
						color: e.color
					}),
					stroke: new ol.style.Stroke({
						color: e.stroke,
						width: 1
					})
				}),
			  })
			});
			layer.setZIndex(0);
			papua_map.addLayer(layer);	
									
		});
		
	},'json');	

	removeAnimationFrom('judul_peta');
};			

function loadKelompok(idkelompok){
	//set judul
	// console.log(idkelompok);
	$judul = $('#indikator_'+idkelompok).data('judul');
	$('span.judul').html($judul);

	$('#indikator_kelompok').val(idkelompok);

	id = $('#jenis_analisa').val();
	loadKabupatenMap_detail(id);

	$map_type = $('#tahun').data('map-type');
	if ($map_type == 'analisa_kabupaten') {
		loadKabupatenMap(id);	
	} else if ($map_type == 'wilayah_adat') {
		loadWilayahMap(id);	
	} else {
		loadKabupatenMap_detail(id);	
	}
}

function loadKabupatenMap_detail(id){
	/*
	 | menampilkan peta tematik kabupaten
	 */
	source = new ol.source.OSM();
	papua_map.getLayers().clear();
	papua_map.addLayer(new ol.layer.Tile({ source: source}));
	tooltip = function(feature) {
		// return '<table border=0 class="table"><tr><th nowrap>'+feature.attributes.urai+'</th></tr><tr><td>'+feature.attributes.tdata+'<br>'+feature.attributes.kelompok+' : '+feature.attributes.data+'</td></tr></table>';
		return '<table class="tooltip-table"><tr><th nowrap>'+feature.attributes.urai+'</th></tr><tr><td>'+feature.attributes.data+' '+feature.attributes.satuan+'</td></tr></table>';
	};

	// indikator & tahun
	$idkelompok = $('#indikator_kelompok').val();
	if ($idkelompok == 0) {
		$idkelompok = $('#sub_kelompok').val();
	}
	$tahun = $('#tahun').val();
				
	$.post(PUSDAHOST+'ajax/map/kabupaten_detail/'+id,{'cmd':'map_gerbangmas','id':id,'tahun':$tahun,'idkelompok':$idkelompok}, function(data) {

		// table of data
		$('#table_data div.table-container').html(data.table_data);

		// chart
		$chart = data.chart;
		// $('#chart div.chart-container').html('');
		reloadChart($chart.judul,$chart.sumber,$chart.kategori,$chart.satuan,$chart.series,$chart.type);

		// judul / map title
		// console.log(data.map);
		// $('#map_title').hide();
		$('#map_title').find('td.judul').html(data.data.namapeta);
		$('#map_title').find('td.sumber').html(data.data.sumberpeta);

		// GIS - map
		$('.map-legend').html('<table><tr><td><b>Legenda</b></td></tr>');
		$.each(data.legend,function(i,e){
			$('.map-legend').append('<tr><td bgcolor=\''+e.hexcolor+'\'>&nbsp;&nbsp;&nbsp;</td><td>&nbsp;'+e.urai+'</td></tr>');
		});
		$('.map-legend').append('</table>');
						
		if (id == 0) {resetMap(view);}

		var k=0;
		$.each(data.map,function(i,e){
			// source = e.wkt_1;

			var format = new ol.format.WKT();
			var feature = format.readFeature(e.wkt_1);
			nfeature = feature;
			// console.log(nfeature);
			feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
			feature.attributes = {'id' : e.id, 'urai': e.urai, 'tdata': e.tdata, 'kelompok':e.kelompok_1, 'data': e.data_1, 'satuan': e.data_1_satuan};
			// console.log(feature.attributes);
			var layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				fill: new ol.style.Fill({
				  color: e.color_1
				}),
				stroke: new ol.style.Stroke({
				  color: e.stroke_1,
				  width: 1.5
				}),
			  })
			});

			layer.on('click',function(e){alert('a');})

			layer.setZIndex(0);
			papua_map.addLayer(layer);

			if(id > 0) {getFocusOnMap(feature);} 

			// text nama kabupaten
			
			feature = format.readFeature(e.wkt_2);
			feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
			// feature.attributes = {'id' : e.id, 'urai': e.urai, 'kelompok':e.kelompok_2, 'data': e.data_2};
			layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				  text: new ol.style.Text({
			          text: e.urai,
			          scale: 1.1,
			          fill: new ol.style.Fill({
			            color: '#000000'
			          }),
			          stroke: new ol.style.Stroke({
			            color: '#ffffff',
			            width: 1.5
			          })
			      })
			  })
			});
			layer.setZIndex(k++);
			papua_map.addLayer(layer);	
/**		
			feature = format.readFeature(e.wkt_2);
			feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
			feature.attributes = {'id' : e.id, 'urai': e.urai, 'kelompok':e.kelompok_2, 'data': e.data_2};
			layer = new ol.layer.Vector({
			  source: new ol.source.Vector({
				features: [feature]
			  }),
			  style: new ol.style.Style({
				  image: new ol.style.Circle({
					radius: 5+Math.floor(e.pct_2/10),
					snapToPixel: false,
					fill: new ol.style.Fill({
						color: e.color_2
					}),
					stroke: new ol.style.Stroke({
						color: e.stroke_2,
						width: 1
					})
				  }),
				  text: new ol.style.Text({
			          text: e.urai,
			          scale: 1.3,
			          fill: new ol.style.Fill({
			            color: '#000000'
			          }),
			          stroke: new ol.style.Stroke({
			            color: '#dddddd',
			            width: 2
			          })
			      })
			  })
			});
			layer.setZIndex(k++);
			papua_map.addLayer(layer);	
*/	
		// end each
	});
		
	},'json');	
}

function loadWilayahMap(id){
	source = new ol.source.OSM();
	papua_map.getLayers().clear();
	papua_map.addLayer(new ol.layer.Tile({ source: source}));
	tooltip = function(feature) {
		// return '<table border=0 class="table"><tr><th nowrap>'+feature.attributes.urai+'</th></tr><tr><td>'+feature.attributes.tdata+'<br>'+feature.attributes.kelompok+' : '+feature.attributes.data+'</td></tr></table>';
		return '<table class="tooltip-table"><tr><th nowrap>'+feature.attributes.urai+'</th></tr><tr><td>'+feature.attributes.data+' '+feature.attributes.satuan+'</td></tr></table>';
	};

	// indikator & tahun
	$idkelompok = $('#indikator_kelompok').val();
	if ($idkelompok == 0) {
		$idkelompok = $('#sub_kelompok').val();
	}
	$tahun = $('#tahun').val();
				
	$.post(PUSDAHOST+'ajax/map/wilayah_detail/'+id,{'cmd':'map_tematik','id':id,'tahun':$tahun,'idkelompok':$idkelompok}, function(data) {

		// table of data
		$('#table_data div.table-container').html(data.table_data);

		// chart
		$chart = data.chart;
    	// $('#chart div.chart-container').html('');
		reloadChart($chart.judul,$chart.sumber,$chart.kategori,$chart.satuan,$chart.series,$chart.type);

		// judul / map title
		// console.log(data.map);
		// $('#map_title').hide();
		$('#map_title').find('td.judul').html(data.data.namapeta);
		$('#map_title').find('td.sumber').html(data.data.sumberpeta);

		// GIS - map
		$('.map-legend').html('<table><tr><td><b>Legenda</b></td></tr></table>');
		$.each(data.legend,function(i,e){
			$('.map-legend').html($('.map-legend').html()+'<table><tr><td bgcolor=\''+e.hexcolor+'\'>&nbsp;&nbsp;&nbsp;</td><td>&nbsp;'+e.urai+'</td></tr></table>');
		});
					
		// if (id == 0) {resetMap(view);}

		var k=0;
		$.each(data.map,function(i,e){
		// source = e.wkt_1;

						var format = new ol.format.WKT();
						var feature = format.readFeature(e.wkt_1);
						nfeature = feature;
						// console.log(nfeature);
						feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
						feature.attributes = {'id' : e.id, 'urai': e.urai, 'tdata': e.tdata, 'kelompok':e.kelompok_1, 'data': e.data_1, 'satuan': e.data_1_satuan};
						var layer = new ol.layer.Vector({
						  source: new ol.source.Vector({
							features: [feature]
						  }),
						  style: new ol.style.Style({
							fill: new ol.style.Fill({
							  color: e.color_1
							}),
							stroke: new ol.style.Stroke({
							  color: e.stroke_1,
							  width: 1.5
							}),
						  })
						});

						layer.on('click',function(e){alert('a');})

						layer.setZIndex(0);
						papua_map.addLayer(layer);

						if(id > 0) {getFocusOnMap(feature);} 

						// text nama kabupaten
						
						feature = format.readFeature(e.wkt_2);
						feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
						// feature.attributes = {'id' : e.id, 'urai': e.urai, 'kelompok':e.kelompok_2, 'data': e.data_2};
						layer = new ol.layer.Vector({
						  source: new ol.source.Vector({
							features: [feature]
						  }),
						  style: new ol.style.Style({
							  text: new ol.style.Text({
						          text: e.urai,
						          scale: 1.1,
						          fill: new ol.style.Fill({
						            color: '#000000'
						          }),
						          stroke: new ol.style.Stroke({
						            color: '#dddddd',
						            width: 3.3
						          })
						      })
						  })
						});
						layer.setZIndex(k++);
						papua_map.addLayer(layer);	
			/**		
						feature = format.readFeature(e.wkt_2);
						feature.getGeometry().transform('EPSG:4326', 'EPSG:3857');
						feature.attributes = {'id' : e.id, 'urai': e.urai, 'kelompok':e.kelompok_2, 'data': e.data_2};
						layer = new ol.layer.Vector({
						  source: new ol.source.Vector({
							features: [feature]
						  }),
						  style: new ol.style.Style({
							  image: new ol.style.Circle({
								radius: 5+Math.floor(e.pct_2/10),
								snapToPixel: false,
								fill: new ol.style.Fill({
									color: e.color_2
								}),
								stroke: new ol.style.Stroke({
									color: e.stroke_2,
									width: 1
								})
							  }),
							  text: new ol.style.Text({
						          text: e.urai,
						          scale: 1.3,
						          fill: new ol.style.Fill({
						            color: '#000000'
						          }),
						          stroke: new ol.style.Stroke({
						            color: '#dddddd',
						            width: 2
						          })
						      })
						  })
						});
						layer.setZIndex(k++);
						papua_map.addLayer(layer);	
			*/	
					// end each
					});
					
				},'json');	
			};

function getFocusOnMap(nfeature){
	/*zoom in*/
	var polygon = /** @type {ol.geom.SimpleGeometry} */ (nfeature.getGeometry());
    var size = /** @type {ol.Size} */ (papua_map.getSize());
    view.fit(polygon, size, {padding: [50, 50, 50, 50], constrainResolution: false});
	$('#reset_btn').removeClass('disabled');
}

function resetMap(pmap){
	// var sview = new ol.View({
	// 				center: ol.proj.transform([137.393801,-5.267262], 'EPSG:4326', 'EPSG:3857'),
	// 				zoom: 6
	// 			});

	// var papua_map = new ol.Map({
	// 			target: 'papua_map',
	// 			view : sview
	// 		});	
	// view.fit();
	// console.log(papua_map);
	// console.log(pmap.getView());
	// pmap.setView(sview);
	$('#reset_btn').addClass('disabled');
	if (pmap == undefined) {pmap = view;}
	coord = ol.proj.transform([137.393801,-5.267262], 'EPSG:4326', 'EPSG:3857');
	pmap.setCenter(coord);
	pmap.setZoom(6);
}

// function detailChart(){

// }

function loadSubKelompok(id,trigger,target){
    if (id == 0) {
        return false
    }
    disableElement(trigger);
    disableElement(target);
     $.get(PUSDAHOST+'ajax/map/listsubkelompok_kabupaten/'+id+'?',{'ajaxOn':1})
        .success(function(data) { 
            enableElement(trigger);
            enableElement(target);
            var jsSearch = jQuery.parseJSON(data);
            if (jsSearch.options != null) {
                $('#'+target).show();
                $('#'+target).parents('div.col.sub_kelompok').show();
                $('#'+target).html('<option value="0">-- Pilih Kelompok Data --</option>'+jsSearch.options);
            } else {
                $('#'+target).hide();
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

// function loadChart(id){
// 	$('#chart').show();
//     $('#chart').html('<div id="chart1" class="bg-gray" style="min-width: auto; height: auto; margin: 0 auto"></div>');

//     $.get(PUSDAHOST+'ajax/progis/chart/'+id+'?',{'ajaxOn':1})
//                 .success(function(data) { 
//                     var $chart = jQuery.parseJSON(data);
//                     reloadChart($chart.judul,$chart.sumber,$chart.kategori,$chart.satuan,$chart.series,$chart.type);
//                     removeAnimationFrom('chart_option');
//                     removeAnimationFrom('chart_content');            
//                 })
//                 .error(function(jqXHR, textStatus) {    
//                     add_aai_notif ('','e');
//                     removeAnimationFrom('chart_option');
//                     removeAnimationFrom('chart_content');
//                 });
// }

function reloadChart(title,sumber,cat,y_title,series,type) {
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
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        // series: series
        series: [{
            showInLegend: false,
            name: series[0].name,
            data: series[0].data                
        }]
    });
}

function load2AxisChart(title,sumber,cat,series1,series2) {
    //2 y-axis chart
    $('#chart1').highcharts({
        chart: {
            zoomType: 'xy'
        },
        title: {
            text: title
        },
        subtitle: {
            text: 'sumber : '+sumber
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
            type: 'column',
            data: series2.data,
            tooltip: {
                valueSuffix: ' '+series2.satuan
            }
        }]
    });
   $('.highcharts-legend').find('rect:first-child').attr('fill-opacity','0.7');
}

function cetakHalamanMap(){
	$element = "<div style='position:relative;'>";
	// $element += $('#papua_map').html();
	$element += $('#table_data').html();
	$element += $('#chart').html();
	$element += "</div>";

	element_Print($element);
}


function element_Print(content) {
	/* req :
	 * element yg harus dicetak
	 * element yg dihapuskan dari element : .no-print
	 */

	$print_title = $(document).data('print_title');
    $('#print_title').html($print_title);

    // return false;

    $('div#print_area').show();
    $('#print_content').html(''+content);
    $('#print_content').prepend($('#papua_map'));
    $('#print_content').find('.no-print').hide();

// $print_title = $(document).data('print_title');

    //copy canvas
	// var c = document.getElementById("myCanvas");
 //    var ctx = c.getContext("2d");
    // var canv = $('div.ol-viewport canvas').toDataURL();
    // console.log(canv);
    // ctx.drawImage(canv, 10, 10);

    // delai print
    // setTimeout(function(){window.print();},1555);
    $('div.wrapper').hide();
}

function cancel_print(){
    $('#map_content').prepend($('#papua_map')).find('.no-print').show();
	$('div#print_area').hide();
	$('#print_content').html('');
    $('div.wrapper').show();
}

$(document).ready(function(){
	
	// $("#papua_map").append($('#over_layer'));
	$("#papua_map").append($('#table_data'));
	$("#papua_map").append($('#chart'));
	$("#papua_map").append($('#options'));
	$("#papua_map").append($('#judul_peta'));
	$("#papua_map").append($('#map_title'));
	// initiateMap();

	$('#tahun').on('change',function(){
		$map_type = $(this).data('map-type');
		id = $('#jenis_analisa').val();

		if ($map_type == 'analisa_kabupaten') {
			loadKabupatenMap(id);	
		} else if ($map_type == 'wilayah_adat') {
			loadWilayahMap(id);	
		} else {
			loadKabupatenMap_detail(id);	
		}
	});

	//chart button
	$('.chart-button').click(function(){
		$('#table_data').hide(100);
		$('#chart').toggle(function() {
		  // alert( "First handler for .toggle() called." );
		}, function() {
		  // alert( "Second handler for .toggle() called." );
		});
	});

	//table data
	$('.table-button').click(function(){
		$('#chart').hide(100);
		$('#table_data').toggle(function() {
		  // alert( "First handler for .toggle() called." );
		}, function() {
		  // alert( "Second handler for .toggle() called." );
		});
	});

});

$(function(){
	$(".dropdown-menu > li > a.trigger").on("click",function(e){
		var current=$(this).next();
		var grandparent=$(this).parent().parent();
		if($(this).hasClass('left-caret')||$(this).hasClass('right-caret'))
			$(this).toggleClass('right-caret left-caret');
		grandparent.find('.left-caret').not(this).toggleClass('right-caret left-caret');
		grandparent.find(".sub-menu:visible").not(current).hide();
		current.toggle();
		e.stopPropagation();
	});
	$(".dropdown-menu > li > a:not(.trigger)").on("click",function(){
		var root=$(this).closest('.dropdown');
		root.find('.left-caret').toggleClass('right-caret left-caret');
		root.find('.sub-menu:visible').hide();
	});
});

// var createTextStyle = function(feature, resolution, dom) {
//         var align = dom.align.value;
//         var baseline = dom.baseline.value;
//         var size = dom.size.value;
//         var offsetX = parseInt(dom.offsetX.value, 10);
//         var offsetY = parseInt(dom.offsetY.value, 10);
//         var weight = dom.weight.value;
//         var rotation = parseFloat(dom.rotation.value);
//         var font = weight + ' ' + size + ' ' + dom.font.value;
//         var fillColor = dom.color.value;
//         var outlineColor = dom.outline.value;
//         var outlineWidth = parseInt(dom.outlineWidth.value, 10);

//         return new ol.style.Text({
//           textAlign: align,
//           textBaseline: baseline,
//           font: font,
//           text: getText(feature, resolution, dom),
//           fill: new ol.style.Fill({color: fillColor}),
//           stroke: new ol.style.Stroke({color: outlineColor, width: outlineWidth}),
//           offsetX: offsetX,
//           offsetY: offsetY,
//           rotation: rotation
//         });
//       };