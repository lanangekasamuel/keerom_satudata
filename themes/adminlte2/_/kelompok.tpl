<?php
$TemplatWaras1 =& TemplatWaras1::init();
// dump(get_defined_vars()); die();

?>

<?php $TemplatWaras1->open('content') ?>
<div id='progis_data_content' data-tbmode="<?= $TemplatWaras1->get_root()->mode_kelompok ?>">
	<strong>
		<i class='fa fa-file-text-o margin-r-5'></i> &nbsp; Notes
	</strong>

	<ul>
		<?php if ($TemplatWaras1->get_root()->userAkses == 'admin'): ?>
			<li class='text-blue'>cara menambahkan indikator : </li>
			<ol>
				<li>cek instansi yang akan ditambahkan data indikatornya, jika belum ada tambahkan melalui menu instansi</li>
				<li>cek user / pengguna dari instansi tersebut, jika belum ada tambahkan melalui menu user</li>
				<li>pilih instansi yang akan ditambahkan indikator, klik lihat indikator</li>
			</ol>
			<li class='text-green'>perhatikan penulisan satuan (semisal persen yg seharusnya di ketik %)</li>
			<?= $notes ?>
		<?php // elseif(): ?>
		<?php // else: ?>
		<?php endif; ?>
	</ul>

	<div class='progis-option' id='progis-option'>
		<!--
			[anovedit][note:] sudah saya hilangkan,
			{$autocomplete_indikator}
		-->

		<?php if ($seleksi_instansi): ?>
			<!-- BERDASARKAN SKPD/INSTANSI -->
			<div class='option-content'>
				<div>
					<h4>
						<i class='fa fa-user'></i> &nbsp; Pilihan SKPD
					</h4>
				</div>
				<form method='POST' class='form-horizontal' id='frm_progis_skpd'>
					<div class='col'>
						<div class='input-group'>
							<select class='form-control' id='select_skpdinstansi'>
								<option value=0> -- PILIH SKPD PENGENTRI -- </option>
								<?php
								$matrix_many = $matrix_zero = true;
								foreach ($seleksi_instansi->result_array() as $recInstansi): ?>
									<?php if ($matrix_many && $recInstansi['matrix'] > 0): $matrix_many = false; ?>
										<option disabled> -- SUDAH MEMILIKI INDIKATOR -- </option>
									<?php endif ?>
									<?php if ($matrix_zero && $recInstansi['matrix'] < 1): $matrix_zero = false; ?>
										<option disabled> -- BELUM MEMILIKI INDIKATOR -- </option>
									<?php endif ?>
									<option value="<?= $recInstansi['idinstansi'] ?>" <?= ($selected_instansi == $recInstansi['idinstansi'] ? 'selected' : '') ?>><?= htmlentities($recInstansi['nama_instansi']) ?></option>
								<?php endforeach ?>
							</select>
							<span class='input-group-btn'>
								<button type='button' class='btn btn-warning btn-flat btn_load_kelompok_instansi'>
									<i class='fa fa-play'></i> &nbsp; Lihat Indikator
								</button>
							</span>
						</div>
					</div>	
				</form>
			</div>
		<?php endif // seleksi_instansi ?>

		<?php if ($seleksi_kelompok): ?>
			<!-- BERDASARKAN KELOMPOK DATA -->
			<div class='option-content'>
				<h4>
					<i class='fa fa-align-justify'></i> &nbsp; Pilih! Berdasarkan Jenis Data, kelompok dan Sub Kelompok Data
				</h4>
				<form method='POST' class='form-horizontal' id='frm_progis_option'>
					<div class='col col-sm-6 col-xs-12'>
						<div class='form-group'>
							<select class='form-control' id='select_jenis'>
							<option value=0>-- PILIH JENIS --</option>
							{$seleksi_kelompok}
							<?php foreach ($seleksi_kelompok->result_array() as $recJenis): ?>
								<option value="<?= $recJenis['idkelompok'] ?>"><?= htmlentities($recJenis['urai']) ?></option>
							<?php endforeach ?>
							</select>
						</div>
					</div>

					<div class='col sub_kelompok'>
						<div class='input-group col col-sm-6 col-xs-12'>
							<select class='form-control' id='select_kelompok'>
								<option value=0>-- KELOMPOK DATA --</option>
							</select>
							<span class='input-group-btn'>
								<button type='button' class='btn btn-info btn-flat btn_load_kelompok'>
									<i class='fa fa-play'></i> &nbsp; Lihat Indikator!
								</button>
							</span>
						</div>
					</div>

					<div class='col sub_kelompok'>
						<div class='input-group'>
							<select class='form-control' id='select_subkelompok1'>
								<option>0</option>
							</select>
							<span class='input-group-btn'>
								<button type='button' class='btn btn-info btn-flat btn_load_kelompok'>
									<i class='fa fa-play'></i> &nbsp; Lihat Indikator!
								</button>
							</span>
						</div>
					</div>

					<div class='col sub_kelompok'>
						<div class='input-group'>
							<select class='form-control' id='select_subkelompok2'>
								<option>0</option>
							</select>
							<span class='input-group-btn'>
								<button type='button' class='btn btn-info btn-flat btn_load_kelompok'>
									<i class='fa fa-play'></i> &nbsp; Lihat Indikator!
								</button>
							</span>
						</div>
					</div>

					<div class='col sub_kelompok'>
						<div class='input-group'>
							<select class='form-control' id='select_subkelompok3'>
								<option>0</option>
							</select>
							<span class='input-group-btn'>
								<button type='button' class='btn btn-info btn-flat btn_load_kelompok'>
									<i class='fa fa-play'></i> &nbsp; Lihat Indikator!
								</button>
							</span>
						</div>
					</div>
				</form>
			</div>
		<?php endif // seleksi_kelompok ?>
	</div>

	<?php if ($is_use_any): // table ?>
		<div class='box-header with-border no-margin'>
			<h4 class='box-title'>
				<i class='fa fa-table'></i>&nbsp; <?= $judul_table ?>
			</h4>
			<div class='ie_option box-tools pull-right'></div>
		</div>

		<div class="table-responsive">
			<table id='table_kelompok' class ='detail_data table-striped' border='0' cellpadding='0' cellspacing='0' width='100%'>
				<thead><!-- {$tableData['header']} --></thead>
				<tbody><!-- {$tableData['body']} --></tbody>
			</table>
		</div>
	<?php endif // table,is_use_any ?>
</div>

<?php if ($is_use_any): // modal ?>
	<!-- Edit/Add Modal -->
	<div id="commonModal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"><i class="fa fa-desktop"></i></h4>
				</div>
				<div class="modal-body" id="modal_content"></div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
	<!--confirm-delete modal-->
	<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">Hapus Indikator</div>
				<div class="modal-body">Anda yakin akan menghapus data ini ?</div>
				<div class="modal-footer">
					<button class="btn btn-danger btn-ok">Hapus</button>
					<button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Batal</button>
				</div>
			</div>
		</div>
	</div>
<?php endif // modal,is_use_any ?>
<?php $TemplatWaras1->close() ?>

<?php $TemplatWaras1->open('pgScript') ?>
<link rel="stylesheet" href="{themepath}plugins/colorpicker/bootstrap-colorpicker.css">
<script src="{themepath}plugins/colorpicker/bootstrap-colorpicker.js"></script>

<script src="{themepath}plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
<script src="{themepath}plugins/bootstrap-tagsinput/bootstrap3-typeahead.js"></script>
<link rel="stylesheet" href="{themepath}plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">

<link rel="stylesheet" href="{themepath}css/kelompok.css?0">
<script src="{themepath}js/kelompok.js?6"></script>

<?php if ($selected_instansi > 0): ?>
	<script>loadDaftarKelompok_instansi('<?= $selected_instansi ?>');</script>
<?php endif ?>
<?php $TemplatWaras1->close(); ?>
