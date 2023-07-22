<?php
$TemplatWaras1 =& TemplatWaras1::init();
// $this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();

?>

<?php if($available): $TemplatWaras1->open('pgScript') // pgScript ?>
<script src="{themepath}js/kabupaten.js"></script>
<?php endif; $TemplatWaras1->close() // pgScript ?>

<?php if($available): $TemplatWaras1->open('content') // available ?>
	<div id='progis_data_content'>
		<div>
			<strong>
				<i class='fa fa-file-text-o margin-r-5'></i> &nbsp; Notes
			</strong>
			<ul>
				<li>untuk melakukan proses import data : pilih terlebih dahulu data yang akan di import, jika belum ada file export maka download dulu export filenya, setelah itu isi file bisa di ubah data tahunannya lalu di import kembali.</li>
			</ul>
		</div>

		<div class='progis-option' id='progis-option'>
			<?php if ($option_kabupaten): ?>
				<!-- BERDASARKAN KABUPATEN -->
				<div class='option-content'>
					<h4><i class='fa fa-map-o'></i> &nbsp; Pilihan Distrik</h4>
					<form method='POST' class='form-horizontal' id='frm_data_kebupaten'>
						<div class='col'>
							<div class='input-group'>
								<select class='form-control' id='select_skpdinstansi'>
									<option value=0>-- PILIH DISTRIK --</option>
									<?php if ($TemplatWaras1->get_root()->userAkses == 'instansi'): ?>
										<option value='-1'>Semua Kabupaten</option>
									<?php endif ?>
									<?php foreach ($option_kabupaten->result() as $buffer): ?>
										<option value="<?= $buffer->kodepemda ?>"><?= $buffer->kabupaten ?></option>
									<?php endforeach ?>
								</select>
								<span class='input-group-btn'>
									<button type='button' class='btn btn-success btn-flat btn_load_data_kabupaten'>
										<i class='fa  fa-play'></i> &nbsp; Lihat Data
									</button>
								</span>	
							</div>
						</div>
					</form>
		 		</div>
			<?php endif // kabupaten ?>

			<?php if ($option_skpd): ?>
				<!-- BERDASARKAN SKPD/INSTANSI -->
				<div class='option-content'>
					<h4><i class='fa fa-user'></i> &nbsp; Pilihan SKPD</h4>
					<form method='POST' class='form-horizontal' id='frm_progis_skpd'>
						<div class='col'>
							<div class='input-group'>
								<select class='form-control' id='select_skpdinstansi'>
									<option value=0>-- PILIH SKPD PENGENTRI --</option>
									<?php
									$matrix_many = $matrix_zero = true;
									foreach ($option_skpd->result() as $buffer): ?>
										<?php if ($matrix_many && $buffer->matrix > 0): $matrix_many = false; ?>
											<option disabled> -- SUDAH MEMILIKI DATA -- </option>
										<?php endif ?>
										<?php if ($matrix_zero && $buffer->matrix < 1): $matrix_zero = false; ?>
											<option disabled> -- BELUM MEMILIKI DATA -- </option>
										<?php endif ?>
										<option value="<?= $buffer->idinstansi ?>"><?= $buffer->nama_instansi ?></option>
									<?php endforeach ?>
								</select>
								<span class='input-group-btn'>
									<button type='button' class='btn btn-warning btn-flat btn_load_data_skpd_instansi'>
										<i class='fa fa-play'></i> &nbsp; Lihat Data
									</button>
								</span>	
							</div> 
						</div>	
					</form>
				</div>
			<?php endif // instansi ?>

			<?php if ($use_opsi_kelompok): ?>
				<!-- BERDASARKAN KELOMPOK DATA -->
				<div class='option-content' hidden>
					<h4><i class='fa fa-align-justify'></i> &nbsp; Pilih! Berdasarkan Jenis Data, kelompok dan Sub Kelompok Data</h4>
					<form method='POST' class='form-horizontal' id='frm_progis_option'>
						<div class='col col-sm-6 col-xs-12'>
							<div class='form-group'>
								<select class='form-control' id='select_jenis'>
									<option value=0>-- PILIH JENIS --</option>
									<?php foreach ($option_jenis->result() as $buffer): ?>
										<option value="<?= $buffer->idkelompok ?>"><?= $buffer->urai ?></option>
									<?php endforeach ?>
								</select>
							</div>
						</div>

						<div class='col'>
							<div class='input-group col col-sm-6 col-xs-12'>
								<select class='form-control' id='select_kelompok'>
									<option value=0>-- KELOMPOK DATA --</option>
								</select>
								<span class='input-group-btn'>
									<button type='button' class='btn btn-info btn-flat btn_load_data'>
										<i class='fa fa-play'></i> &nbsp; Lihat Data
									</button>
								</span>
							</div> 		
						</div>

						<div class='col sub_kelompok'>
							<div class='input-group'>
								<select class='form-control' id='select_subkelompok1'><option>0</option></select>
								<span class='input-group-btn'>
									<button type='button' class='btn btn-info btn-flat btn_load_data'>
										<i class='fa fa-play'></i> &nbsp; Lihat Data
									</button>
								</span>
							</div>
						</div>

						<div class='col sub_kelompok'>
							<div class='input-group'>
								<select class='form-control' id='select_subkelompok2'><option>0</option></select>
								<span class='input-group-btn'>
									<button type='button' class='btn btn-info btn-flat btn_load_data'>
										<i class='fa fa-play'></i> &nbsp; Lihat Data
									</button>
								</span>	
							</div>
						</div>

						<div class='col sub_kelompok'>
							<div class='input-group'>
								<select class='form-control' id='select_subkelompok3'><option>0</option></select>
								<span class='input-group-btn'>
									<button type='button' class='btn btn-info btn-flat btn_load_data'>
										<i class='fa fa-play'></i> &nbsp; Lihat Data
									</button>
								</span>
							</div>
						</div>
					</form>
				</div>
			<?php endif // kelompok ?>

			<?php if ($show_table && $tahun_data && $tahun_list): ?>
				<div class='form-group option-content'>
					<form id='frm_tahun' name='frm_tahun'>
						<i class='fa  fa-calendar'></i> &nbsp; Pilihan Tahun
						<select id='tahun_awal'>
							<?php for ($i = $tahun_list['max']; $i >= $tahun_list['min']; $i--): ?>
								<option <?= ($i == $tahun_data['min'] ? 'selected' : '') ?>><?= $i ?></option>
							<?php endfor ?>
						</select>
						<span> s/d </span>
						<select id='tahun_akhir'>
							<?php for ($i = $tahun_list['max']; $i >= $tahun_list['min']; $i--): ?>
								<option <?= ($i == $tahun_data['max'] ? 'selected' : '') ?>><?= $i ?></option>
							<?php endfor ?>
						</select>
					</form>
				</div>
			<?php endif // tahun ?>
		</div>
	</div>

	<?php if ($show_table): ?>
		<div class='box-header with-border no-margin'>
			<h4 class='box-title'>
				<i class='fa fa-table'></i>
				<span class='table-title'>Data Indikator Distrik</span>
			</h4>
			<div class='ie_option box-tools pull-right'>
				<!-- {ajax:$tableData['opsidata']} -->
			</div>
		</div>
		<div class="table-responsive">
			<table id='table_kelompok_input' class ='detail_data table-striped' border='0' cellpadding='0' cellspacing='0' width='100%'>
				<thead><!-- {ajax:$tableData['header']} --></thead>
				<tbody><!-- {ajax:$tableData['body']} --></tbody>
			</table>
		</div>
	<?php endif // table ?>

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

<?php else: $TemplatWaras1->open('content') // available ?>
	<div id='progis_data_content'>
		<strong>
			<i class='fa fa-file-text-o margin-r-5'></i> &bnsp; Catatan
		</strong>
		<ul>
			<li class='text-red'>
				<i class='fa fa-warning'></i>
				Indikator/Data Distrik yang berhubungan dengan SKPD anda tidak tersedia.
			</li>
		</ul>
	</div>

<?php endif; $TemplatWaras1->close() // available ?>
