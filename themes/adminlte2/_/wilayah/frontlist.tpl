<?php
$TemplatWaras2 = TemplatWaras2::init();

$roote = 'distrik'; // root-route

?>


<?php $TemplatWaras2->open('pagescript') ?>
	<div id="chartModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"> &times; </button>
					<h4 class="modal-title">
						<i class="fa fa-bar-chart-o"></i>
						&nbsp; Chart
					</h4>
				</div>
			  <div class="modal-body" id="modal_content"><!-- {ajax:} --></div>
			  <div class="modal-footer">
			  	<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
			  </div>
			</div>
		</div>
	</div>

	<script src='<?= THEME_URL ?>plugins/Highcharts-4.2.3/js/highcharts.js'></script>
	<script src='<?= THEME_URL ?>plugins/Highcharts-4.2.3/js/modules/exporting.js'></script>
	<script src="<?= THEME_URL ?>/js/kabupaten.js?20181024-1"></script>
	<script>
		$('#chartModal')
		.modal({ show: false })
		.on('shown.bs.modal', function() {
			$('#chart1').css('width', $('.modal-body',this).offsetWidth() + 'px');
		})
		.find('.modal-body')
		.css('padding', '0')
		.html('<div id="chart1" class="bg-gray"></div>');

		function wilayah_chart(idkelompok,idkabupaten,min,max) {
			var href = '<?= ROOT_URL ?>/ajax/kabupaten/chart/' + idkelompok;
			var data = {
				ajaxOn: 1,
				idkabupaten: idkabupaten,
				tahun_chart: [],
			};
			for (var i = min; i <= max; i++) data.tahun_chart.push(i);

			$('#chartModal').modal('show');
			$.get(href, data).always(function(result) {
				var $chart = JSON.parse(result);
				reloadChart($chart.judul,$chart.kategori,$chart.sumber,$chart.satuan,$chart.series,$chart.type);
			});
		}

		(function(wilayah) {
			var _contents = document.getElementById('wilayah-table-contents').innerHTML;
			var entrypoint = '<?= ROOT_URL ?>/ajax/<?= $roote ?>';
			var form,table,years,contents;
			var pksubwilayah,min,max;

			table = $('table:first', wilayah);
			years = table.find('.years:first');
			contents = table.find('.contents:first');
			years_length = 1;

			$('form:first',wilayah).each(function() {
				form = $(this);

			}).on('submit', function(ev) {
				ev.preventDefault();
				pksubwilayah = this.elements.namedItem('pk').value;
				min = Number(this.elements.namedItem('min').value);
				max = Number(this.elements.namedItem('max').value);
				$
				.post(entrypoint + '/frontlist_contents/' + pksubwilayah, $(this).serialize())
				.always(function(result) {
					years.children().remove();
					contents.children().remove();

					years_length = 0;
					for (var i = min; i <= max; i++) {
						years_length++;
						years.append(['<th class="text-center">',i,'</th>'].join(''));
					}
					years_length = years_length > 0 ? years_length : 1;
					table.find('thead:first .ys').prop('colspan', years_length);

					if ($.isArray(result))
						for (var i = 0; i < result.length; i++)
							contents.append(parse(result[i]));
				});
			});

			function parse(entry,contents) {
				return _contents.replace(/\{(\w+)\}/gm, function(group,key) {
					var tmp;

					if(key === '_i') return (entry._i || '');

					if (key === 'satuan') return (entry._type === 3 ? entry.satuan : '');

					if (key === 'teks') {
						if (entry._type === 1) return ['<b>',entry.nama_skpd,'</b>'].join('');
						else return ('&nbsp; ' + entry.uraian);
					}

					if (key === 'years') {
						if (entry._type === 3) {
							var ys = [];
							for (var i = min; i <= max; i++) {
								ys.push(['<td class="text-right">',(entry.years[i] || ''),'</td>'].join(''));
							}
							if (ys.length < 1) ys.push('<td></td>');
							return ys.join('');
						} else return '<td></td>'.repeat(years_length);
					}

					if (key === 'chart' && entry._type == 3) {
						return (
							'<button onclick="wilayah_chart('+
							"'" + entry.idkelompok + "'," +
							"'" + pksubwilayah + "'," +
							"'" + min + "'," +
							"'" + max + "'" +
							')"><i class="fa fa-bar-chart-o"></i></button>'
						);
					}

					return (entry[key] || '');
				});
			}

		})(document.getElementById('wilayah'));
	</script>
<?php $TemplatWaras2->close() ?>


<?php $TemplatWaras2->open('pagecontent') ?>
	<style>
		#wilayah table.table th {
			vertical-align: middle;
			text-align: center;
		}
	</style>

	<script type="text/html" id="wilayah-table-contents" hidden>
		<tr>
			<td class="text-center">{_i}</td>
			<td>{teks}</td>
			{years}
			<td class="text-left">{satuan}</td>
			<td class="text-center">{chart}</td>
		</tr>
	</script>

	<div id="wilayah" class="col-sm-12 col-md-12">
		<div class="box box-danger">
			<div class="box-body">

				<form class="form-inline" action="javascript:void(0);">
					<select class="form-control" name="pk" autofocus required>
						<option value> -- Pilihan <?= $prefix0 ?> -- </option>
						<?php foreach ($wilayah_list as $wl): ?>
							<option value="<?= $wl['kodewilayah'] ?>"><?= $wl['namawilayah'] ?></option>
						<?php endforeach ?>
					</select>

					<select name="min" class="form-control" required>
						<option value> -- Dari Tahun -- </option>
						<?php for ($i=$max_tahun; $i >= $min_tahun; $i--): ?>
							<option><?= $i ?></option>
						<?php endfor ?>
					</select>

					<select name="max" class="form-control" required>
						<option value> -- Sampai Tahun -- </option>
						<?php for ($i=$max_tahun; $i >= $min_tahun; $i--): ?>
							<option><?= $i ?></option>
						<?php endfor ?>
					</select>

					<button class="btn">Lihat</button>
				</form>

				<br>

				<div class="table-responsive">
					<table class="table table-bordered table-condensed table-hover">
						<thead>
							<tr>
								<th rowspan="2">N</th>
								<th rowspan="2">SKPD / Indikator</th>
								<th class="ys" colspan="1">Tahun</th>
								<th rowspan="2">Satuan</th>
								<th rowspan="2">Aksi</th>
							</tr>
							<tr class="years"><th>N</th></tr>
						</thead>
						<tbody class="contents"></tbody>
					</table>
				</div>

			</div><!-- /.box-body -->
		</div><!-- /.box.box-danger -->
	</div>
<?php $TemplatWaras2->close() ?>
