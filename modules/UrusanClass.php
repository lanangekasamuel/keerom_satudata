<?php
Class UrusanClass extends ModulClass{

	/**
	 * modul kelompok
	 * digunakan untuk manipulasi tabel kelompok
	 * tabel2 yg bersangkutan :
	 * kelompok : idkelompok, idparent, urai, satuan, formula, ordering, penggunaan, iduser, latsupdate
	 * urusan_bidang : kode_urusan, kode_suburusan, kode_organisasi, urai
	 * suburusan_bidang : idsub_urusan, kode_urusan, kode_suburusan, sub_urusan
	 **/
	
	function buildForm(){
		// edit add form
		$id = $this->db->escape_string($_GET['id']);
		$idparent = $this->db->escape_string($_GET['idparent']);

		list($kode_urusan,$kode_suburusan,$idsub_urusan) = explode('_',$id); //extract id
		list($kode_urusan_parent,$kode_suburusan_parent) = explode('_',$idparent); //extract id

		if ($this->hasAksesOnUrusan()) {			
			$theme_file = "urusan.html";// pilih theme

			if($idsub_urusan <> ''){
				$sql = "SELECT sb.*,sb.sub_urusan as urai,u1.urai as parent FROM suburusan_bidang sb 
						JOIN urusan u1 ON u1.kode_urusan = sb.kode_urusan 
							AND u1.kode_suburusan = sb.kode_suburusan
						WHERE sb.idsub_urusan='".$this->scr->filter($idsub_urusan)."'";
			} else if ($kode_suburusan <> '') {
				$sql = "SELECT u.*,u1.urai as parent FROM urusan u
						JOIN urusan u1 ON u1.kode_urusan = u.kode_urusan 
							AND u1.kode_suburusan = ''
						WHERE u.kode_urusan='".$this->scr->filter($kode_urusan)."' 
							AND u.kode_suburusan='".$this->scr->filter($kode_suburusan)."'";
			} else if ($kode_urusan <> '') {
				$sql = "SELECT u.* FROM urusan u
						WHERE kode_urusan='".$this->scr->filter($kode_urusan)."' 
							AND kode_suburusan=''";
			}
			// die($sql);

			if (empty($idparent)) {
				$result = $this->db->query($sql);
				$data = $this->db->fetchAssoc($result);
				$action ='upd';
				$status ='edit';
			} else {
				$sql_parent = "SELECT *,urai as parent,'' as urai FROM urusan 
						WHERE kode_urusan='".$this->scr->filter($kode_urusan_parent)."' 
							AND kode_suburusan='".$this->scr->filter($kode_suburusan_parent)."'";
				$result_parent = $this->db->query($sql_parent);
				$data = $this->db->fetchAssoc($result_parent);
				$action ='ins';
				$status ='tambah';				
			}

			#build form
			$this->title = 'Urusan';

			$define = array ( 
							'info'		=> $info,
							'parent'	=> $data['parent'], 
							'urusan'	=> $data['urai'], 
							'id' 		=> $id,
							'kode_urusan' 		=> $data['kode_urusan'],
							'kode_suburusan' 	=> $data['kode_suburusan'],
							'idsub_urusan' 		=> $data['idsub_urusan'],
							'status' 	=> $status,
							'rootdir' 	=> ROOT_URL,
							'action' 	=> $action
							 );

			if (isset($_GET['ajaxOn'])) {
				$define['ajax_var'] = 'id="ajaxOn" name="ajaxOn"'; // untuk handling asal update
			}

			$tplform = new TemplateClass;
			$tplform->init(THEME.'/forms/'.$theme_file);
			$tplform->defineTag($define);	
			$form = $tplform->parse();	

			$this->pgScript = "
			<script src='{themepath}js/urusan.js'></script>
			";
			$message = 'load berhasil';
		} else {
			$message = 'tidak ada akses ke urusan!'.ERROR_TAG;
		}

		if (isset($_GET['ajaxOn'])) {
			$json_data = array(
				'content' => $form,
				'message' => $message,
				);
			$jason = json_encode($json_data);
			die ($jason);
		} else {
			return $form; 
		}
	}
	// sub function from insert
	function getLastedChild($kode_urusan,$kode_suburusan='') {
		// cek anak terakhirnya, sampai kecucu2-nya tidak ditemukan child lagi
		if (!empty($kode_suburusan)) {
			$sql_lastchild = "SELECT DISTINCT(idsub_urusan) FROM suburusan_bidang 
							WHERE kode_urusan = '".$kode_urusan ."' AND
								kode_suburusan = '".$kode_suburusan."' 
							ORDER BY idsub_urusan DESC LIMIT 1;";
			$res_lastchild = $this->db->query($sql_lastchild);
			$lastchilddata = $this->db->fetchAssoc($res_lastchild);

			if (!empty($lastchilddata['idsub_urusan'])) {
				return $kode_urusan."_".$kode_suburusan."_".$lastchilddata['idsub_urusan'];
			} else {
				return $kode_urusan."_".$kode_suburusan;
			}
		} else if (!empty($kode_urusan)) {
			$sql_lastchild = "SELECT DISTINCT(kode_suburusan) FROM urusan_bidang 
							WHERE kode_urusan = '".$kode_urusan ."' 
							ORDER BY kode_suburusan DESC LIMIT 1;";
			$res_lastchild = $this->db->query($sql_lastchild);
			$lastchilddata = $this->db->fetchAssoc($res_lastchild);

			if (!empty($lastchilddata['kode_suburusan'])) {
				return $this->getLastedChild($kode_urusan,$lastchilddata['kode_suburusan']);
			} else {
				return $kode_urusan;
			}
		}
	}
	function Insert(){
		# query insert 
		// cek akses kekelompok
		// $idparent = $this->scr->filter($_POST['idparent']);
		if ($this->hasAksesOnUrusan()) {
			$kode_urusan 	= $this->scr->filter($_POST['kode_urusan']);
			$kode_suburusan = $this->scr->filter($_POST['kode_suburusan']);

			if (!empty($kode_suburusan)) {
				// suburusan_bidang
				// $result	= $this->db->query("SELECT max(idsub_urusan) as mxid FROM suburusan_bidang 
				// 				WHERE kode_urusan = '".$kode_urusan."' 
				// 					AND kode_suburusan = '".$kode_suburusan."'");				
				$result	= $this->db->query("SELECT max(idsub_urusan) as mxid FROM suburusan_bidang");
				$dord	= $this->db->fetchArray($result);
				$maxid  = ($dord['mxid'] <> '')?$dord['mxid'] +1 : 1;	

				$sql = "INSERT suburusan_bidang 
						SET idsub_urusan = '".$maxid."',
							sub_urusan = '".$this->scr->filter($_POST['urusan'])."',
							kode_urusan = '".$kode_urusan."',
							kode_suburusan = '".$kode_suburusan."'";

				// ditambahkan row setelah id sub urusan
				$data['after_idurusan'] = $this->getLastedChild($kode_urusan,$kode_suburusan);
				$dataUrusan = $_POST;
				$dataUrusan['numbering'] = '-';
				$dataUrusan['idsub_urusan'] = $maxid;
				$dataUrusan['sub_urusan'] = $_POST['urusan'];
				$row_content = $this->rowData('sub_urusan',$dataUrusan);

			} else if (!empty($kode_urusan)) {
				// urusan_bidang
				$result	= $this->db->query("SELECT max(kode_suburusan) as mxid FROM urusan_bidang 
								WHERE kode_urusan = '".$kode_urusan."'");
				$dord	= $this->db->fetchArray($result);
				$maxid  = ($dord['mxid'] <> '')?$dord['mxid'] +1 : 1;
				$maxid 	= sprintf("%'.02d",$maxid);	

				$sql = "INSERT urusan_bidang 
						SET kode_organisasi = '0',
							urai = '".$this->scr->filter($_POST['urusan'])."',
							kode_urusan = '".$kode_urusan."',
							kode_suburusan = '".$maxid."'";

				// ditambahkan row setelah id urusan
				$data['after_idurusan'] = $this->getLastedChild($kode_urusan);
				$dataUrusan = $_POST;
				$dataUrusan['numbering'] = '-';
				$dataUrusan['kode_suburusan'] = $maxid;
				$dataUrusan['urai'] = $_POST['urusan'];
				$row_content = $this->rowData('urusan',$dataUrusan);
			}		

			// die($sql.print_r($data,1).$row_content);
			$insertQuery = $this->db->query($sql);

			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$message = ($insertQuery) 
					? 'penambahan urusan sukses' 
					: 'penambahan urusan gagal'.ERROR_TAG;

				$json_data = array(
					'message' 	=> $message,
					'data'		=> $data, //<- digunakan untuk insert data di tabel
					'row_content' => $row_content,
					);

				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/urusan'>");				
			}
		} else {
			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$json_data = array(
					'message' => 'akses ke urusan ditolak'.ERROR_TAG
					);
				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/urusan'>");				
			}
		}	 
	}
	function Update(){
		# query update 
		// cek akses urusan
		if ($this->hasAksesOnUrusan()) {
			$kode_urusan 	= $this->scr->filter($_POST['kode_urusan']);
			$kode_suburusan = $this->scr->filter($_POST['kode_suburusan']);
			$idsub_urusan 	= $this->scr->filter($_POST['idsub_urusan']);

			if (!empty($idsub_urusan)) {
				$sql = "UPDATE suburusan_bidang 
						SET sub_urusan = '".$this->scr->filter($_POST['urusan'])."'
						WHERE 
							kode_urusan = '".$kode_urusan."' AND
							kode_suburusan = '".$kode_suburusan."' AND
							idsub_urusan = '".$idsub_urusan."'";
			} else if (!empty($kode_suburusan)) {
				$sql = "UPDATE urusan_bidang 
						SET urai = '".$this->scr->filter($_POST['urusan'])."'
						WHERE 
							kode_urusan = '".$kode_urusan."' AND
							kode_suburusan = '".$kode_suburusan."'";
			} else if (!empty($kode_urusan)) {
				$sql = "UPDATE urusan_bidang 
						SET urai = '".$this->scr->filter($_POST['urusan'])."'
						WHERE 
							kode_urusan = '".$kode_urusan."' AND
							kode_suburusan = ''";
			}		

			// die($sql);
			$updateQuery = $this->db->query($sql);

			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$message = ($updateQuery) 
					? 'update urusan sukses' 
					: 'update urusan gagal'.ERROR_TAG;

				$data = $_POST;			
				$json_data = array(
					'message' 	=> $message,
					'data'		=> $data, //<- digunakan untuk update data di tabel
					);

				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/urusan'>");				
			}
		} else {
			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$json_data = array(
					'message' => 'akses ke indikator ditolak'.ERROR_TAG
					);
				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/urusan'>");				
			}
		}	 
	}
	function Delete(){
		# query delete 
		// yg bisa dihapus cuma bidang dan sub_urusan
		$id = $this->db->escape_string($_GET['id']);
		list($kode_urusan,$kode_suburusan,$idsub_urusan) = explode('_',$id); //extract id

		if ($this->hasAksesOnUrusan()) {
			
			if($idsub_urusan <> ''){
				$sql = "DELETE FROM suburusan_bidang
						WHERE 
						kode_urusan='".$this->scr->filter($kode_urusan)."'
						AND kode_suburusan='".$this->scr->filter($kode_suburusan)."'
						AND idsub_urusan='".$this->scr->filter($idsub_urusan)."'
						";
				$sql_child = "SELECT * FROM kelompok WHERE idsub_urusan='".$this->scr->filter($idsub_urusan)."'";
			} else if ($kode_suburusan <> '') {
				$sql = "DELETE FROM urusan_bidang
						WHERE 
						kode_urusan='".$this->scr->filter($kode_urusan)."'
						AND kode_suburusan='".$this->scr->filter($kode_suburusan)."'";
				$sql_child = "SELECT * FROM suburusan_bidang 
						WHERE 
						kode_urusan='".$this->scr->filter($kode_urusan)."'
						AND kode_suburusan='".$this->scr->filter($kode_suburusan)."'";
			} 

			$res_child = $this->db->query($sql_child);
			$num_child = $this->db->numRows($res_child);
			// die ($sql.$num_child);
			if ($num_child > 0) {
				$message = 'urusan ini masih memiliki sub urusan atau kelompok indikator yang terhubung, <br>untuk menghapus urusan ini, silakan menghapus dulu sub urusannya / hubungan dengan indikatornya'.ERROR_TAG;
			} else {
				$deleteQuery = $this->db->query($sql);
				$message = ($deleteQuery) 
					? 'menghapus urusan sukses' 
					: 'menghapus urusan gagal'.ERROR_TAG;
			}

			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				$json_data = array(
					'message' 	=> $message,
					);
				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/urusan'>");				
			}
		} else {
			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				$json_data = array(
					'message' => 'akses ke indikator ditolak'.ERROR_TAG
					);
				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/urusan'>");				
			}
		}	
	}
	function Manage(){
		# grid & manajemen data
		$this->title 	= 'Urusan, Bidang Urusan dan Sub Urusan';		
		$this->pgScript = '
		<script src="{themepath}js/urusan.js"></script>
		<script src="{themepath}plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
		<script src="{themepath}plugins/bootstrap-tagsinput/bootstrap3-typeahead.js"></script>
		<link rel="stylesheet" href="{themepath}plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
		<link rel="stylesheet" href="{themepath}css/urusan.css">
		';

		# table daftar kelompok sesuai akses
		$tData = $this->_loadTableUrusan();
		$table = "
 		<table id='table_urusan' class ='detail_data table-striped' border='0' cellpadding='0' cellspacing='0' width='100%'>
 		<thead>
 		<tr>
				<th>No</th>
				<th>Kode Urusan</th>
				<th>Kode Bidang</th>
				<th>Kode Sub Urusan</th>
				<th>Urusan / Bidang Urusan / Sub Urusan</th>
				<th>Aksi</th>
		</tr>
		</thead>
		<tbody>
		{$tData}
		</tbody></table>
 		";

 		$modal .= '<!-- Edit/Add Modal -->
		<div id="commonModal" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-md">

		    <!-- Modal content-->
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal">&times;</button>
		        <h4 class="modal-title"><i class="fa fa-desktop"></i></h4>
		      </div>
		      <div class="modal-body" id="modal_content">
		      {}
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      </div>
		    </div>

		  </div>
		</div>';

		$content = "

		<div id='progis_data_content'>
 		<strong><i class='fa fa-file-text-o margin-r-5'></i> Notes</strong>
 		<ul>
 		<li>Urusan utama tidak bisa ditambahkan, jika akan menambahkan.. edit urusan utama yang kosong uraian-nya</li>
 		<li>Bidang urusan ditambahkan melalui Urusan Utama </li>
 		<li>Bidang urusan yang memiliki sub urusan tidak bisa dihapus tanpa menghapus sub urusannya</li>
 		<li>Sub urusan ditambahkan melalui bidang </li>
 		<li>Sub urusan yang memiliki indikator tidak bisa dihapus tanpa menghapus hubungannya dengan indikator</li>
 		</ul>
 		<div class='progis-option' id='progis-option'>

		<!-- BERDASARKAN KELOMPOK DATA
 		<div class='option-content'>
 		<div><h4><i class='fa fa-align-justify'></i>&nbsp; Pilih! Berdasarkan Jenis Data, kelompok dan Sub Kelompok Data</h4></div>
 		<form method='POST' class='form-horizontal' id='frm_progis_option'>

	 	</form>
 		</div>

 		</div> -->

 		<div class='box-header with-border no-margin'>
              <h4 class='box-title'><i class='fa fa-table'></i>&nbsp; <span>Tabel 1.0 : Urusan, Bidang urusan dan Sub Urusan</span>
              </h4>

              <div class='ie_option box-tools pull-right'>
                
              </div>
        </div>

 		{$table}

		<!--
			[anovedit][workaround][note:] saya sedang malas utak-atik `urusan`,
			jadinya tombol aksi saya sembunyikan, serta `No.` ndak guna, jadi juga saya sembunyikan.
		-->
 		<style>
 		#table_urusan tr > td:last-child,
 		#table_urusan tr > th:last-child,
 		#table_urusan tr > td:first-child,
 		#table_urusan tr > th:first-child {display: none;}
 		</style>

 		</div>

 		{$modal}
 		";

		$this->content = $content;
	}
	function FrontDisplay(){
		# tampilan depan
	}
	function FrontList(){
		# daftar 
	}
	function GetDetail($id){
		# detail 
	}
	function getJSON($id){
		# ajax handler
		// $jmode = $_GET['ajaxmode'];
		// switch ($jmode) {	
		// 	case 'xxx':
				
		// 	break;
		// 	default:
		// 		# code...
		// 	break;
		// }
	}
	function cekAkses() {
		/* AKSES KELOMPOK 
		 * uraikan berdasarkan akses, 1:admin, 2:operator 3:skpd, 4:instansi_vertikal\
		 */
		// $datausr = $this->auth->getDetail();
		// $Qgroup = $this->db->query('SELECT * FROM `group` WHERE idgroup='.$datausr['idgroup']);
		// $dataGroup = $this->db->fetchAssoc($Qgroup);
		// // print_r($datausr);
		// if ($datausr['idgroup'] == 1) {
		// 	$this->userAkses = 'admin';
		// } else if ($datausr['idgroup'] == 2) {
		// 	$this->userAkses = 'instansi';
		// 	$sqlInstansi = 'SELECT * FROM instansi AS i 
		// 			LEFT JOIN users AS u ON u.`idinstansi` = i.`idinstansi` 
		// 			WHERE u.`iduser`='.$datausr['iduser'];
		// 	$qInstansi = $this->db->query($sqlInstansi);
		// 	$this->activeInstansi = $this->db->fetchAssoc($qInstansi);
		// } else if ($datausr['idgroup'] == 3) {
		// 	$this->userAkses = 'bidang';
		// 	// $this->activeBidang = $this->db->fetchAssoc($qInstansi);
		// }
	}
	function hasAksesOnUrusan(){
		/** cek akses user ke urusan
		 */

		$datausr 	= $this->auth->getDetail();
		$hasAkses = false;
		if ($datausr['idgroup'] == 1) {
			//akses admin, all granted
			$hasAkses = true;
		} else {
			$hasAkses = false;
		}
		return $hasAkses;
	}

	private function _loadTableUrusan(){
		//load dataTabale untuk ditampilkan
		$sqlUrusan = "SELECT * FROM `urusan` ub ORDER BY kode_urusan,kode_suburusan";
	 	$resUrusan = $this->db->query($sqlUrusan);

 		$this->tblNumbering = 0;
 		$tData = "";
	 	while ($dataUrusan = $this->db->fetchAssoc($resUrusan)) {
	 		$this->tblNumbering++;
	 		$dataUrusan['numbering'] = $this->tblNumbering;

	 		// sub urusan
	 		$dataSub = "";
	 		$this->_loadTableSuburusan($dataUrusan['kode_urusan'],$dataUrusan['kode_suburusan'],$dataSub);
	 		$tData .= $this->rowData("urusan",$dataUrusan).$dataSub;
	 	}

	 	return $tData;
	}

	private function _loadTableSuburusan($kode_urusan,$kode_suburusan,&$returnVar) {
		// load datatable untuk sub urusan
		$sqlSubUrusan = "SELECT * FROM `suburusan_bidang` sub
					JOIN `urusan` ub ON ub.kode_urusan = sub.kode_urusan 
						AND ub.kode_suburusan = sub.kode_suburusan
					WHERE sub.kode_urusan = ".$this->scr->filter($kode_urusan)."
						AND sub.kode_suburusan = ".$this->scr->filter($kode_suburusan)."
	 				ORDER BY sub.idsub_urusan";
	 				;

	 	$resSubUrusan = $this->db->query($sqlSubUrusan);
	 	while ($dataSubUrusan = $this->db->fetchAssoc($resSubUrusan)) {
	 		$this->tblNumbering++;
	 		$dataSubUrusan['numbering'] = $this->tblNumbering;
			$returnVar .= $this->rowData("sub_urusan",$dataSubUrusan);
	 	}
	}

  	private function rowData($type='urusan',$dataUrusan) { 
  		// type : urusan=bidang/sub_urusan

	  	// coloring row
	  	$bg_row = '';
	  	if ($_POST['cntmode'] == 'ins') {$bg_row = 'bg-info text-blue';}

	  	if ($type == 'urusan' || $type == 'bidang') {
		 		// row class
				$row_class = ($dataUrusan['kode_suburusan'] == '') ? 'urusan' : 'bidang' ;

				// iddata for js-ajax
				$iddata = $dataUrusan['kode_urusan'];
				$iddata .= ($dataUrusan['kode_suburusan'] <> '') ? "_".$dataUrusan['kode_suburusan'] : '' ;

	  		if ($dataUrusan['kode_suburusan'] == '' && $dataUrusan['kode_urusan'] <> 1) {
					// urusan
					$action = '
		  			<button type="button" title="edit urusan" data-toggle="tooltip" data-placement="top" class="btn-flat-info" onClick="editUrusan(\''.$iddata.'\')"><i class="fa fa-edit"></i></button>
		  			<button type="button" title="tambah bidang urusan" data-toggle="tooltip" data-placement="top"  class="btn-flat-info" onClick="addUrusan(\''.$iddata.'\')"><i class="fa fa-plus"></i></button>
		  		';
				} else if($dataUrusan['kode_urusan'] <> 1) {
					// bidang urusan
					$action = '
		  			<button type="button" title="Edit Bidang Urusan" data-toggle="tooltip" data-placement="top"  class="btn-flat-info" onClick="editUrusan(\''.$iddata.'\')"><i class="fa fa-edit"></i></button>
		  			<button type="button" title="Tambah Sub Urusan" data-toggle="tooltip" data-placement="top"  class="btn-flat-info" onClick="addUrusan(\''.$iddata.'\')"><i class="fa fa-plus"></i></button>
		  			<button type="button" title="Hapus Bidang Urusan" data-toggle="tooltip" data-placement="top"  class="btn-flat-info" onClick="removeUrusan(\''.$iddata.'\')"><i class="fa fa-minus"></i></button>
		  		';
				}


				// [anovedit] LZERO
				$dataUrusan['kode_urusan'] = str_pad($dataUrusan['kode_urusan'], 2, 0, STR_PAD_LEFT);

	  		$return = "<tr id='urusan_".str_replace('.','_',$iddata)."' class='{$row_class} {$bg_row}'>
				<td>{$dataUrusan['numbering']}</td>
				<td>{$dataUrusan['kode_urusan']}</td>
				<td>{$dataUrusan['kode_suburusan']}</td>
				<td></td>
				<td nowrap class='urusan'>{$dataUrusan['urai']}</td>
				<td nowrap>{$action}</td>
				</tr>";
			} else if ($type == 'sub_urusan') {
				// iddata for js-ajax
				$dataSubUrusan = $dataUrusan;
				$iddata = $dataSubUrusan['kode_urusan']."_".$dataSubUrusan['kode_suburusan']."_".$dataSubUrusan['idsub_urusan'];

		 		$action = '
		  			<button type="button" title="edit Sub urusan" data-toggle="tooltip" data-placement="top"  class="btn-flat-info" onClick="editUrusan(\''.$iddata.'\')"><i class="fa fa-edit"></i></button>
		  			<button type="button" title="hapus Sub Urusan" data-toggle="tooltip" data-placement="top"  class="btn-flat-info" onClick="removeUrusan(\''.$iddata.'\')"><i class="fa fa-minus"></i></button>
		  		';

	  		// [anovedit] LZERO
		  	$dataSubUrusan['kode_urusan'] = str_pad($dataSubUrusan['kode_urusan'], 2, 0, STR_PAD_LEFT);
		  	$dataSubUrusan['idsub_urusan'] = str_pad($dataSubUrusan['idsub_urusan'], 2, 0, STR_PAD_LEFT);

	  		$return = "<tr id='urusan_".str_replace('.','_',$iddata)."' class='sub_urusan {$bg_row}'>
		 		<td>{$dataSubUrusan['numbering']}</td>
				<td>{$dataSubUrusan['kode_urusan']}</td>
				<td>{$dataSubUrusan['kode_suburusan']}</td>
				<td>{$dataSubUrusan['idsub_urusan']}</td>
				<td nowrap class='urusan'>{$dataSubUrusan['sub_urusan']}</td>
				<td nowrap>{$action}</td>
		 		</tr>";
			}

			return $return;
  	}

}