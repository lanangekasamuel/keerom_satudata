<?php
$TemplatWaras1 = TemplatWaras1::init();
$TemplatWaras1->get_root()->title = 'GIS';

$subs2 = []; foreach ($subs as $sub) $subs2[$sub->kodepemda] = $sub;
unset($subs,$sub);
?>

<?php $TemplatWaras1->open('pgScript') ?>
<script src="<?= ROOT_URL ?>/bower_components/leaflet/dist/leaflet.js"></script>
<script src="<?= ROOT_URL ?>/bower_components/leaflet-draw/dist/leaflet.draw.js"></script>
<script>window.Leaflet = window.L.noConflict();</script>
<script src="<?= ROOT_URL ?>/themes/leaflet.js?20181028-3"></script>
<script>
// ...
var sup = <?= json_encode($sup) ?>;
var subs = <?= json_encode($subs2) ?>;
var gis;

var xmapz = document.getElementById('xmapz');
var xlistz = document.getElementById('xlistz');
var xlistz_item = document.getElementById('xlistz-item').innerHTML;

$(document).ready(function() {
	$(xmapz).css('height', $(this).height()*0.80); // 4/5 browser

	$.notifyDefaults({ allow_dismiss: true, placement: {from: 'top', align: 'left'} });

	gis = new MemPetaKan(xmapz);
	gis.options.draw_polygon = false;
	gis.options.edit_remove = false;

	gis.sup_latlng(sup.lat,sup.lng);
	gis.sup_init(gis.text2poly(sup.poly, 'reverse'));

	gis.map_control_layers();
	gis.map_draw_control();

	gis.save(function() {
		var data = {};
		for (var sub in subs)
			if (gis.draw_layers.hasLayer(subs[sub].map))
				data[sub] = gis.geojson2text(subs[sub].map.toGeoJSON());
		$
		.post('<?= ROOT_URL ?>/ajax/gis/save/anovwashere', data)
		.fail(function() {
			$.notify({message: 'Gagal Simpan'}, {type: "danger"});
		})
		.success(function() {
			$.notify({message: 'Berhasil Simpan'}, {type: "success"});
		})
		.always(function() {});
	});

	for (var sub in subs) {
		xlistz.insertAdjacentHTML('beforeend', CurlyParser(xlistz_item, subs[sub]));
		subs[sub].map = window.Leaflet
		.polygon(gis.text2poly(subs[sub].poly, 'reverse'), { color: Text2Color(subs[sub].name) })
		.bindTooltip(subs[sub].name, gis.options.tooltip);
	}
});

function gis_toggle_distrik(kodepemda) {
	if (gis.draw_layers.hasLayer(subs[kodepemda].map)) {
		subs[kodepemda].map.removeFrom(gis.draw_layers);
	} else {
		subs[kodepemda].map.addTo(gis.draw_layers);
	}
}
</script>
<?php $TemplatWaras1->close() ?>


<?php $TemplatWaras1->open('content') ?>
<link rel="stylesheet" href="<?= ROOT_URL ?>/bower_components/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="<?= ROOT_URL ?>/bower_components/leaflet-draw/dist/leaflet.draw.css">
<div id="xmapz"></div>

<br>
<table class="table table-condensed table-bordered"><tbody id="xlistz"></tbody></table>
<script id="xlistz-item" type="text/html">
	<tr>
		<td>{kodepemda}</td>
		<td>{name}</td>
		<td>
			<button onclick="gis_toggle_distrik('{kodepemda}')">show/hide</button>
		</td>
	</tr>
</script>
<?php $TemplatWaras1->close() ?>
