<?php
$TemplatWaras2 = TemplatWaras2::init();

$pdfjs = '#zoom=50';

$judul = $file_item ? htmlentities($file_item->title) : 'Output Berkas Publikasi';
$keterangan = $file_item ? htmlentities($file_item->content) : 'Silahakan pilih berkas yang ada di samping kanan <i class="fa fa-arrow-right"></i>';

?>

<?php $TemplatWaras2->open('pagecontent') ?>
	<style>
		#output_content embed {
			width: 100%;
			height: 75vh;
		}
	</style>

	<div class='panel-group col-sm-12 col-md-8'>
		<div class='box box-success' id='output_content'>
			<div class='box-header with-border align-center'>
				<h4><?= $judul ?></h4>
				<div style="color: gray;"><?= $keterangan ?></div>
			</div>

			<div class='box-body'>
				<?php if ($file_item):
					$target = $ouput_folder_url . urlencode($file_item->filesumber);
					$ext = strtolower($file_item_info['extension']);
					?>

					<?php if (preg_match($module_output_class->moc_streamable, $file_item->filesumber)): ?>
						<button class="pull-right" onClick="requestFullScreen()">
							<i class="glyphicon glyphicon-fullscreen"></i>
						</button>
						<embed id="pdf_container" src="<?= $target,$pdfjs ?>">
					<?php endif ?>

					<div>
						<a download="<?= htmlentities($file_item->title), '.', $ext ?>" href="<?= $target ?>">
							<button class="btn-block">
								<i class='fa fa-download'></i> &nbsp; Download
							</button>
						</a>
					</div>
				<?php endif ?>
			</div><!-- /.box-body -->

		</div>
	</div>

	<div class='panel-group col-sm-12 col-md-4'>
		<div class='box box-success'  id='output_list'>
			<div class='box-header with-border'>
				<h4>Daftar Berkas Publikasi</h4>
			</div>
			<div class='box-body'>
				<?php if ($file_list): ?>
					<?php foreach ($file_list->result() as $item): $empty = false; ?>
						<div>
							<a href="<?= ROOT_URL ?>/output/<?= urlencode($item->title) ?>.htm">
								<i class="fa fa-file-o"></i> &nbsp; <span><?= htmlentities($item->title) ?></span>
							</a>
						</div>
					<?php endforeach ?>
				<?php endif ?>
			</div>
		</div>
	</div>
<?php $TemplatWaras2->close() ?>

<?php $TemplatWaras2->open('pagescript') ?>
	<script src="<?= THEME_URL ?>js/output.js?20181017-11"></script>
<?php $TemplatWaras2->close() ?>
