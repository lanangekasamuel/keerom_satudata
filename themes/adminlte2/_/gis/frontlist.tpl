<?php
$prefix0 = 'Kabupaten';
$prefix1 = 'Distrik';

$TemplatWaras2 = TemplatWaras2::init();
$TemplatWaras2->block([
	'pagetitle' => "Peta Geografis {$prefix0} {$sup->name}",
]);
?>


<?php $TemplatWaras2->open('pagescript') ?>
<script src="<?= ROOT_URL ?>/bower_components/leaflet/dist/leaflet.js"></script>
<script src="<?= ROOT_URL ?>/bower_components/leaflet-draw/dist/leaflet.draw.js"></script>
<script>window.Leaflet = window.L.noConflict();</script>
<script src="<?= ROOT_URL ?>/themes/leaflet.js?20181028-3"></script>
<script>
// ...
var sup = <?= json_encode($sup) ?>;
var subs = <?= json_encode($subs) ?>;
var gis;

var xmapz = document.getElementById('xmapz');

$(document).ready(function() {
	$(xmapz).css('height', $(this).height()*0.67); // 2/3 browser

	gis = new MemPetaKan(xmapz);

	var attribution = ' | Data &copy; Pusdalisbang <?= $prefix0 ?> ' + sup.name;
	gis.options.attribution.OSM = gis.options.attribution.OSM + attribution;
	gis.options.attribution.GMaps = gis.options.attribution.GMaps + attribution;

	gis.sup_latlng(sup.lat,sup.lng);
	gis.sup_init(gis.text2poly(sup.poly, 'reverse'));

	gis.map_control_layers();

	$.extend(gis.options.tooltip, {
		sticky: true,
		// permanent: true,
	});
	for (var i = 0; i < subs.length; i++) {
		subs[i].map = window.Leaflet
		.polygon(gis.text2poly(subs[i].poly, 'reverse'), { color: Text2Color(subs[i].name), fillColor: 'transparent' })
		.bindTooltip('<?= $prefix1 ?> ' + subs[i].name, gis.options.tooltip)
		.addTo(gis.map);
	}
});
</script>
<?php $TemplatWaras2->close() ?>


<?php $TemplatWaras2->open('pagecontent') ?>
<link rel="stylesheet" href="<?= ROOT_URL ?>/bower_components/leaflet/dist/leaflet.css">
<link rel="stylesheet" href="<?= ROOT_URL ?>/bower_components/leaflet-draw/dist/leaflet.draw.css">

<div class='panel-group col-sm-12 col-md-12'>
	<div class='box box-success' id='output_content'>
		<div class='box-body'>
			<div id="xmapz"></div>
		</div>
	</div>
</div>
<?php $TemplatWaras2->close() ?>
