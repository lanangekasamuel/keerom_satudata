<?php
$TemplatWaras1 =& TemplatWaras1::init();
// $this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();

?>

<?php $TemplatWaras1->open('content') ?>
<div id='progis_data_content'>
	<strong>
		<i class='fa fa-file-text-o margin-r-5'></i> &nbsp; Notes
	</strong>
	<ul>
		<li>
			<span>Untuk melakukan proses import data : </span>
			<ul>
				<li>pilih terlebih dahulu data yang akan di import,</li>
				<li>jika belum ada file export maka download dulu dengan meng-export data,</li>
				<li>setelah itu isi file bisa di ubah data tahunan-nya lalu di import kembali.</li>
			</ul>
		</li>
	</ul>

	<div class='progis-option no-print' id='progis-option'>
		<?php if ($seleksi_instansi): ?>
			<!-- BERDASARKAN SKPD/INSTANSI -->
			<div class='option-content'>
				<h4>
					<i class='fa fa-user'></i> &nbsp; Pilihan SKPD
				</h4>
				<form method='POST' class='form-horizontal' id='frm_progis_skpd'>
					<div class='col'>
						<div class='input-group'>
							<select class='form-control' id='select_skpdinstansi'>
								<option value=0>-- PILIH SKPD PENGENTRI --</option>
								<?php
								$matrix_many = $matrix_zero = true;
								foreach ($seleksi_instansi->result() as $buffer): ?>
									<?php if ($matrix_many && $buffer->matrix > 0): $matrix_many = false; ?>
										<option disabled> -- SUDAH MEMILIKI DATA -- </option>
									<?php endif ?>
									<?php if ($matrix_zero && $buffer->matrix < 1): $matrix_zero = false; ?>
										<option disabled> -- BELUM MEMILIKI DATA -- </option>
									<?php endif ?>
									<option value="<?= $buffer->idinstansi ?>" <?= ($selected_instansi == $buffer->idinstansi ? 'selected' : '') ?>><?= $buffer->nama_instansi ?></option>
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
		<?php endif // seleksi_instansi ?>

		<!-- tahun -->
		<div class='option-content'>
			<div>
				<h4>
					<i class='fa  fa-calendar'></i> &nbsp; Pilihan Tahun
				</h4>
			</div>
			<div class='form-group'>
				<form id='frm_tahun' name='frm_tahun'>
					<div class='col col-md-2'>
						<select class='form-control' id='tahun_awal'>
							<?php for ($i = $tahun_list['max']; $i >= $tahun_list['min']; $i--): ?>
								<option <?= ($i == $tahun_data['min'] ? 'selected' : '') ?>><?= $i ?></option>
							<?php endfor ?>
						</select>
					</div>
					<div class='col col-md-2'>
						<select class='form-control' id='tahun_akhir'>
							<?php for ($i = $tahun_list['max']; $i >= $tahun_list['min']; $i--): ?>
								<option <?= ($i == $tahun_data['max'] ? 'selected' : '') ?>><?= $i ?></option>
							<?php endfor ?>
						</select>
					</div>
					<span class='input-group-btn'>
						<button type='button' class='btn btn-success btn-flat btn_load_by_tahun'>
							<i class='fa fa-play'></i> &nbsp; Lihat Data
						</button>
					</span>
				</form>
			</div>
		</div>
	</div>

	<div class='table-responsive'>
		<div class='box-header with-border no-margin'>
			<h4 class='box-title'>
				<i class='fa fa-table'></i> &nbsp; Data Indikator SKPD
			</h4>
			<div class='ie_option box-tools pull-right'><?= $tableData['opsidata'] ?></div>
		</div>
		<table id='table_kelompok_input' class ='detail_data table-condensed-side' border='0' cellpadding='0' cellspacing='0' width='100%'>
			<thead><?= $tableData['header'] ?></thead>
			<tbody><?= $tableData['body'] ?></tbody>
		</table>
	</div>
</div>

<!-- Modal -->
<div id="chartModal" class="modal fade" role="dialog">
  <div class="modal-dialog">
  	<div class="modal-content">
  		<div class="modal-header">
  			<button type="button" class="close" data-dismiss="modal">&times;</button>
  			<h4 class="modal-title">Chart</h4>
  		</div>
  		<div class="modal-body" id="modal_content"></div>
  		<div class="modal-footer">
  			<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
  		</div>
  	</div>
  </div>
</div>
<?php $TemplatWaras1->close() ?>
