/*
	https://stackoverflow.com/a/40471701
	https://jsfiddle.net/user2314737/324h2d9q/
	https://jsfiddle.net/w8r/2a5j2x07/
	http://leaflet.github.io/Leaflet.draw/docs/leaflet-draw-latest.html
	https://leaflet.github.io/Leaflet.draw/docs/examples/full.html
*/
// ...

if (window.Leaflet && window.Leaflet.drawLocal) {
	window.Leaflet.drawLocal.draw.toolbar.actions.text = 'Batal';
	window.Leaflet.drawLocal.draw.toolbar.actions.title = '';

	window.Leaflet.drawLocal.draw.toolbar.finish.text = 'Selesai';
	window.Leaflet.drawLocal.draw.toolbar.finish.title = '';

	window.Leaflet.drawLocal.draw.toolbar.undo.text = 'Hapus titik sebelumnya';
	window.Leaflet.drawLocal.draw.toolbar.undo.title = '';

	window.Leaflet.drawLocal.draw.toolbar.buttons.polygon = 'Buat Polygon';
	window.Leaflet.drawLocal.draw.toolbar.buttons.marker = 'Buat Penanda';

	window.Leaflet.drawLocal.draw.handlers.marker.tooltip.start = 'Klik pada peta untuk menandai';

	window.Leaflet.drawLocal.draw.handlers.polygon.tooltip.start = 'Klik untuk mulai membuat bentuk';
	window.Leaflet.drawLocal.draw.handlers.polygon.tooltip.cont = 'Klik untuk lanjut membuat bentuk';
	window.Leaflet.drawLocal.draw.handlers.polygon.tooltip.end = 'Klik titik pertama untuk menutup bentuk';

	window.Leaflet.drawLocal.edit.toolbar.actions.save.text = 'Simpan';
	window.Leaflet.drawLocal.edit.toolbar.actions.save.title = '';

	window.Leaflet.drawLocal.edit.toolbar.actions.cancel.text = 'Batal';
	window.Leaflet.drawLocal.edit.toolbar.actions.cancel.title = '';

	window.Leaflet.drawLocal.edit.toolbar.actions.clearAll.text = 'Hapus semua';
	window.Leaflet.drawLocal.edit.toolbar.actions.clearAll.title = '';

	window.Leaflet.drawLocal.edit.toolbar.buttons.edit = 'Ubah bentuk';
	window.Leaflet.drawLocal.edit.toolbar.buttons.editDisabled = ''; // 'Tidak ada bentuk untuk diubah';

	window.Leaflet.drawLocal.edit.toolbar.buttons.remove = 'Hapus bentuk';
	window.Leaflet.drawLocal.edit.toolbar.buttons.removeDisabled = ''; // 'Tidak ada bentuk untuk dihapus';

	window.Leaflet.drawLocal.edit.handlers.edit.tooltip.text = ''; // 'Tarik titik atau tanda untuk merubah bentuk';
	window.Leaflet.drawLocal.edit.handlers.edit.tooltip.subtext = '';

	window.Leaflet.drawLocal.edit.handlers.remove.tooltip.text = 'Klik pada bentuk untuk hapus';
}

window.MemPetaKan = function(el_id, map_options) {
	var that = this;
	this.save_callback;
	this.options = {
		zoom: 8,
		distance_m: 0.0000089,
		distance_km: 0.0089,
		sup: {
			color: { OSM: 'gray', GMaps: 'cyan' },
			fillColor: { OSM: 'transparent', GMaps: 'transparent' },
		},
		sub: {
			color: { OSM: 'purple', GMaps: 'purple' },
		},
		edit_remove: false,
		draw_polygon: {
			allowIntersection: false,
			shapeOptions: { color: 'purple' }
		},
		tooltip: {
			direction:'center',
			opacity: 0.5,
			sticky: true,
			interactive: false,
		},
		attribution: {
			// tidak boleh dirubah, melanggar hak cipta.
			OSM: 'Map &copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OSM</a> Contributors',
			GMaps: 'Map &copy; Google',
		}
	};

	this.sup = null;
	this.subs = [];
	this.map = window.Leaflet.map(el_id, $.extend(true,{ zoomControl: false }, (map_options || {})));

	this.map.on('baselayerchange', function(layer) {
		if (that.sup) {
			that.sup.setStyle({ color: that.options.sup.color[layer.name] });
		}
	});
};

MemPetaKan.prototype.map_control_layers = function() {
	if (this.control_layers) return this;
	this.control_layers = window.Leaflet.control.layers({
		OSM: window.Leaflet.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
			attribution: this.options.attribution.OSM,
		}),
		GMaps: window.Leaflet.tileLayer('https://www.google.cn/maps/vt?lyrs=s@189&gl=cn&x={x}&y={y}&z={z}',{
			attribution: this.options.attribution.GMaps,
		}),
	}).addTo(this.map);
	return this;
};

MemPetaKan.prototype.save = function(fn) { this.save_callback = fn; };

MemPetaKan.prototype.map_draw_control = function() {
	if (this.draw_control) return this;
	var that = this;
	this.draw_layers = window.Leaflet.featureGroup().addTo(this.map);
	this.draw_control = new window.Leaflet.Control.Draw({
		// position: 'topright',
		edit: {
			featureGroup: this.draw_layers,
			remove: this.options.edit_remove,
		},
		draw: {
			circlemarker: false,
			rectangle: false,
			polyline: false,
			circle: false,
			marker: false,
			polygon: this.options.draw_polygon,
		},
	});
	this.map.addControl(this.draw_control);

	/*
	this.map.on(window.Leaflet.Draw.Event.CREATED, function (ev) {
		that.draw_layers.addLayer(ev.layer);
	});
	*/
	this.map.on(window.Leaflet.Draw.Event.EDITED, function (ev) {
		if (that.save_callback) that.save_callback(ev);
	});

	return this;
};

MemPetaKan.prototype.sup_init = function(poly) {
	if (!this.sup) {
		this.sup = window.Leaflet.polygon(poly, {
			color: this.options.sup.color.OSM,
			fillColor: this.options.sup.fillColor.OSM,
		}).addTo(this.map);
	}
	if (this.sup) this.map.fitBounds(this.sup.getBounds());
	return this;
};

MemPetaKan.prototype.sup_latlng = function(lat,lng,zoom) {
	if (lat) this.options.lat = lat;
	if (lng) this.options.lng = lng;
	if (this.options.lat && this.options.lng) {
		this.map.setView([this.options.lat, this.options.lng], (zoom || this.options.zoom));
	}
	return this;
};

MemPetaKan.prototype.text2poly = function(text, rvrs) {
	text = text.split(',');
	text[0] = text[0].replace(/^([a-zA-Z]+\({1,})/,'');
	text[text.length-1] = text[text.length-1].replace(/(\){1,})$/,'');
	text = text.map(function(node) {
		node = node.split(' ');
		if (rvrs) node.reverse();
		return node.map(window.parseFloat);
	});
	return text;
};
MemPetaKan.prototype.poly2text = function(poly,rvrs) {
	poly = poly.map(function(node) {
		if (rvrs) node.reverse();
		return node.join(' ');
	});
	poly[0] = 'Polygon((' + poly[0];
	poly[poly.length-1] = poly[poly.length-1] + '))';
	poly = poly.join(',');
	return poly;
};

MemPetaKan.prototype.geojson2text = function(geojson,rvrs) {
	rvrs = rvrs || false;
	var coords = geojson.geometry.coordinates[0];
	for (var i = 0; i < coords.length; i++) {
		if (rvrs) coords[i] = coords[i].reverse();
		coords[i] = coords[i].join(' ');
	}
	return [geojson.geometry.type,'((', coords.join(','), '))'].join('');
};
