<?php
Class KonversiClass extends ModulClass{

	/**
	 * modul kelompok
	 * digunakan untuk manipulasi tabel kelompok
	 * tabel2 yg bersangkutan :
	 * kelompok : idkelompok, idparent, urai, satuan, formula, ordering, penggunaan, iduser, latsupdate
	 * penggunaan_indikator : idpenggunaan,penggunaan
	 * suburusan_bidang : idsub_urusan, sub_urusan
	 **/
	
	function buildForm(){
		// edit add form
		$this->cekAkses();
		$idkelompok = $this->db->escape_string($_GET['id']);
		$idparent 	= $this->db->escape_string($_GET['idparent']);
		$haskakses 	= $this->hasAksesOnKelompok($idkelompok);
		$haskakses_parent= $this->hasAksesOnKelompok($idparent);

		if ($haskakses || $haskakses_parent) {			
			// pilih theme
			$datausr = $this->auth->getDetail();
			if ($datausr['idgroup'] == 1) {
				$theme_file = "kelompok.html";
			} else if ($datausr['idgroup'] == 2) {
				$theme_file = "kelompok_instansi.html";
			}

			if($idkelompok <> ''){
				// kelompok memiliki author/user
				$sql = "SELECT k.*,ka.idkelompok AS parent_id,ka.urai AS parent_urai,
							u.`idinstansi`, u.`idbidang_instansi` 
						FROM kelompok k 
						LEFT JOIN kelompok ka ON ka.idkelompok = k.idparent 
						LEFT JOIN users u ON u.iduser = k.iduser 
						WHERE 
							k.idkelompok='".$idkelompok."'";
				$result = $this->db->query($sql);
				$data = $this->db->fetchArray($result);
				$action ='upd';
				$status ='edit';
			} elseif ($idparent <> '') {
				$sql = "SELECT k.idkelompok AS parent_id,k.urai AS parent_urai,
							u.`idinstansi`, u.`idbidang_instansi` 
						FROM kelompok k 
						LEFT JOIN users u ON u.iduser = k.iduser 
						WHERE 
							k.idkelompok='".$idparent."'";
				$result = $this->db->query($sql);
				$data = $this->db->fetchArray($result);
				$action ='ins';
				$status ='tambah';
			}

			#build form
			$this->title = 'Kelompok';

			if ($this->userAkses == 'admin') {
				// opsi instansi
				$opsi_instansi = '<option value=0>--pilih Instansi--</option>';
				$insql = "SELECT DISTINCT i.* FROM `instansi` i
						JOIN users u ON u.idinstansi = i.idinstansi 
						ORDER BY i.nama_instansi ASC";
				$insres = $this->db->query($insql);
				while($indata = $this->db->fetchArray($insres)){
					$slc = ($indata['idinstansi']==$data['idinstansi'])?'selected':'';
					$opsi_instansi .= "<option value='".$indata['idinstansi']."' $slc>".$indata['nama_instansi']."</option>";
				}

				// opsi bidang (opsi berubah jika instansi berubah)
				$opsi_bidang = '<option value=0>--pilih Bidang--</option>';
				$bidsql = "SELECT * FROM `instansi_bidang` WHERE idinstansi = {$data['idinstansi']} ORDER BY bidang ASC";
				$bidres = $this->db->query($bidsql);
				while($biddata = $this->db->fetchArray($bidres)){
					$slc = ($biddata['idbidang_instansi']==$data['idbidang_instansi'])?'selected':'';
					$opsi_bidang .= "<option value='".$biddata['idbidang_instansi']."' $slc>".$biddata['bidang']."</option>";
				}

				// opsi suburusan (opsi berubah jika instansi berubah) <- mengikuti instansi
				// WHERE kode_urusan = {$data['kode_urusan']} 
				$opsi_sub_urusan = '<option value=0>--pilih Sub urusan--</option>';
				$sub_sql = "SELECT * FROM `suburusan_bidang` ORDER BY sub_urusan ASC";
				$sub_res = $this->db->query($sub_sql);
				while($subdata = $this->db->fetchArray($sub_res)){
					$slc = ($subdata['idsub_urusan']==$data['idsub_urusan'])?'selected':'';
					$opsi_sub_urusan .= "<option value='".$subdata['idsub_urusan']."' $slc>".$subdata['sub_urusan']."</option>";
				}

				// penggunaan data
				// $penggunaan = array(RKPD,RPJM,LPPD,SIPD,TANNAS,INDIKATOR KINERJA,SPM,LAKIP,LKPJ,MDGs);
				$current_penggunaan = explode(',',$data['penggunaan']);
				$cek_penggunaan = '';
				$keg_sql = "SELECT * FROM `penggunaan_indikator` ORDER BY penggunaan ASC";
				$keg_res = $this->db->query($keg_sql);
				while($kegdata = $this->db->fetchArray($keg_res)){
					$slc = (in_array($kegdata['idpenggunaan'],$current_penggunaan)) ?'checked':'';
					$cek_penggunaan .= "<div class='checkbox'><label><input name='penggunaan[]' id='penggunaan[]' type='checkbox' value='".$kegdata['idpenggunaan']."' $slc>".$kegdata['penggunaan']."</label></div>";
				}
			} else if ($this->userAkses == 'instansi') {
				// die();
			}

			$define = array ( 
							'info'		=>$info,
							'parent'	=> $data['parent_urai'], 
							'idparent'	=> $data['parent_id'], 
							'urai'		=> $data['urai'], 
							'satuan'	=> $data['satuan'], 
							'formula'   => $data['formula'],
							'opsi_instansi'	=> $opsi_instansi,
							'opsi_bidang'	=> $opsi_bidang,
							'opsi_user'		=> $opsi_user,
							'cek_penggunaan'	=> $cek_penggunaan,
							'opsi_sub_urusan'	=> $opsi_sub_urusan,
							'id' 		=> $data['idkelompok'],
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
			<script src='{themepath}js/konversi.js'></script>
			<script src='{themepath}js/jqBootstrapValidation.js'></script>
			<script>
				$(document).ready(function(){
					$('input,select,textarea,button').not('[type=submit]').jqBootstrapValidation({preventSubmit: true});
					{$js_onload}
				});
			</script>
			";
			$message = 'load berhasil';
		} else {
			$message = 'tidak ada akses ke indikator!'.ERROR_TAG;
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
	function getLastedChild($idparent) {
		// cek anak terakhirnya, sampai kecucu2-nya tidak ditemukan child lagi
		$sql_lastchild = "SELECT DISTINCT(idkelompok) FROM kelompok 
						WHERE idparent=".$idparent." 
						ORDER BY ordering DESC LIMIT 1;";
		$res_lastchild = $this->db->query($sql_lastchild);
		$lastchilddata = $this->db->fetchAssoc($res_lastchild);

		if (!empty($lastchilddata)) {
			return $this->getLastedChild($lastchilddata['idkelompok']);
		} else {
			return $idparent;
		}
	}
	function Insert(){
		# query insert 
		// cek akses kekelompok
		$idparent = $this->scr->filter($_POST['idparent']);
		$haskakses 	= $this->hasAksesOnKelompok($idparent);

		if ($haskakses) {
			// cek users berdasarkan instansi/bidang
			$idinstansi 		= $this->scr->filter($_POST['idinstansi']);
			$idbidang_instansi 	= $this->scr->filter($_POST['idbidang_instansi']);
			if ($idinstansi > 0 && $idbidang_instansi > 0) {
				// user bidang di butuhkan
				$sql_user = "SELECT * FROM users 
						WHERE
							idinstansi = '".$idinstansi."' 
							AND idbidang_instansi = '".$idbidang_instansi."'";			
			} else if ($idinstansi > 0) {
				// user instansi dibuthkan
				$sql_user = "SELECT * FROM users 
						WHERE
							idinstansi = '".$idinstansi."'
							AND idbidang_instansi IS NULL";
			}
			$res_user 		= $this->db->query($sql_user);
			$userKelompok 	= $this->db->fetchAssoc($res_user);
			$upd_user = '';
			if ($userKelompok['iduser'] > 0) {
				$upd_user = "`iduser` = '".$this->scr->filter($userKelompok['iduser'])."',";
			}

			$result		= $this->db->query("SELECT max(ordering) as mxord FROM kelompok 
				WHERE idparent='".$idparent."'");
			$dord		= $this->db->fetchArray($result);
			$ordering   = ($dord['mxord'] <> '')?$dord['mxord'] +1 : 1;	

			$result		= $this->db->query("SELECT max(idkelompok) as mxid FROM kelompok");
			$dord		= $this->db->fetchArray($result);
			$maxid   	= ($dord['mxid'] <> '')?$dord['mxid'] +1 : 1;	

			// ditambahkan row setelah id kelompok nnn
			$data['after_idkelompok'] = $this->getLastedChild($idparent);

			// penggunaan
			$upd_penggunaan = '';
			$penggunaan = implode(',', $_POST['penggunaan']);
			if (!empty($penggunaan)) {
				$upd_penggunaan = "`penggunaan` = '".$this->scr->filter($penggunaan)."',";
			}

			// tambahkan child dari kelompok
			$sql = "INSERT INTO `kelompok` 
					SET 
						`idkelompok` = '".$this->scr->filter($maxid)."',
						`idparent` = '".$this->scr->filter($_POST['idparent'])."',
						`urai` = '".$this->scr->filter($_POST['urai'])."',
						`satuan` = '".$this->scr->filter($_POST['satuan'])."',
						`ordering` = '".$this->scr->filter($ordering)."',
						`idsub_urusan` = '".$this->scr->filter($_POST['sub_urusan'])."',
						{$upd_penggunaan}
						{$upd_user}
						`formula` = '".$this->scr->filter($_POST['formula'])."'
					";			

			// die($sql);
			$updateQuery = $this->db->query($sql);

			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$message = ($updateQuery) 
					? 'penambahan kelompok/indikator sukses' 
					: 'penambahan kelompok/indikator gagal'.ERROR_TAG;

				// parsing table row content
				$tab = $_POST['tab'];
				$sql_kelompok = "SELECT k.*,i.*,b.*
						FROM kelompok k 
						LEFT JOIN users u ON u.iduser = k.iduser 
						LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
						LEFT JOIN instansi_bidang b ON b.idbidang_instansi = u.idbidang_instansi 
						WHERE idkelompok='".$this->scr->filter($maxid)."'";
				$res_kelompok = $this->db->query($sql_kelompok);
				$dataKelompok = $this->db->fetchAssoc($res_kelompok);

				// penggunaan
				$penggunaan_sql = "SELECT GROUP_CONCAT(penggunaan) as penggunaan FROM penggunaan_indikator WHERE idpenggunaan IN (".$this->scr->filter($penggunaan).")";
  				$penggunaan_qry = $this->db->query($penggunaan_sql);
  				$resPenggunaan = $this->db->fetchAssoc($penggunaan_qry);
  				$dataKelompok['penggunaan'] = $resPenggunaan['penggunaan'];

  				// subs urusan
  				$sub_sql = "SELECT * FROM suburusan_bidang WHERE idsub_urusan  = '".$this->scr->filter($_POST['sub_urusan'])."'";
	  			$sub_qry = $this->db->query($sub_sql);
	  			$resSubUrs = $this->db->fetchAssoc($sub_qry);
	  			$dataKelompok['sub_urusan'] = $resSubUrs['sub_urusan'];

				$row_content = $this->rowData($dataKelompok['idkelompok'],$dataKelompok,$tab+1,$ordering);

				// die($sql_kelompok);

				$json_data = array(
					'message' 	=> $message,
					'data'		=> $data, //<- digunakan untuk insert data di tabel
					'row_content' => $row_content,
					);

				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		} else {
			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$json_data = array(
					'message' => 'akses ke kelompok/indikator ditolak'.ERROR_TAG
					);
				$jason = json_encode($json_data);
				die ($jason);
			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		}	 
	}
	function Update(){
		# query update 
		// print_r($_POST);
		// cek akses kekelompok
		
	}
	function Delete(){
		# query delete 
		// DELETE KELOMPOK
		
	}
	function Manage(){
		# grid & manajemen data
		$this->title 	= 'Konversi Kelompok';		
		$this->pgScript = '
		<script src="{themepath}js/konversi.js"></script>
		<script src="{themepath}plugins/bootstrap-tagsinput/bootstrap-tagsinput.js"></script>
		<script src="{themepath}plugins/bootstrap-tagsinput/bootstrap3-typeahead.js"></script>
		<link rel="stylesheet" href="{themepath}plugins/bootstrap-tagsinput/bootstrap-tagsinput.css">
		<link rel="stylesheet" href="{themepath}css/kelompok.css">
		';

		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			
			$this->title .= ' : Admin';

	 		// instansi seleksi
	 		// instansi : idinstansi, instansi, singkatan_instansi 
	 		$sqlInstansi = "SELECT * FROM `instansi` ORDER BY nama_instansi ASC";
	 		$qInstansi = $this->db->query($sqlInstansi);
		  	while ($recInstansi = $this->db->fetchAssoc($qInstansi)) {
		  		$option_skpd .= "<option value='".$recInstansi['idinstansi']."'>{$recInstansi['nama_instansi']}</option>";
		  	} 		

		  	$seleksi_instansi = "
		<!-- BERDASARKAN SKPD/INSTANSI -->
		<div class='option-content'>
 		<div><h4><i class='fa fa-user'></i> &nbsp; Pilih! Berdasarkan SKPD/Instansi Terkait</h4></div> 		
 		<form method='POST' class='form-horizontal' id='frm_progis_skpd'>
 		<div class='col'>
	 		<div class='input-group'>
	 			<select class='form-control' id='select_skpdinstansi'>
	 			<option value=0>-- PILIH INSTANSI PENGENTRI --</option>
	 			{$option_skpd}
	 			</select>
	 			<span class='input-group-btn'>
	            	<button type='button' class='btn btn-warning btn-flat btn_load_kelompok_instansi'><i class='fa  fa-caret-right'></i>&nbsp;Lihat Kelompok/Indikator!</button>
	        	</span>	
	 		</div> 
 		</div>	
 		</form>
 		</div>";

		} else if ($this->userAkses == 'instansi') {

			$this->title .= ' : '.$this->activeInstansi['nama_instansi'];

			$sql_kelompok 	= "SELECT * FROM kelompok k
								JOIN `users` u ON k.iduser = u.iduser	
								WHERE urai LIKE '%".$this->db->escape_string($keyword)."%' 
								AND u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->db->escape_string($this->activeInstansi['idinstansi'])."')";

		} else if ($this->userAkses == 'bidang') {
			$sql_kelompok 	= "SELECT * FROM kelompok WHERE urai LIKE '%".$this->db->escape_string($keyword)."%'";
		} 

	 	//opsi by jenis kelompok
	 	$started_id = 0; // 4 = 8 kelp data
	 	$q_jenis = "SELECT * FROM kelompok 
	 				WHERE 
	 					idparent=".$this->db->escape_string($started_id);
	 	$res_jenis = $this->db->query($q_jenis);
		while ($recJenis = $this->db->fetchAssoc($res_jenis)) {
		  	$option_jenis .= "<option value='".$recJenis['idkelompok']."'>{$recJenis['urai']}</option>";
		}

		# table daftar kelompok sesuai akses
		$table = "
 		<table id='table_kelompok' class ='detail_data table-striped' border='0' cellpadding='0' cellspacing='0' width='100%'>
 		<thead>
 		{$tableData['header']}
		</thead>
		<tbody>
		{$tableData['body']}
		</tbody></table>
 		";

 		$modal .= '<!-- Edit/Add Modal -->
		<div id="commonModal" class="modal fade" role="dialog">
		  <div class="modal-dialog modal-lg">

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
 		<li class='text-green'>Indikator yang ada adalah yang disepakati oleh Pusdalisbang dan Instansi terkait</li>
 		<li>untuk Menambahkan Indikator untuk SIPD sebaiknya melalui pilihan jenis : 8 kelompok data -> pilih kelompok dat -> pilih kelompok, lalu menambahkan indikator/kelompok indikator berserta nama instansinya, untuk indikator dibawah indikator yang ditambahkan tadi bisa melalui pilihan instansi </li>
 		<li></li>
 		<li></li>
 		<li class='text-red'>Instansi hanya bisa mengubah wali bidang, bukan indikatornya</li>
 		<li>untuk mencari indikator bisa menggunakan pencarian dibawah ini, ketik.. dan pilihan indikator akan muncul</li>
 		</ul>
 		<div class='progis-option' id='progis-option'>

		<!-- AUTOCOMPLETE KELOMPOK -->
		<div class='option-content'>

 		<div><h4><i class='fa fa-search'></i> &nbsp; Cari Kelompok/Indikator</h4></div> 	

 		<form method='POST' class='form-horizontal' id='frm_progis_skpd'>
 		<div class='col'>
	 		<div class='input-group'>
	 			<input class='form-control' type=text name='kelompok_ac' id='kelompok_ac' placeholder='ketik uraian kelompok'>
	 			<input type=hidden name='idkelompok_search' id='idkelompok_search'>
	 			<span class='input-group-btn'>
	            	<button type='button' class='btn btn-warning btn-flat btn_load_kelompok_ac'><i class='fa  fa-caret-right'></i>&nbsp;Lihat Kelompok/Indikator!</button>
	        	</span>	
	 		</div> 
 		</div>	
 		</form>
 		</div>

 		{$seleksi_instansi}

		<!-- BERDASARKAN KELOMPOK DATA -->
 		<div class='option-content'>
 		<div><h4><i class='fa fa-align-justify'></i>&nbsp; Pilih! Berdasarkan Jenis Data, kelompok dan Sub Kelompok Data</h4></div>
 		<form method='POST' class='form-horizontal' id='frm_progis_option'>

	 	<div class='col col-sm-6 col-xs-12'>
	 		<div class='form-group'>
	 			<select class='form-control' id='select_jenis'>
	 			<option value=0>-- PILIH JENIS --</option>{$option_jenis}
	 			</select>
	 		</div>
	 	</div>

	 	<div class=''>
	 		<div class='input-group col col-sm-6 col-xs-12'>
	 			<select class='form-control' id='select_kelompok'>
	 			<option value=0>-- KELOMPOK DATA --</option>
	 			</select>
	 			<span class='input-group-btn'>
	            	<button type='button' class='btn btn-info btn-flat btn_load_kelompok'><i class='fa  fa-caret-right'></i>&nbsp;Lihat Kelompok/Indikator!</button>
	        	</span>	
	 		</div> 		
	 	</div>

	 	<div class='col sub_kelompok'>
	 		<div class='input-group'>
	 			<select class='form-control' id='select_subkelompok1'><option>0</option></select>
	 			<span class='input-group-btn'>
	            	<button type='button' class='btn btn-info btn-flat btn_load_kelompok'><i class='fa  fa-caret-right'></i>&nbsp;Lihat Kelompok/Indikator!</button>
	        	</span>
	        </div> 		
 		</div>

	 	<div class='col sub_kelompok'>
	 		<div class='input-group'>
	 			<select class='form-control' id='select_subkelompok2'><option>0</option></select>
	 			<span class='input-group-btn'>
	            	<button type='button' class='btn btn-info btn-flat btn_load_kelompok'><i class='fa  fa-caret-right'></i>&nbsp;Lihat Kelompok/Indikator!</button>
	        	</span>	
	 		</div> 
 		</div>	

	 	<div class='col sub_kelompok'>
		 	<div class='input-group'>
		 		<select class='form-control' id='select_subkelompok3'><option>0</option></select>
		 		<span class='input-group-btn'>
		            <button type='button' class='btn btn-info btn-flat btn_load_kelompok'><i class='fa  fa-caret-right'></i>&nbsp;Lihat Kelompok/Indikator!</button>
		        </span>
		 	</div> 
	 	</div>	

	 	<div class='col'>
	 		Pilih Tabel Inti
		 	<div class='input-group'>
		 		<select class='form-control' id='select_table'>
		 		<option value='kelompok'>kelompok SUPD</option>
		 		<option value='kelompok_sipd'>kelompok SIPD</option>
		 		<option value='kelompok_matrix'>kelompok Matrix/Provinsi</option>
		 		<option value='kelompok_kabupaten'>kelompok Kabupaten</option>
		 		</select>
		 	</div> 
	 	</div>	

	 	</form>
 		</div>

 		</div>

 		<div class='box-header with-border no-margin'>
              <h4 class='box-title'><i class='fa fa-table'></i>&nbsp; <span>Tabel 1.0 : Kelompok Indikator</span>
              </h4>

              <div class='ie_option box-tools pull-right'>
                
              </div>
        </div>

 		{$table}

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
		$kat = strtoupper(trim($this->scr->filter($_GET['kat'])));
		$this->kategori = $kat;
		if ($kat == 'SIPD') {
			$this->listkelompok(4);
		} else if ($kat == 'SUPD') {
			$this->listkelompok(4);
		}
	}


	function listKelompok($idkelompok) {
		$list = "<table border=1>
		<tr>
		<th>level</th>
		<th>idkelompok</th>
		<th>idparent</th>
		<th>urai</th>
		<th>satuan</th>
		<th>formula</th>
		<th>ordering</th>
		<th>penggunaan</th>
		<th>iduser</th>
		</tr>
		";
		$this->recrusiveKelompok($list, $idkelompok);
		$list .= "</table>";
		print $list;
	}
	function recrusiveKelompok( &$sresult = "", $parent = 0, $level = 0) {
		$sql = "SELECT *
				FROM kelompok_sipd 	
				WHERE idparent = '$parent' 
				order by ordering ";

		if ($this->kategori == 'SIPD') {
			$sql = "SELECT k_supd.*,k_supd.idkelompok as supd_idkelompok,k_supd.iduser as supd_iduser,k_sipd.*
			FROM kelompok_sipd k_sipd
			LEFT JOIN `konversi_kelompok` konv ON konv.idkelompok_sipd = k_sipd.idkelompok
			LEFT JOIN `kelompok` k_supd ON k_supd.idkelompok = konv.idkelompok_supd
			WHERE k_sipd.idparent = '$parent'  ORDER BY k_sipd.ordering ASC;";
		} else if ($this->kategori == 'SUPD') {
			$sql = "SELECT k_supd.*,k_supd.idkelompok as supd_idkelompok,k_supd.iduser as supd_iduser
			FROM kelompok_supd k_supd
			LEFT JOIN `konversi_kelompok` konv ON konv.idkelompok_supd = k_supd.idkelompok
			LEFT JOIN `kelompok` k_sipd ON k_sipd.idkelompok = konv.idkelompok_supd
			WHERE k_supd.idparent = '$parent'  ORDER BY k_supd.ordering ASC;";
		}
				 
		// $sql = "SELECT k_sipd.*,k_supd.*
		// FROM kelompok k_supd
		// LEFT JOIN `konversi_kelompok` konv ON konv.idkelompok_supd = k_supd.idkelompok
		// LEFT JOIN `kelompok_sipd` k_sipd ON k_sipd.idkelompok = konv.idkelompok_sipd
		// WHERE k_supd.idparent = '$parent'  ORDER BY k_supd.ordering ASC;";
		// die($sql);

		$dataSource = $this->db->query($sql);
		while($data = $this->db->fetchArray($dataSource)){

			// URUSAN, bisa lebih dari 1 dalam 1 indikator
  			$sql_urusan = "SELECT GROUP_CONCAT(CONCAT(u.kode_urusan,'.',su.kode_suburusan,' : ',su.urai) SEPARATOR '; ') AS urusan FROM urusan_bidang su
				 JOIN urusan_bidang u ON u.kode_urusan = su.kode_urusan AND u.kode_suburusan = ''
				 WHERE CONCAT(su.kode_urusan,'-',su.kode_suburusan) IN (
				 SELECT CONCAT(kode_urusan,'-',kode_suburusan)
				 FROM urusan_kelompok WHERE idkelompok  = '{$data['supd_idkelompok']}');";

  			$res_urusan = $this->db->query($sql_urusan);
  			$dataUrusan = $this->db->fetchAssoc($res_urusan);
  			$data['urusan'] = $dataUrusan['urusan'];

			// SUPD, bisa lebih dari 1 dalam 1 indikator
  			$sql_supd = "SELECT GROUP_CONCAT(CONCAT(s.urai,' : ',sub.urai) SEPARATOR '; ') AS supd_urai FROM supd sub
						JOIN supd s ON s.idsupd = sub.idparent
						WHERE sub.idsupd 
						IN (SELECT idsupd FROM supd_kelompok 
  							WHERE idkelompok  = '{$data['supd_idkelompok']}')";
  			// if ($tab == 1) die($sql_supd);
  			$res_supd = $this->db->query($sql_supd);
  			$dataSUPD = $this->db->fetchAssoc($res_supd);
  			$data['supd'] = $dataSUPD['supd_urai'];

  			// PENGGUNAAN
  			$penggunaan_sql = "SELECT GROUP_CONCAT(penggunaan) as penggunaan FROM penggunaan_indikator WHERE idpenggunaan IN (SELECT idpenggunaan FROM kelompok_penggunaan 
  							WHERE idkelompok  = '{$data['supd_idkelompok']}')";
  			$penggunaan_qry = $this->db->query($penggunaan_sql);
  			$resPenggunaan = $this->db->fetchAssoc($penggunaan_qry);
  			$data['penggunaan_supd'] = $resPenggunaan['penggunaan'];

  			//Asal Instansi
  	// 		$sql_instansi = "SELECT u.*,i.*
			// 			FROM users u
			// 			LEFT JOIN instansi i ON i.idinstansi = u.idinstansi 
			// 			WHERE u.iduser='{$data['supd_iduser']}'";
			// 			die($sql_instansi);
			// $res_instansi = $this->db->query($sql_instansi);
			// $dataInstansi = $this->db->fetchAssoc($res_instansi);
			// $data['user_supd'] = $dataInstansi['nama_instansi'];

			$tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
			$sresult .= "<tr>
			<td>{$level}</td>
			<td>{$data['idkelompok']}</td>
			<td>{$data['idparent']}</td>
			<td>{$tab}{$data['urai']}</td>
			<td>{$data['satuan']}</td>
			<td>{$data['formula']}</td>
			<td>{$data['ordering']}</td>
			<td>{$data['penggunaan']}</td>
			<td>{$data['iduser']}</td>
			<td>{$data['urusan']}</td>
			<td>{$data['supd']}</td>
			<td>{$data['penggunaan_supd']}</td>
			</tr>";
			$this->recrusiveKelompok($sresult, $data['idkelompok'], $level+1);
		}
	}


	function GetDetail($id){
		# detail 
	}
	function getJSON($id){
		# ajax handler
		$jmode = $_GET['ajaxmode'];
		switch ($jmode) {
			case 'listkelompok':
				# code...
				return $this->_listKelompok();
			break;			
			case 'listsubkelompok':
				# code...
				return $this->_listSubKelompok($id);
			break;
			case 'tabelkelompok' :
				$type 	= $_GET['type'];
				$id 	= $_GET['id'];
				$select_table 	= $_GET['select_table'];
				return $this->_loadTableKelompok($id,$type,$select_table);
			break;			
			case 'tabelkelompokinstansi' :
				$type 	= $_GET['type'];
				$id 	= $_GET['id'];
				$select_table 	= $_GET['select_table'];
				return $this->_loadTableKelompok($id,$type);
			break;
			case 'editkelompok' :
				return $this->_editKelompok($id);
			break;
			case 'listkelompok_analisa':
				return $this->_listKelompokAnalisa($id);
			break;			
			case 'sub_urusan':
				return $this->_subUrusan($id);
			break;
			default:
				# code...
			break;
		}
	}
	function cekAkses() {
		/* AKSES KELOMPOK 
		 * uraikan berdasarkan akses, 1:admin, 2:operator 3:skpd, 4:instansi_vertikal\
		 */
		$datausr = $this->auth->getDetail();
		$Qgroup = $this->db->query('SELECT * FROM `group` WHERE idgroup='.$datausr['idgroup']);
		$dataGroup = $this->db->fetchAssoc($Qgroup);
		// print_r($datausr);
		if ($datausr['idgroup'] == 1) {
			$this->userAkses = 'admin';
		} else if ($datausr['idgroup'] == 2) {
			$this->userAkses = 'instansi';
			$sqlInstansi = 'SELECT * FROM instansi AS i 
					LEFT JOIN users AS u ON u.`idinstansi` = i.`idinstansi` 
					WHERE u.`iduser`='.$datausr['iduser'];
			$qInstansi = $this->db->query($sqlInstansi);
			$this->activeInstansi = $this->db->fetchAssoc($qInstansi);
		} else if ($datausr['idgroup'] == 3) {
			$this->userAkses = 'bidang';
			// $this->activeBidang = $this->db->fetchAssoc($qInstansi);
		}
	}
	function hasAksesOnKelompok($idkelompok){
		/** cek akses user ke indikator 
		 * - load kelompok berdasarkan idkelompok dan iduser pada instansi
		 * - digunakan pada modul : Kelompok, Progis, Analisa
		 */

		$datausr 	= $this->auth->getDetail();
		$hasAkses = false;
		if ($datausr['idgroup'] == 1) {
			//akses admin, all granted
			$hasAkses = true;
		} else if ($datausr['idgroup'] == 2) {
			// instansi, cek seluruh user dibawah instansi
			$qKelompok = "SELECT idkelompok FROM `kelompok` k 
							JOIN `users` u ON k.iduser = u.iduser 
							WHERE k.idkelompok='".$this->db->escape_string($idkelompok)."' 
								AND u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->db->escape_string($datausr['idinstansi'])."')";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) > 0) 
				$hasAkses = true;
		} else if ($datausr['idgroup'] == 3) {
			// bidang, cek hanya user
			$qKelompok = "SELECT idkelompok FROM `kelompok` k 
							JOIN `users` u ON k.iduser = u.iduser 
							WHERE k.idkelompok='".$this->db->escape_string($idkelompok)."' 
								AND u.iduser='".$this->db->escape_string($datausr['iduser'])."'";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) >0)
				 $hasAkses = true;
		}
		return $hasAkses;
	}
	private function _listKelompok(){
		// membuat list json kelompok berdasrkan ura dicari
		// table kelompok : idkelompok, idparent, urai, formula, satuan 
		$keyword 		= $_GET['keyword']; //<!-- input keyword 

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT * FROM kelompok WHERE urai LIKE '%{$this->db->escape_string($keyword)}%'";

		} else if ($this->userAkses == 'instansi') {
			$sql_kelompok 	= "SELECT *
				FROM kelompok k
				JOIN users u ON (k.iduser = u.iduser)
				WHERE k.urai LIKE '%{$this->db->escape_string($keyword)}%'
				AND u.iduser IN (
					SELECT iduser FROM users
					WHERE idinstansi='{$this->db->escape_string($this->activeInstansi['idinstansi'])}'
				)";

		} else if ($this->userAkses == 'bidang') {
			// $sql_kelompok 	= "SELECT * FROM kelompok WHERE urai LIKE '%".$this->db->escape_string($keyword)."%'";
		} 

		$res_kelompok	= $this->db->query($sql_kelompok);
		$json_data = array();
		while ($rec_kelompok = $this->db->fetchAssoc($res_kelompok)) {
			$id = $rec_kelompok['idkelompok'];
			// $json_data[$id]['label'] = $rec_kelompok['urai'];
			$json_data[$id]['value'] = $rec_kelompok['urai'];
			$json_data[$id]['id'] = $id;
		}
		sort($json_data);
		print json_encode($json_data);
	}
	private function _listSubKelompok($idkelompok) {
		// list opsi sub/jenis urusan
		$this->cekAkses();
		if ($idkelompok > 0) {
			if ($idkelompok < 5) {
				$sqlsub = "SELECT * FROM kelompok WHERE idparent={$this->db->escape_string($idkelompok)} ORDER BY ordering ASC";

			} else if ($this->userAkses == 'admin') {
				// akses admin 
				$sqlsub = "SELECT * FROM kelompok WHERE idparent={$this->db->escape_string($idkelompok)} ORDER BY ordering ASC";

			} else if ($this->userAkses == 'instansi'){
				// $idinstansi = $this->loadinstansi;

				// $sqlsub = "SELECT * FROM kelompok k 
				// 			LEFT JOIN users u ON k.iduser = u.iduser 
				// 			WHERE u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->loadinstansi."') 
				// 			AND k.idparent=".$id;
			} else if ($this->userAkses == 'bidang'){
				//
			}

			$qsub = $this->db->query($sqlsub);
	  		while ($dataSUrusan = $this->db->fetchAssoc($qsub)) {
	  			$option .= "<option value='".$dataSUrusan['idkelompok']."'>{$dataSUrusan['urai']}</option>";
	  		}
		} else {
			$option = "";
		}
	  	return json_encode(array('options'=>$option)); 
	}

	private function _loadTableKelompok($iddata,$type='kelompok',$select_table='kelompok'){
		//load dataTabale untuk ditampilkan
		$this->cekAkses();
		$this->typeData  = $type; 
		$this->tableKelompok = $select_table;

 		// seleksi index data : 1. kelompok 2. skpd
 		// die($type);
 	 	if ($this->typeData == 'kelompok') {
 			// detail data kelompok : parent Data
			$sqlKelompok = "SELECT
				a.idkelompok,
				a.urai,
				k.idkelompok as started_id
				FROM {$this->tableKelompok} k
				JOIN {$this->tableKelompok} a ON (a.idkelompok = k.idparent)
				WHERE k.idkelompok={$this->db->escape_string($iddata)}";
 		} 

	 	$resKelompok = $this->db->query($sqlKelompok);
	 	$rowKelompok = $this->db->fetchAssoc($resKelompok);

	 	// print_r($rowKelompok);
 		// die ($sqlKelompok);

	 	// table header, sesuaikan dengan pilihan tahun
 		$thead = "
 			<tr>
				<th rowspan=2>No</th>
				<th rowspan=2>Kelompok/Sub Kelompok</th>
				<th rowspan=2>Satuan</th>
				<th rowspan=2 class='visible_id'>{ID}</th>
				<th rowspan=2>Formula</th>
				<th colspan=2>Sumber Data</th>
				<th rowspan=2>Urusan / SubUrusan</th>
				<th rowspan=2>SUPD</th>
				<th rowspan=2>penggunaan</th>
				<th rowspan=2>Aksi</th>
			</tr>
			<tr>
				<th>Instansi</th>
				<th>Bidang</th>
			</tr>
		";

		// table body
		$empty_td = str_repeat("<td></td>", count($this->tahunData));
		$tbody = "<tr class='kelompok_parent'>
		<td></td><td colspan=3><b>".$rowKelompok['urai']."</b></td>
		{$empty_td}
		<td></td><td></td><td></td><td></td><td></td>
		</tr>";

		$this->elemennumber = 1;
		$this->detailKelompok = "";
		$this->row_id = 1;
 		$this->_lisdetailkelompok($rowKelompok['started_id']);
 		$tbody .= $this->detailKelompok; //join

 		// print "<table>{$thead}{$tbody}</table>";
 		
		print json_encode(array('header' => $thead, 'body' => $tbody));
	}

	private function giveNumbering($number,$tab=0){
		//numbering format, base on level kelompok
		// penomoran yang sampai melebini z (kecil) 
		// menimbulkan masalah yang serius terutama jika yang dimunculkan adalah karakter non UTF
		if ($tab == 0) {
			$number = ""; //lv 1
		} else if ($tab == 1) {
			$number = $number.". "; //lv 1
		} else if ($tab == 2) {
			$pref = floor(($number-1)/26);
			$pref = ($pref > 0) ? chr($pref+96) : "" ; 
			$calc = ($number%26 > 0) ? $number%26 : 26 ; 
			$number = $pref.chr($calc+96).". "; //lv 2		
		} else if ($tab == 3) {
			$number = $number."). "; //lv 3
		} else if ($tab == 4) {
			$pref = floor(($number-1)/26);
			$pref = ($pref > 0) ? chr($pref+96) : "" ; 
			$calc = ($number%26 > 0) ? $number%26 : 26 ; 
			$number = $pref.chr($calc+96)."). "; //lv 4	
		} else if ($tab == 5) {
			$number = $number."). "; //lv 5
		} else if ($tab == 6) {
			$pref = floor(($number-1)/26);
			$pref = ($pref > 0) ? chr($pref+96) : "" ; 
			$calc = ($number%26 > 0) ? $number%26 : 26 ; 
			$number = $pref.chr($calc+96)."). "; //lv 6	
		}
		return $number;
	} 

	function _lisdetailkelompok($iddata,$tab=0){
	/*
		 * dipakai pada form edit detail kelompok
		 * lisitng element & sub (parent & 1 child)
		 * tambahkan detail kelompok
		 * - load kelompok - kelompok detail
		 * - cek type permintaan, skpd / instansi > this->typeData
		 */
		// id, idparent, urai, formula, satuan
		// kelompok_detail, idkelompok_detail, idkelompok, tahun, nilai, iduser, postdate

		if ($this->typeData == 'kelompok') {
			$sqlKlp	= "SELECT * FROM {$this->tableKelompok} WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child	= "SELECT idkelompok FROM {$this->tableKelompok} WHERE idparent={$this->db->escape_string($iddata)} ORDER BY ordering ASC";
		}

		// current kelompok
		$QKelompok 		= $this->db->query($sqlKlp);
		$dataKelompok 	= $this->db->fetchAssoc($QKelompok);
		// print "<pre>".print_r($dataKelompok,1)."</pre>";

		// current kelompok child
		$res_child 	= $this->db->query($sql_child);
		$n_child 	= $this->db->numRows($res_child);
		// die($sqlKlp);

		if (!empty($iddata)) {
			$this->detailKelompok .= $this->rowData($iddata,$dataKelompok,$tab);
			$this->elemennumber++;
		}

  		if ($n_child > 0) {
  			// parent kelompok
				// idkelompok ini masih memiliki child didalamannya
				$this->numbering[$iddata] = 0;
				// $this->elemennumber++;
	  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
	  			// print_r($dataDetail);
				$this->numbering[$iddata] += 1;
	  			$this->_lisdetailkelompok($dataDetail['idkelompok'],$tab+1);
	  		}
  		}

  	}

  	private function rowData($iddata,$dataKelompok,$tab=0,$number=0){
  		// penomoran item
  		if ($number > 0) {
			$number = $this->giveNumbering($number,$tab);
  		} else {
			$number = $this->giveNumbering($this->numbering[$dataKelompok['idparent']],$tab);
  		}

		$number = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;',$tab)."<b>{$number}</b>";

		if ($this->userAkses == 'admin') {
  			$action = '
	  			<button type="button" class="btn-flat-info" onClick="editKelompok('.$iddata.')"><i class="fa fa-edit"></i></button>
	  			<button type="button" class="btn-flat-info" onClick="addKelompokChild('.$iddata.')"><i class="fa fa-plus"></i></button>
	  			<button type="button" class="btn-flat-info" onClick="removeKelompok('.$iddata.')"><i class="fa fa-minus"></i></button>
	  		';
		}

	  	// coloring row
	  	$bg_row = '';
	  	if ($_POST['cntmode'] == 'ins') {$bg_row = 'bg-info text-blue';}
	  	// if ($_POST['cntmode'] == 'upd') {$bg_row = 'bg-warning';}

  		$return = "
			<tr id='kelompok_".$dataKelompok['idkelompok']."' data-tab='{$tab}' class='{$bg_row}'>
			<td>{$this->elemennumber}</td>
			<td class='urai'>".$number."<span>".$dataKelompok['urai']."</span></td>
			<td align=center>".$dataKelompok['satuan']."</td>
			<td align=center>".$dataKelompok['idkelompok']."</td>
			</tr>";

		// $dataKelompok['sub_urusan']
			/*
			<td align=center class='formula hidden'>".$dataKelompok['formula']."</td>
			<td class='nama_instansi hidden'>".$dataKelompok['nama_instansi']."</td>
			<td class='bidang hidden'>".$dataKelompok['bidang']."</td>
			<td class='urusan hidden'>".$dataKelompok['urusan']."</td>
			<td class='supd hidden'>".$dataKelompok['supd']."</td>
			<td class='penggunaan hidden'>".$dataKelompok['penggunaan']."</td>
			<td nowrap class=' hidden'>{$action}</td>			<td class='urai'>".$number."<span>".$dataKelompok['urai_supd']."</span></td>
			<td align=center class='satuan'>".$dataKelompok['satuan_supd']."</td>
			<td align=center class='visible_id'>".$dataKelompok['idkelompok_supd']."</td>
			*/

		return $return;
  	}

  	function _editKelompok($idkelompok) {

  	}

  	private function _listKelompokAnalisa(){
		// membuat list json kelompok berdasrkan ura dicari
		// table kelompok : idkelompok, idparent, urai, formula, satuan 
		$keyword 		= $_GET['keyword']; //<!-- input keyword 

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT *
				FROM kelompok
				WHERE urai LIKE '%{$this->db->escape_string($keyword)}%'
				AND iduser <> ''
				AND (formula <> '' OR idkelompok IN (SELECT DISTINCT(idkelompok) FROM kelompok_detail))";

		} else if ($this->userAkses == 'instansi') {
			$sql_kelompok 	= "SELECT *
				FROM kelompok k
				JOIN users u ON (k.iduser = u.iduser)
				WHERE urai LIKE '%{$this->db->escape_string($keyword)}%'
				AND u.iduser IN (SELECT iduser FROM users WHERE idinstansi='{$this->db->escape_string($this->activeInstansi['idinstansi'])}')";

		} else if ($this->userAkses == 'bidang') {
			// $sql_kelompok 	= "SELECT * FROM kelompok WHERE urai LIKE '%".$this->db->escape_string($keyword)."%'";
		} 

		$res_kelompok	= $this->db->query($sql_kelompok);
		$json_data = array();
		while ($rec_kelompok = $this->db->fetchAssoc($res_kelompok)) {
			$id = $rec_kelompok['idkelompok'];
			$json_data[$id]['text'] = $rec_kelompok['urai'];
			// $json_data[$id]['value'] = $rec_kelompok['urai'];
			$json_data[$id]['value'] = $id;
			$json_data[$id]['id'] = $id;
		}
		sort($json_data);
		print json_encode($json_data,JSON_NUMERIC_CHECK);
	}

	private function _subUrusan($idinstansi) {
		/*
		* id = idinstansi
		*/
		if ($idinstansi > 0) {
			$sql_sub = "SELECT * FROM instansi i
				LEFT JOIN suburusan_bidang sub ON (
					sub.kode_urusan = i.kode_urusan
					AND sub.kode_suburusan = i.kode_suburusan
				)
				WHERE i.idinstansi={$idinstansi}";

			$res_sub = $this->db->query($sql_sub);
	  		while ($dataSUrusan = $this->db->fetchAssoc($res_sub)) {
	  			$option .= "<option value='".$dataSUrusan['idsub_urusan']."'>{$dataSUrusan['sub_urusan']}</option>";
	  		}
		} else {
			$option = "";
		}
	  	return json_encode(array('options'=>$option)); 
	}

}