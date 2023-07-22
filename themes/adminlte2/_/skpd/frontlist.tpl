<?php
$TemplatWaras2 = TemplatWaras2::init();
?>

<?php $TemplatWaras2->open('pagescript') ?>
<script src='<?= THEME_URL ?>plugins/Highcharts-4.2.3/js/highcharts.js'></script>
<script src='<?= THEME_URL ?>plugins/Highcharts-4.2.3/js/modules/exporting.js'></script>
<script src='<?= THEME_URL ?>/js/skpd.js?20181009-0'></script>
<script src='<?= THEME_URL ?>js/progis.js?20181009-5'></script>
<?php $TemplatWaras2->close() ?>

<?php $TemplatWaras2->open('pagecontent') ?>
<style>
	.list-walidata .info-box {
		--gap: 6px;
		min-height: auto;
		padding: calc(var(--gap)*0.1) var(--gap);
	}
	.list-walidata .info-box > .info-box-text:first-child {
		margin-top: var(--gap);
	}
	.list-walidata .info-box > .info-box-text {
		margin-bottom: var(--gap);
	}
</style>

<div class="col-md-12 list-walidata">
	<div class="row">
		<?php for ($i = $pagination->indexstart; $i < $pagination->indexend; $i++):
			if(empty($xskpdz[$i]['idinstansi'])) continue;

			$nama = htmlentities($xskpdz[$i]['nama_instansi']);
			$alias = htmlentities($xskpdz[$i]['singkatan']);
			?>
			<div class="col-md-3 col-sm-4 col-xs-6" id="skpd_<?= $xskpdz[$i]['idinstansi'] ?>">
				<div class="info-box">

					<div class="info-box-text" title="<?= $nama ?>"><?= $nama ?></div>

					<div class="info-box-text info-box-name">
						<b><?= empty($alias) ? $nama : $alias ?></b>
					</div>

					<span class="info-box-text text-center">
						<button type="button" class="btn btn-info btn-sm btn-block" onClick="displaySkpdKelompok('<?= $xskpdz[$i]['idinstansi'] ?>');">
							<i class="fa fa-folder"></i> &nbsp; Lihat Kelompok Data
						</button>
					</span>

				</div>
			</div>
		<?php endfor; ?>
	</div>
</div>

<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="text-center"><?= $pagination->pagedisplay ?></div>
</div>
<?php $TemplatWaras2->close() ?>
