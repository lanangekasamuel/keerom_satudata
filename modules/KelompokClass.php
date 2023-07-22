<?php
/*
	[20180925023725][anovedit][note]
	saya sendiri masih bingung. karena sedang marathon,
	jadi saya akan melakukan apa yg kiranya perlu dilakukan.

	$this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();
*/

Class KelompokClass extends ModulClass
{
	/**
	 * modul kelompok
	 * digunakan untuk manipulasi tabel kelompok
	 * tabel2 yg bersangkutan :
	 * kelompok : idkelompok, idparent, urai, satuan, formula, ordering, penggunaan, iduser, latsupdate
	 * penggunaan_indikator : idpenggunaan,penggunaan
	 * suburusan_bidang : idsub_urusan, sub_urusan
	 **/

	/* [anovedit] handle penomoran, dan tingkatan */
	private $table_kelompok_levl = []; // loop level
	private $table_kelompok_numb = []; // loop number

	// [anovedit] saya pindah sini, supaya tidak mengganggu getter.
	private $elemennumber = 1;

	/*
		[anovedit][note] method ini digunakan pada:
		//giadmin/kelompok/matrik/
		//giadmin/kelompok/kabupaten/
		;yaitu saat manipulasi matrix-indikator (form,ajax,create|update)
	*/
	function buildForm()
	{
		// edit add form
		$this->cekAkses();
		$idkelompok = $this->db->escape_string($_GET['id']);
		$idparent 	= $this->db->escape_string($_GET['idparent']);
		$idinstansi = $this->db->escape_string($_GET['idinstansi']);
		$haskakses 	= $this->hasAksesOnKelompok($idkelompok);

		$md_kelp = $this->scr->filter(strtolower($_GET['tbmode']));
		$this->pilahKelompok($md_kelp);

		/*
			[anovedit][workaround] karena params yg ada cuma "idkelompok"/"idparent",
			sedangkan pk_instansi tidak ada, padahal pk_instansi harus ada.
			jadi harus panggil manual untuk mendapatkan "pk_instansi".
		*/
		if (empty($idinstansi) && (!empty($idparent) || !empty($idkelompok))) {
			$idinstansi = $this->db->ci3db
			->from($this->table)
			->select('pk_instansi')
			->where('idkelompok', (empty($idparent) ? $idkelompok : $idparent))
			->get();
			$idinstansi = $idinstansi ? $idinstansi->row_array()['pk_instansi'] : 0;
		}

		if ($haskakses) { // || $haskakses_parent
			// pilih theme
			$datausr = $this->auth->getDetail();
			if ($datausr['idgroup'] == 1) {
				$theme_file = 'kelompok.html';
			} else if ($datausr['idgroup'] == 2) {
				$theme_file = 'kelompok_instansi.html';
			}

			if($idkelompok <> ''){
				/* [anovedit][rewrite][users_table_ignored][bidang_is_suburusan] */
				// kelompok memiliki author/user
				$sql = "SELECT
					a.*,
					d.idkelompok as parent_id,
					d.urai as parent_urai,
					a.pk_instansi as idinstansi,
					c.idsub_urusan as idbidang_instansi -- [legacy]
					FROM {$this->table} a
					left join {$this->table} d on (d.idkelompok = a.idparent)
					left join instansi b on (b.idinstansi = a.pk_instansi)
					left join suburusan_bidang c on (c.kode_urusan = b.kode_urusan and c.kode_suburusan = b.kode_suburusan)
					WHERE a.idkelompok = {$this->db->ci3db->escape($idkelompok)}";
				$result = $this->db->query($sql);
				$data = $this->db->fetchArray($result);
				$action ='upd';
				$status ='edit';

				$sql_child = "SELECT *
					FROM {$this->table}
					WHERE idparent = {$this->db->ci3db->escape($idkelompok)}
					ORDER BY ordering ASC";
				$res_child = $this->db->query($sql_child);
				$num_child = $this->db->numRows($res_child);

				// indikator (child) ditambah auto complete kedepannya
				$opsi_formula .= "<div class='col col-sm-12 b-border'><div class='col col-sm-2'>sub indikator </div><div class='col col-sm-10' id='formula_subindikator'>";
				$xin = 0;
				while ($dataChild = $this->db->fetchAssoc($res_child)) {
					$xin_label = chr(97+$xin);
					$opsi_formula .= "<button form='none' type='button' class='btn btn-flat btn-info btn-match btn-indikator' data-key='{\"key\":\"{{$dataChild['idkelompok']}}\",\"label\":\"({$dataChild['urai']})\",\"hide\":true,\"id\":\"{$dataChild['idkelompok']}\",\"urai\":\"{$dataChild['urai']}\"}'>{$xin_label} : {$dataChild['urai']}</button>";
					$xin ++;
				}
				$opsi_formula .= "</div><input type='text' form='frm_progis_skpd' class='form-control' id='indikator_ac' name='indikator_ac' value='' placeholder='tambahkan indikator lain'></div>";

				// operator normal
				$opsi_formula .= "<div class='col col-sm-12 b-border'><div class='col col-sm-2'>operator</div>
				<div class='col col-sm-10'>";
				$match_sym = array('+','-','*','/');
				foreach ($match_sym as $key) {
					$opsi_formula .= "<button form='none' type='button' class='btn-match btn-operator' data-key='{\"key\":\"{$key}\",\"hide\":false}'>{$key}</button>";
				}
				$opsi_formula .= "<button form='none' class='btn btn-sm btn-flat btn-default btn-match-reset'><i class='fa fa-remove'></i> Hapus/Reset Formula</button></div></div>";

				// fast formula
				if ($num_child > 0) {
					$opsi_formula .= "<div class='col col-sm-12 b-border'><div class='col col-sm-2'>formula cepat </div><div class='col col-sm-10'>";
					$match_sym = array('sum' => 'SUM(a+b+c+..)','avg' => 'AVG(a+b+c+../n)','mlt' => 'Multiply(a*b*..)');
					foreach ($match_sym as $key => $label) {
						$opsi_formula .= "<button form='none' type='button' class='btn-match btn-operator-child' data-key='{\"key\":\"{$key}\",\"hide\":false}'>{$label}</button>";
					}						
					if ($num_child == 2) {
						$match_sym = array('a-b','b-a','a/b','b/a','a/b*100%','b/a*100%');
						foreach ($match_sym as $key) {
							$opsi_formula .= "<button form='none' type='button' class='btn-match btn-operator-child' data-key='{\"key\":\"{$key}\",\"hide\":false}'>{$key}</button>";
						}				
					}
					$opsi_formula .= "</div></div>";
				}  

				// custoom map legend
				$list_maplegend = "";
				$sql_legend = "SELECT * FROM map_legend WHERE idkelompok={$this->db->ci3db->escape($idkelompok)} ORDER BY batas_atas DESC";
				$res_legend = $this->db->query($sql_legend);

				while($data_legend = $this->db->fetchAssoc($res_legend)) {
					$idlegend = $data_legend['idlegend'];
					$list_maplegend .= "
						<tr class='row_legend' id='legend_{$idlegend}' data-id='{$idlegend}'>
							<td>
							<form id='frm_legend_{$idlegend}' hidden>
								<input type='hidden' name='idkelompok' value='{$data_legend['idkelompok']}' form='frm_legend_{$idlegend}'>
								<input type='hidden' name='idlegend' value='{$idlegend}' form='frm_legend_{$idlegend}'>
							</form>
							<span>{$data_legend['label']}</span>
								<input type='text' class='form-control' name='label' id='label' placeholder='isikan label/keterangan' value='{$data_legend['label']}' form='frm_legend_{$idlegend}'>
							</td>
							<td class='col-xs-2'><span>{$data_legend['batas_bawah']}</span>
								<input type='number' class='form-control' name='batas_bawah' id='batas_bawah' placeholder='isikan batas bawah' value='{$data_legend['batas_bawah']}' form='frm_legend_{$idlegend}'></td>
							<td class='col-xs-2'><span>{$data_legend['batas_atas']}</span>
								<input type='number' class='form-control' name='batas_atas' id='batas_atas' placeholder='isikan batas atas' value='{$data_legend['batas_atas']}' form='frm_legend_{$idlegend}'></td>
							<td class='col-xs-2 warna' style='background-color:{$data_legend['warna']};'><span>{$data_legend['warna']}</span>
								<input type='text' class='form-control' style='background-color:transparent;' name='warna' id='warna' placeholder='pilih warna' value='{$data_legend['warna']}' form='frm_legend_{$idlegend}'></td>
							<td class='col-xs-1 text-center' nowrap>
								<button type='button' class='btn_update btn btn-sm btn-info btn-flat'><i class='fa fa-save'></i></button>
								<button type='button' class='btn_cancel btn btn-sm btn-warning btn-flat'><i class='fa fa-close'></i></button>
								<button type='button' class='btn_edit btn btn-sm btn-success btn-flat'><i class='fa fa-edit'></i></button>
								<button type='button' class='btn_delete btn btn-sm btn-danger btn-flat'><i class='fa fa-close'></i></button>
							</td>
						</tr>
					";
				}

			} elseif ($idparent <> '') {
				/*
					[anovedit][legacy][users_table_ignored][bidang_is_suburusan]
					untuk *bidang, saya ganti dengan dengan pk_suburusan_bidang.
					karena kurang jelas yg dimaksud *bidang itu apa...
				*/
				$sql = "SELECT
				a.idkelompok AS parent_id,
				a.urai AS parent_urai,
				a.publish,
				a.pk_instansi as idinstansi,
				c.idsub_urusan as idbidang_instansi -- [legacy]
				FROM {$this->table} a 
				left join instansi b on (b.idinstansi = a.pk_instansi)
				left join suburusan_bidang c on (c.kode_urusan = b.kode_urusan and c.kode_suburusan = b.kode_suburusan)
				WHERE a.idkelompok = {$this->db->ci3db->escape($idparent)}";

				$result = $this->db->query($sql);
				$data = $this->db->fetchArray($result);
				$action ='ins';
				$status ='tambah';
			} elseif ($idinstansi <> '') {
				// $sql = "SELECT k.idkelompok AS parent_id,k.urai AS parent_urai,
				// 			u.`idinstansi`, u.`idbidang_instansi` 
				// 		FROM kelompok_matrix k 
				// 		LEFT JOIN users u ON u.iduser = k.iduser 
				// 		WHERE 
				// 			k.idkelompok='".$idparent."'";
				// $result = $this->db->query($sql);
				$data['idinstansi'] = $idinstansi;
				$data['publish'] = 1;
				$action ='ins';
				$status ='tambah';
			}
			// die($sql);

			#build form
			$this->title = 'Kelompok';

			if ($this->userAkses == 'admin') {

				// opsi formula
				$formula_label = $this->readFormula($this->table,$data['formula']);

				// opsi instansi
				$opsi_instansi = '<option value=0> -- Pilih SKPD --</option>';

				/* [anovedit][rewrite][users_table_ignored] */
				$opts_instansi = $this->db->ci3db
				->from('instansi')
				->order_by('nama_instansi','asc')
				->get();
				foreach ($opts_instansi->result_array() as $indata) {
					$slc = ($indata['idinstansi'] == $data['idinstansi']) ? 'selected' : '';
					$opsi_instansi .= "<option value='{$indata['idinstansi']}' {$slc}>{$indata['nama_instansi']}</option>";
				}

				/*
					[anovedit][override] saya hilangkan, field penggunaan,
					kesimpulan saya, belum tau penggunaan itu nanti dipakai untuk apa.
					tapi jika harus dipakai, bisa saja...
					[penggunaan_remove][revert]
				*/
				
				// penggunaan data
				// $penggunaan = array(RKPD,RPJM,LPPD,SIPD,TANNAS,INDIKATOR KINERJA,SPM,LAKIP,LKPJ,MDGs);
				$sql_penggunaan = "SELECT
					GROUP_CONCAT(idpenggunaan) as id
					FROM penggunaan_kelompok
					WHERE idkelompok={$this->db->ci3db->escape($idkelompok)}";
				$res_penggunaan = $this->db->query($sql_penggunaan);
				$data_penggunaan = $this->db->fetchAssoc($res_penggunaan);
				$data['penggunaan'] = $data_penggunaan['id'];
				$current_penggunaan = explode(',',$data['penggunaan']);
				$cek_penggunaan = '';
				$keg_sql = "SELECT * FROM penggunaan_indikator ORDER BY penggunaan ASC";
				$keg_res = $this->db->query($keg_sql);
				while($kegdata = $this->db->fetchArray($keg_res)){
					$slc = (in_array($kegdata['idpenggunaan'],$current_penggunaan)) ? 'checked' : '';
					$cek_penggunaan .= "
						<div class='checkbox col col-sm-4 col-md-3'>
							<label>
								<input name='penggunaan[]' id='penggunaan[]' type='checkbox' value='{$kegdata['idpenggunaan']}' {$slc}>
									{$kegdata['penggunaan']}
							</label>
						</div>";
				}

				// publikasi data
				$publish = array(1=>'Ya', '0'=>'Tidak');
				$cek_publish = "";
				foreach ($publish as $kode => $urai) {
					$slc = ($kode == $data['publish']) ? 'checked' : '';
					$cek_publish .= "
						<div class='col col-sm-2 col-md-2'>
							<label><input name='publish' id='publish' type='radio' value='{$kode}' {$slc}>
								&nbsp; {$urai}
							</label>
						</div>";
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
							'metode_isian'  => $metode_isian,
							'ordering'   => $data['ordering'],
							'opsi_formula'	=> $opsi_formula,
							'formula_label'	=> $formula_label,
							'opsi_instansi'	=> $opsi_instansi,
							'opsi_sub_urusan'	=> $opsi_sub_urusan,
							'opsi_bidang'	=> $opsi_bidang,
							'opsi_user'		=> $opsi_user,
							'cek_penggunaan'	=> $cek_penggunaan,
							'cek_publish'		=> $cek_publish,

							'list_maplegend' => $list_maplegend,

							'id' 		=> $data['idkelompok'],
							'status' 	=> $status,
							'tbmode' 	=> $this->mode_kelompok,
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
				<script src='{themepath}js/kelompok.js'></script>
				<script src='{themepath}js/jqBootstrapValidation.js'></script>
				<script>
				$(document).ready(function(){
					$('input,select,textarea,button').not('[type=submit]').jqBootstrapValidation({preventSubmit: true});
					{$js_onload}
				});
				</script>";
			$message = 'load berhasil';
		} else {
			$message = 'tidak ada akses ke indikator!'.ERROR_TAG;
		}

		if (isset($_GET['ajaxOn'])) {
			echo json_encode([
				'content' => $form,
				'message' => $message,
			]);
			die();
		} else {
			return $form; 
		}
	}

	// formula reader
	function readFormula($table,$formula)
	{
		// ubah formula menjadi value : 
		// contoh : {idkelompok}*{idkelompok} => 100*12
		// print $akumulasiFormula."<br>";
		preg_match_all("/\{([0-9]+)\}/", $formula, $arrmatches);
		$formulaTextual = $formula;

		foreach ($arrmatches[1] as $idkelompok) {
			// cek ada formulanya atau tidak
			$sqlKelompok = "SELECT formula,urai
				FROM {$table}
				WHERE idkelompok='{$this->db->escape_string($idkelompok)}'";
			$rKelompok = $this->db->query($sqlKelompok);
			$dataKelompok = $this->db->fetchAssoc($rKelompok);			
			$formulaTextual = str_replace('{'.$idkelompok.'}', "(".$dataKelompok['urai'].")", $formulaTextual);
		}

		return $formulaTextual;  	
	}

	// sub function from insert
	function getLastedChild($idparent) {
		// cek anak terakhirnya, sampai kecucu2-nya tidak ditemukan child lagi
		$sql_lastchild = "SELECT idkelompok
			FROM {$this->table}
			WHERE idparent={$this->db->ci3db->escape($idparent)}
			ORDER BY ordering DESC LIMIT 1";
		$res_lastchild = $this->db->query($sql_lastchild);
		$lastchilddata = $this->db->fetchAssoc($res_lastchild);

		if (!empty($lastchilddata)) {
			return $this->getLastedChild($lastchilddata['idkelompok']);
		} else {
			return $idparent;
		}
	}

	function Insert()
	{
		# query insert 
		// cek akses kekelompok
		$this->cekAkses();
		$idparent = $this->scr->filter($_POST['idparent']);
		$haskakses 	= $this->hasAksesOnKelompok($idparent);

		$md_kelp = $this->scr->filter(strtolower($_POST['tbmode']));
		$this->pilahKelompok($md_kelp);

		if ($haskakses) {
			// cek users berdasarkan instansi/bidang
			$idinstansi 		= $this->scr->filter($_POST['idinstansi']);
			$idbidang_instansi 	= $this->scr->filter($_POST['idbidang_instansi']);
			$publish 	= $this->scr->filter($_POST['publish']);

			/*
				[anovedit][override]
				saya masih bingung dengan yg satu ini,
				jadi saya langsung pakai user_id berdasarkan session saja.
			*/
			$upd_user = $this->auth->getDetail();
			$upd_user = "iduser = {$this->db->ci3db->escape($upd_user['iduser'])},";

			if (!empty($idparent)) {
				$result		= $this->db->query("SELECT max(ordering) as mxord
					FROM {$this->table}
					WHERE idparent={$this->db->ci3db->escape($idparent)}");
				// ditambahkan row setelah id kelompok nnn
				$data['after_idkelompok'] = $this->getLastedChild($idparent);
			} else if (!empty($idinstansi)){
				$result		= $this->db->query("SELECT max(ordering) as mxord
					FROM {$this->table}
					where pk_instansi = {$this->db->ci3db->escape($idinstansi)}
					AND idparent = 0");
				// ditambahkan row setelah id kelompok nnn
				$data['after_idkelompok'] = $this->getLastedChild();
			}

			$dord		= $this->db->fetchArray($result);
			$ordering   = ($dord['mxord'] <> '')?$dord['mxord'] +1 : 1;	

			$result		= $this->db->query("SELECT max(idkelompok) as mxid FROM {$this->table}");
			$dord		= $this->db->fetchArray($result);
			$maxid   	= ($dord['mxid'] <> '')?$dord['mxid'] +1 : 1;	

			// penggunaan
			$penggunaan = implode(',', $_POST['penggunaan']);

			/* [anovedit][override] langsung update id_parent saja. */
			$upd_parent = (isset($_POST['idparent']) && $_POST['idparent'] != '') ? $_POST['idparent'] : 0;
			$upd_parent = "idparent = {$this->db->ci3db->escape($upd_parent)},";

			$sql = "INSERT INTO {$this->table} SET
				idkelompok = {$this->db->ci3db->escape($maxid)},
				{$upd_user}
				{$upd_parent}
				urai = {$this->db->ci3db->escape($_POST['urai'])},
				satuan = {$this->db->ci3db->escape($_POST['satuan'])},
				ordering = {$this->db->ci3db->escape($ordering)},
				publish = {$this->db->ci3db->escape($publish)},
				pk_instansi = {$this->db->ci3db->escape($idinstansi)},
				formula = {$this->db->ci3db->escape($_POST['formula'])},
				lastupdate = now()";
			$updateQuery = $this->db->query($sql);

			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				$message = ($updateQuery) 
					? 'penambahan &nbspIndikator sukses' 
					: ('penambahan &nbspIndikator gagal' . ERROR_TAG);

				/*
					[anovedit][remove_penggunaan][?] kurang spesifik yg ini untuk apa?
					[note:][revert] penggunaan
				*/
				if ($this->mode_kelompok == 'matrik') {
				// update tabel penggunaan
					$this->db->ci3db->delete('penggunaan_kelompok', ['idkelompok' => $maxid]);
					if (!empty($penggunaan)) {
						foreach (explode(',',$penggunaan) as $idpenggunaan) {
							$this->db->ci3db->insert('penggunaan_kelompok', ['idkelompok' => $maxid, 'idpenggunaan' => $idpenggunaan]);
						}
						// $upd_penggunaan = "`penggunaan` = '".$this->scr->filter($penggunaan)."',";
					}
				}

				/*
					[anovedit][workaround][users_table_ignored]
					[bidang_is_suburusan:deprecated][bidang_is_urusan_suburusan:recomended]
				*/
				$sql_kelompok = "SELECT c.*,b.*,a.*
					FROM {$this->table} a
					left join instansi b on (b.idinstansi = a.pk_instansi)
					left join urusan c on (c.kode_urusan = b.kode_urusan and c.kode_suburusan = b.kode_suburusan)
					where a.idkelompok = {$this->db->ci3db->escape($maxid)}";

				$tab = isset($_POST['tab']) ? (int) $_POST['tab'] : 0;
				$res_kelompok = $this->db->query($sql_kelompok);
				$dataKelompok = $this->db->fetchAssoc($res_kelompok);

				/* [anovedit][override][remove_penggunaan][revert] */
				if ($this->mode_kelompok == 'matrik') {
					// penggunaan
					$penggunaan_sql = "SELECT
						GROUP_CONCAT(penggunaan) as penggunaan
						FROM penggunaan_indikator
						WHERE idpenggunaan IN ({$this->scr->filter($penggunaan)})";
					$penggunaan_qry = $this->db->query($penggunaan_sql);
					$resPenggunaan = $this->db->fetchAssoc($penggunaan_qry);
					$dataKelompok['penggunaan'] = $resPenggunaan['penggunaan'];
	  		}

				// subs urusan
				$sub_sql = "SELECT *
					FROM suburusan_bidang
					WHERE idsub_urusan  = '{$this->scr->filter($_POST['sub_urusan'])}'";
  			$sub_qry = $this->db->query($sub_sql);
  			$resSubUrs = $this->db->fetchAssoc($sub_qry);
  			$dataKelompok['sub_urusan'] = $resSubUrs['sub_urusan'];

	  		$this->mode_kelompok = 'matrik';
				if (!empty($idparent)) {
					$row_content = $this->rowData($dataKelompok['idkelompok'],$dataKelompok,$tab+1,$ordering);
				} else if (!empty($idinstansi)){
					$row_content = $this->rowData($dataKelompok['idkelompok'],$dataKelompok,0,$ordering);
				}

				echo json_encode([
					'message' => $message,
					'data' => $data, //<- digunakan untuk insert data di tabel
					'row_content' => $row_content,
				]);
				die();

			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}

		} else {
			if (isset($_POST['ajaxOn'])) {
				// dipanggil melalui ajax
				echo json_encode([
					'message' => ('akses ke Indikator ditolak' . ERROR_TAG)
				]);
				die();

			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		}
	}

	function Update()
	{
		# query update 
		// cek akses kekelompok
		$scr_idkelompok = $this->scr->filter($_POST['id']);
		$haskakses 	= $this->hasAksesOnKelompok($scr_idkelompok);

		$md_kelp = $this->scr->filter(strtolower($_POST['tbmode']));
		$this->pilahKelompok($md_kelp);

		if ($haskakses) {
			// muat data indikator yang sebelumnya
			$sql_pkelompok = "SELECT * FROM {$this->table} WHERE idkelompok={$this->db->ci3db->escape($scr_idkelompok)}";
			$res_pkelompok = $this->db->query($sql_pkelompok);
			$prev_datakelompok = $this->db->fetchAssoc($res_pkelompok);

			// cek users berdasarkan instansi/bidang
			$idinstansi 		= $this->scr->filter($_POST['idinstansi']);
			$idbidang_instansi 	= $this->scr->filter($_POST['idbidang_instansi']);

			/* [anovedit][?] ini untuk apa ya? */
			if ($idinstansi > 0 && $idbidang_instansi > 0) {
				// user bidang di butuhkan
				$sql_user = "SELECT * FROM users
					WHERE idinstansi = {$this->db->ci3db->escape($idinstansi)}
					AND idbidang_instansi = {$this->cb->ci3db->escape($idbidang_instansi)}";

			} else if ($idinstansi > 0) {
				// user instansi dibutuhkan
				$sql_user = "SELECT * FROM users
					WHERE idinstansi = {$this->db->ci3db->escape($idinstansi)}
					AND idbidang_instansi IS NULL";
			}
			$res_user 		= $this->db->query($sql_user);
			$userKelompok 	= $this->db->fetchAssoc($res_user);

			/*
				[anovedit][override][?]
				saya masih bingung dengan yg satu ini,
				jadi saya langsung pakai user_id berdasarkan session saja.
			*/
			$upd_user = $this->auth->getDetail();
			$upd_user = "iduser = {$this->db->ci3db->escape($upd_user['iduser'])},";

			/* [anovedit][override] langsung pakai idparent saja. */
			$upd_parent = (isset($_POST['idparent']) && $_POST['idparent'] != '') ? $_POST['idparent'] : 0;
			$upd_parent = "idparent = {$this->db->ci3db->escape($upd_parent)},";

			/* [anovedit][?][revert] apa ini? */
			// update tabel penggunaan kelompok/indikator (matrik provinsi)
			if ($this->mode_kelompok == 'matrik') {
				// penggunaan
				$penggunaan = implode(',', $_POST['penggunaan']);
				$this->db->ci3db->delete('penggunaan_kelompok',['idkelompok' => $scr_idkelompok]);
				if (!empty($penggunaan)) {
					foreach (explode(',',$penggunaan) as $idpenggunaan) {
						$this->db->ci3db->insert('penggunaan_kelompok', ['idkelompok' => $scr_idkelompok, 'idpenggunaan' => $idpenggunaan]);
					}
				}
			}

			$sql = "UPDATE {$this->table} SET
				{$upd_user}
				{$upd_parent}
				urai = {$this->db->ci3db->escape($_POST['urai'])},
				ordering = {$this->db->ci3db->escape($_POST['ordering'])},
				satuan = {$this->db->ci3db->escape($_POST['satuan'])},
				publish = {$this->db->ci3db->escape($_POST['publish'])},
				pk_instansi = {$this->db->ci3db->escape($idinstansi)},
				formula = {$this->db->ci3db->escape($_POST['formula'])},
				lastupdate = now()
				WHERE idkelompok = {$this->db->ci3db->escape($scr_idkelompok)}";
			$updateQuery = $this->db->query($sql);

			if (isset($_POST['ajaxOn'])) {
				$message = ($updateQuery) 
					? 'update &nbspIndikator sukses' 
					: ('update &nbspIndikator gagal' . ERROR_TAG);

				$data = $_POST;
				// instansi && bidang
							// LEFT JOIN instansi_bidang b ON b.idinstansi=i.`idinstansi` 
							// AND b.idbidang_instansi = '".$idbidang_instansi."'";
				$sql_ibid = "SELECT *
					FROM instansi i
					WHERE i.idinstansi = {$this->db->ci3db->escape($idinstansi)}";
				$res_ibid = $this->db->query($sql_ibid);
				$instansiBidang = $this->db->fetchAssoc($res_ibid);

				// penggunaan
				$penggunaan_sql = "SELECT
					GROUP_CONCAT(penggunaan) as penggunaan
					FROM penggunaan_indikator
					WHERE idpenggunaan IN ({$this->scr->filter($penggunaan)})";
				$penggunaan_qry = $this->db->query($penggunaan_sql);
				$resPenggunaan = $this->db->fetchAssoc($penggunaan_qry);
				$data['penggunaan'] = $resPenggunaan['penggunaan'];

  				// // subs urusan
  				// $sub_sql = "SELECT * FROM suburusan_bidang WHERE idsub_urusan  = '".$this->scr->filter($_POST['sub_urusan'])."'";
	  			// $sub_qry = $this->db->query($sub_sql);
	  			// $resSubUrs = $this->db->fetchAssoc($sub_qry);
	  			// $data['sub_urusan'] = $resSubUrs['sub_urusan'];

  			if ($data['publish'] == 1) {
  				$label = 'Ya'; $text_color = 'text-green'; $icon = 'fa-eye';
  			} else {
  				$label = 'Tidak'; $text_color = 'text-red'; $icon = 'fa-eye-slash';
  			}
	  		$publish_action ='<button type="button" class="btn-flat-info '.$text_color.'" onClick="changePublish('.$scr_idkelompok.')" data-toggle="tooltip" data-original-title="click untuk mengubah Status Publikasi Indikator"><i class="fa '.$icon.'"></i>&nbsp;'.$label.'</button>';
	  		$data['publish'] = $publish_action;

				if (!empty($instansiBidang)) {
					$data += $instansiBidang;
				} else {
					$data += array('nama_instansi' => '', 'bidang' => '') ;			
				}

				echo json_encode([
					'message' => $message,
					'data' => $data, //<- digunakan untuk update data di tabel
				]);
				die();

			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}

		} else {
			if (isset($_POST['ajaxOn'])) {
				echo json_encode([
					'message' => ('akses ke indikator ditolak'.ERROR_TAG)
				]);
				die();

			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		}	 
	}

	function Delete()
	{
		# query delete 
		// DELETE KELOMPOK
		$idkelompok = $this->db->escape_string($_GET['id']);
		$haskakses 	= $this->hasAksesOnKelompok($idkelompok);

		$md_kelp = $this->scr->filter(strtolower($_GET['tbmode']));
		$this->pilahKelompok($md_kelp);

		if ($haskakses) {
			// delete kelompok

			// cek apakah memiliki anak atau tidak
			$sql_child = "SELECT idkelompok FROM {$this->table} WHERE idparent={$this->db->ci3db->escape($idkelompok)}";
			$res_child = $this->db->query($sql_child);
			$num_child = $this->db->numRows($res_child);

			// cek digunakan pada analisa

			// cek digunakan untuk formula

			// cek jika memiliki child
			if ($num_child > 0) {
				$message = 'kelompok ini masih memiliki sub kelompok, <br>untuk menghapus Indikator ini, silakan menghapus dulu sub kelompoknya'.ERROR_TAG;
			} else {
				/*
					[anovedit][workaround]
					langsung hapus saja, jika kepala dipotong, maka semua bagian tubuhnya harus tumbang.
				*/
				$table_kelompok_detail = null;
				if ($this->table === 'kelompok_matrix') $table_kelompok_detail = 'kelompok_detail_matrix';
				elseif ($this->table === 'kelompok_kabupaten') $table_kelompok_detail = 'kelompok_detail_kabupaten';

				$this->db->ci3db->trans_start();
				// [anovedit] kelompok_*
				$this->db->ci3db->delete($this->table,['idkelompok' => $idkelompok]); // hapus kelompok
				if ($table_kelompok_detail) {
					// [anovedit] kelompok_detail_*
					$this->db->ci3db->delete($table_kelompok_detail,['idkelompok' => $idkelompok]); // hapus kelompok-detail
				}
				// [anovedit] penggunaan_kelompok (berdasarkan pada insert dan update)
				$this->db->ci3db->delete('penggunaan_kelompok', ['idkelompok' => $idkelompok]);

				$this->db->ci3db->trans_complete();

				$message = ($this->db->ci3db->trans_status())
					? 'menghapus Indikator sukses'
					: ('menghapus Indikator gagal'.ERROR_TAG);
			}

			if (isset($_GET['ajaxOn'])) {
				echo json_encode([
					'message' => $message,
				]);
				die();

			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}

		} else {
			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				echo json_encode([
					'message' => ('akses ke indikator ditolak'.ERROR_TAG)
				]);
				die();

			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		}
	}

	function up(){
		$idkelompok = $this->db->escape_string($_GET['id']);
		$idinstansi = $this->db->escape_string($_GET['idinstansi']);
		$haskakses 	= $this->hasAksesOnKelompok($idkelompok);

		/*$md_kelp = $this->scr->filter(strtolower($_GET['tbmode']));
		$this->pilahKelompok($md_kelp);*/

		if ($haskakses) {
		#ambil urutan
			$sql 		= "SELECT idkelompok,ordering,idparent FROM kelompok_matrix WHERE idkelompok= '".$idkelompok."'";					
			$res 		= $this->db->query($sql);
			$dtself	 	= $this->db->fetchArray($res);
			
			// q parent
			$sql = "SELECT idkelompok,ordering,idparent FROM kelompok_matrix 
							WHERE idparent= {$this->db->ci3db->escape($dtself['idparent'])} 
							and pk_instansi = {$this->db->ci3db->escape($idinstansi)} 
							AND ordering < {$dtself['ordering']} 
							ORDER BY ordering DESC ";
			$res 		= $this->db->query($sql);
			$dtchg	 	= $this->db->fetchArray($res);

			// die(print_r($_GET,1).print_r($dtself,1).print_r($dtchg,1));
						
			if($this->db->numRows($res) > 0){
				#tukar
				$sqla ="UPDATE kelompok_matrix SET ordering = '".$dtchg['ordering']."' WHERE idkelompok='".$dtself['idkelompok']."'";
				$sqlb ="UPDATE kelompok_matrix SET ordering = '".$dtself['ordering']."' WHERE idkelompok='".$dtchg['idkelompok']."'";
		
				// die(print_r($_GET,1).print_r($sqla,1).print_r($sqlb,1));
				
				$message = ($this->db->query($sqla) && $this->db->query($sqlb)) 
					? 'mengubah urutan Indikator sukses' 
					: 'mengubah urutan Indikator gagal'.ERROR_TAG;
			} else {
				$message = 'mengubah urutan Indikator gagal'.ERROR_TAG.' : sudah yang pertama';
			}

			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				echo json_encode([
					'message' => $message,
				]);
			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		} else {
			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				echo json_encode([
					'message' => ('akses ke indikator ditolak'.ERROR_TAG)
				]);
				die();
			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		}	
	}
	
	function down(){
		$idkelompok = $this->db->escape_string($_GET['id']);
		$idinstansi = $this->db->escape_string($_GET['idinstansi']);
		$haskakses 	= $this->hasAksesOnKelompok($idkelompok);

		/*$md_kelp = $this->scr->filter(strtolower($_GET['tbmode']));
		$this->pilahKelompok($md_kelp);*/

		if ($haskakses) {
		#ambil urutan
			$sql 		= "SELECT idkelompok,ordering,idparent FROM kelompok_matrix WHERE idkelompok= '".$idkelompok."'";					
			$res 		= $this->db->query($sql);
			$dtself	 	= $this->db->fetchArray($res);
			
			// q parent					
			$sql = "SELECT idkelompok,ordering,idparent FROM kelompok_matrix
				WHERE idparent= {$this->db->ci3db->escape($dtself['idparent'])}
				and pk_instansi = {$this->db->ci3db->escape($idinstansi)}
				AND ordering > {$dtself['ordering']}
				ORDER BY ordering ";
			$res 		= $this->db->query($sql);
			$dtchg	 	= $this->db->fetchArray($res);
			
			// die(print_r($_GET,1).print_r($dtself,1).print_r($dtchg,1));
						
			if($this->db->numRows($res) > 0){
				#tukar
				$sqla ="UPDATE kelompok_matrix SET ordering = '".$dtchg['ordering']."' WHERE idkelompok='".$dtself['idkelompok']."'";
				$sqlb ="UPDATE kelompok_matrix SET ordering = '".$dtself['ordering']."' WHERE idkelompok='".$dtchg['idkelompok']."'";
		
				// die(print_r($_GET,1).print_r($sqla,1).print_r($sqlb,1));
				
				$message = ($this->db->query($sqla) && $this->db->query($sqlb)) 
					? 'mengubah urutan Indikator sukses' 
					: 'mengubah urutan Indikator gagal'.ERROR_TAG;
			} else {
				$message = 'mengubah urutan Indikator gagal'.ERROR_TAG.' : sudah yang terakhir';
			}

			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				echo json_encode([
					'message' => $message,
				]);
				die();
			} else {
				echo "<script>alert('data tersimpan');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		} else {
			if (isset($_GET['ajaxOn'])) {
				// dipanggil melalui ajax
				echo json_encode([
					'message' => ('akses ke indikator ditolak'.ERROR_TAG),
				]);
				die();
			} else {
				echo "<script>alert('data gagal disimpan, tidak ada akses');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/kelompok'>");				
			}
		}	
	}

	function pilahKelompok($mode_kelompok)
	{
		switch ($mode_kelompok) {
			case 'sipd' : 
				$this->title .= ' SIPD'; 
				$this->table = 'kelompok';
			break;
			case 'supd' : 
				$this->title .= ' SUPD'; 
				$this->table = 'kelompok_supd';
			break;
			case 'matrik' : 
				$this->title .= ' SKPD';
				$this->table = 'kelompok_matrix';
			break;
			case 'kabupaten' : 
				$this->title .= ' Distrik';
				$this->table = 'kelompok_kabupaten';
			break;
		}
		$this->mode_kelompok = $mode_kelompok;
	}

	function Manage()
	{
		# grid & manajemen data
		$this->title 	= '<i class="fa fa-th-list"></i> &nbsp; Matrik Kelompok Indikator';

		$TemplatWaras1 = TemplatWaras1::init();
		$TemplatWaras1->set_root($this);

		$md_kelp = $this->scr->filter(strtolower($_GET['cntmode']));
		$this->pilahKelompok($md_kelp);

		$use_seleksi_instansi = false; // khusus kabupaten sesuaikan dengan kelompok_kabupaten data
		$use_seleksi_kelompok = false;
		$use_seleksi_kabupaten = false;
		$judul_table = "";
		$notes = "";

		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$this->title .= ' (admin)';
			switch ($this->mode_kelompok) {
				case 'sipd' : 
					$use_seleksi_kelompok = true; 
					$notes .= "<li>indikator / elemen data di dalam SIPD atau SUPD merupakan ketetapan dari pusat, mengganti / mengubah indikator perlu dikoordinasikan lebih lanjut</li>";
					$judul_table = "Matrik Kelompok Indikator SIPD";
				break;
				case 'supd' : 
					$use_seleksi_kelompok = true; 	
					$notes .= "<li>indikator / elemen data di dalam SIPD atau SUPD merupakan ketetapan dari pusat, mengganti / mengubah indikator perlu dikoordinasikan lebih lanjut</li>";
					$judul_table = "Matrik Kelompok Indikator SUPD";
				break;
				case 'matrik':
					$use_seleksi_instansi = true; 
					$notes .= "<li class='text-green'>Indikator yang ada adalah yang disepakati oleh Pusdalisbang dan Instansi terkait</li>";
					$judul_table = "Matrik Kelompok Indikator SKPD";
				break;
				case 'kabupaten':
					$use_seleksi_instansi = true;
					$judul_table = "Matrik Kelompok Indikator Distrik";
				break;
			}

		} else if ($this->userAkses == 'instansi') {
			// $this->title .= ' : '.$this->activeInstansi['nama_instansi'];

			// $sql_kelompok 	= "SELECT * FROM kelompok k
			// 					JOIN `users` u ON k.iduser = u.iduser	
			// 					WHERE urai LIKE '%".$this->db->escape_string($keyword)."%' 
			// 					AND u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->db->escape_string($this->activeInstansi['idinstansi'])."')";

		} else if ($this->userAkses == 'bidang') {
			// $sql_kelompok 	= "SELECT * FROM kelompok WHERE urai LIKE '%".$this->db->escape_string($keyword)."%'";
		} 

		// manajement per instansi (MATRIK), baca link dari dasboard / data indikator 
		$selected_instansi = $_GET['instansi'];

		$seleksi_instansi = null;
		if ($use_seleksi_instansi) {
			// instansi seleksi
	 		// instansi : idinstansi, instansi, singkatan_instansi 
	 		/*
	 			[anovedit][workaround]
	 			hitung berapa banyak data matrik yg dimiliki skpd, yg nantinya digunakan untuk pengelompokan,
	 			skpd berdasarkan mana skpd yg sudah isi matrik dan mana yg belum.
	 		*/
	 		$seleksi_instansi = $this->db->ci3db->query("SELECT a.*,
	 			(
	 				select (CASE WHEN count(b.idkelompok) > 0 THEN 1 ELSE 0 END)
	 				from {$this->table} b
	 				where b.pk_instansi = a.idinstansi and b.idparent = 0
 				) as matrix
	 			from instansi a
	 			order by
	 			matrix desc,
	 			a.kode_urusan,a.kode_suburusan,a.kode_organisasi asc
	 		");
	 	}

	 	$seleksi_kelompok = null;
	 	if ($use_seleksi_kelompok) {
		 	//opsi by jenis kelompok
		 	$started_id = 0; // 4 = 8 kelp data
			$seleksi_kelompok = $this->db->ci3db
			->from($this->table)
			->where(['idparent' => $started_id])
			->get();
	 	}

	 	/*
			[anovedit][workaround][note:]
	 		rencananya, saya mau mengembalikan ${autocomplete_indikator}
	 	*/
	 	$autocomplete_indikator = '';
	 	$is_use_any = $use_seleksi_instansi || $use_seleksi_kelompok || $use_seleksi_kabupaten;
	 	if ($is_use_any) {
	 		$notes .= "<li>untuk mencari indikator bisa menggunakan pencarian dibawah ini, ketik.. dan pilihan indikator akan muncul</li>";
		}

 		$TemplatWaras1->load(ROOT_PATH.'/themes/'.THEME.'/_/kelompok.tpl', [
 			'use_seleksi_instansi' => $use_seleksi_instansi,
 			'use_seleksi_kelompok' => $use_seleksi_kelompok,
 			'use_seleksi_kabupaten' => $use_seleksi_kabupaten,
 			'is_use_any' => $is_use_any,
 			'selected_instansi' => $selected_instansi,
 			'seleksi_instansi' => &$seleksi_instansi,
 			'seleksi_kelompok' => &$seleksi_kelompok,
 			'judul_table' => $judul_table,
 			'notes' => $notes,
 		]);
	}

	function getJSON($id)
	{
		# ajax handler
		$jmode = $_GET['ajaxmode'];
		switch ($jmode) {
			case 'listkelompok':
				# code...
				return $this->_listKelompok();
			break;			
			case 'listkelompok_formula':
				return $this->_listKelompokFormula();
			break;
			case 'listsubkelompok':
				# code...
				return $this->_listSubKelompok($id);
			break;
			case 'tabelkelompok' :
				$type 	= $_GET['type'];
				$tbmode = $_GET['tbmode'];
				$id 	= $_GET['id'];
				return $this->_loadTableKelompok($id,$type,$tbmode);
			break;			
			case 'tabelkelompokinstansi' :
				$type 	= $_GET['type'];
				$tbmode = $_GET['tbmode'];
				$id 	= $_GET['id'];
				return $this->_loadTableKelompok($id,$type,$tbmode);
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
			case 'ubahpublikasi':
				return $this->_ubahPublikasi($id);
			break;			
			case 'moveup':
				return $this->up();
			break;			
			case 'movedown':
				return $this->down();
			break;			
			case 'legendupdate':
				return $this->_legendUpdate();
			break;		
			case 'legendsave':
				return $this->_legendSave();
			break;			
			case 'legendremove':
				return $this->_legendRemove();
			break;				
			// case 'movedown':
			// 	return $this->down();
			// break;			
			// case 'movedown':
			// 	return $this->down();
			// break;
			default:
				# code...
			break;
		}
	}
	
	function hasAksesOnKelompok($idkelompok)
	{
		/** cek akses user ke indikator 
		* - load kelompok berdasarkan idkelompok dan iduser pada instansi
		* - digunakan pada modul ?
		* - 
		*/

		$datausr  = $this->auth->getDetail();
		$hasAkses = false;
		if ($datausr['idgroup'] == 1) {
			//akses admin, all granted
			$hasAkses = true;

		} else if ($datausr['idgroup'] == 2) {
			// instansi, cek seluruh user dibawah instansi
			$qKelompok = "SELECT idkelompok
				FROM kelompok k
				WHERE k.idkelompok= {$this->db->ci3db->escape($idkelompok)}
				AND k.pk_instansi = {$this->db->ci3db->escape($datausr['idinstansi'])}";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) > 0) $hasAkses = true;

		} else if ($datausr['idgroup'] == 3) {
			/* [anovedit][?] ada yg aneh, seharusnya "ON (u.iduser = k.iduser)", tapi ntahlah, saya belum tau untuk apa */
			// bidang, cek hanya user
			$qKelompok = "SELECT idkelompok
				FROM kelompok k
				JOIN users u ON (k.iduser = u.iduser)
				WHERE k.idkelompok = '{$this->db->escape_string($idkelompok)}'
				AND u.iduser = '{$this->db->escape_string($datausr['iduser'])}'";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) > 0) $hasAkses = true;
		}

		return $hasAkses;
	}

	private function _listKelompok()
	{
		// membuat list json kelompok berdasrkan ura dicari
		// table kelompok : idkelompok, idparent, urai, formula, satuan 
		$keyword 	= $_GET['keyword']; //<!-- input keyword 
		$md_kelp 	= $this->scr->filter(strtolower($_POST['tbmode']));
		$this->pilahKelompok($md_kelp);

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT * FROM {$this->table} WHERE urai LIKE '%{$this->db->escape_string($keyword)}%'";
		} else if ($this->userAkses == 'instansi') {
			$sql_kelompok 	= "SELECT *
				FROM kelompok_matrix k
				WHERE k.urai LIKE '%{$this->db->escape_string($keyword)}%'
				AND k.pk_instansi = {$this->db->ci3db->escape($this->activeInstansi['idinstansi'])}";

		} else if ($this->userAkses == 'bidang') {
			// $sql_kelompok 	= "SELECT * FROM kelompok WHERE urai LIKE '%".$this->db->escape_string($keyword)."%'";
		} 

		$res_kelompok	= $this->db->query($sql_kelompok);
		$json_data = array();
		while ($rec_kelompok = $this->db->fetchAssoc($res_kelompok)) {
			$id = $rec_kelompok['idkelompok'];
			$json_data[$id]['label'] = $rec_kelompok['urai'];
			$json_data[$id]['value'] = $rec_kelompok['urai'];
			$json_data[$id]['id'] = $id;
		}
		sort($json_data);
		print json_encode($json_data);
	}

	private function _listKelompokFormula()
	{
		// membuat list json kelompok berdasrkan ura dicari dan kelompok tertentu.. 
		// where : idparent = nn, iduser == xx

		$keyword = $_GET['keyword']; //<!-- input keyword 
		$scr_initial_idkelompok = $this->scr->filter($_POST['initial_idkelompok']);
		$md_kelp = $this->scr->filter(strtolower($_POST['tbmode']));
		$this->pilahKelompok($md_kelp);

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT * FROM {$this->table}
				WHERE urai LIKE '%{$this->db->escape_string($keyword)}%'
				AND idparent <> '{$scr_initial_idkelompok}'
				AND iduser IN (
					SELECT iduser
					FROM {$this->table}
					WHERE idkelompok = '{$scr_initial_idkelompok}'
				)
				LIMIT 0,25";
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

	/*
		[anovedit][?][note]
		saat laman diakases, otomatis ada ajax yg mengarah kesini, nah untuk apa?
	*/
	private function _listSubKelompok($idkelompok) {
		// list opsi sub/jenis urusan
		$md_kelp 	= $this->scr->filter(strtolower($_GET['tbmode']));
		$this->pilahKelompok($md_kelp);
		$this->cekAkses();

		if ($idkelompok > 0) {
			if ($idkelompok < 5) {
				$sqlsub = "SELECT * FROM {$this->table} WHERE idparent={$this->db->ci3db->escape($idkelompok)} ORDER BY ordering ASC";
			} else if ($this->userAkses == 'admin') {
				// akses admin 
				$sqlsub = "SELECT * FROM {$this->table} WHERE idparent={$this->db->ci3db->escape($idkelompok)} ORDER BY ordering ASC";
			} else if ($this->userAkses == 'instansi'){
				// $idinstansi = $this->loadinstansi;

				// $sqlsub = "SELECT * FROM ".$this->table." k 
				// 			LEFT JOIN users u ON k.iduser = u.iduser 
				// 			WHERE u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->loadinstansi."') 
				// 			AND k.idparent=".$id;
			} else if ($this->userAkses == 'bidang') {
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

	/*
		[anovedit][guide][note]
		saat klik [lihat] nanti ada ajax kesini, untuk menampilkan data.
		respon yg diberikan adalah struktur table (thead,tbody) dalam bentuk json.

		user:admin, type:instansi, tbmode:matrik,kabupaten
	*/
	private function _loadTableKelompok($iddata,$type='kelompok',$md_kelp="sipd")
	{
		//load dataTabale untuk ditampilkan
		$this->cekAkses();
		$this->typeData  = $type; 
		$this->pilahKelompok($md_kelp);

		$c_td  = 0;
 		$add_th_by_mode = "";
  	switch ($this->mode_kelompok) {
  		case "sipd" :
  			$add_th_by_mode = "
  			";
  		break;
  		case "supd" :
  			$add_th_by_mode = "
  			";
  		break;
  		case "matrik" :
  			$c_td += 3;
  			$add_th_by_mode = "
				<!--th rowspan=2>penggunaan</th-->
				<th rowspan=2>Publikasi</th>
				<th rowspan=2>Aksi</th>
  			";
  		break;
  		case "kabupaten" :
  			$c_td += 3;
  			$add_th_by_mode = "
				<!--th rowspan=2>penggunaan</th-->
				<th rowspan=2>Publikasi</th>
				<th rowspan=2>Aksi</th>
  			";
  		break;
  	}

 		// seleksi index data : 1. kelompok 2. skpd
 		$add_link = $parent_urai = "";
 	 	if ($this->typeData == 'kelompok') {
 			// detail data kelompok : parent Data
			$sqlKelompok = "SELECT
				a.idkelompok,
				a.urai,
				k.idkelompok as started_id
				FROM {$this->table} k
				LEFT JOIN {$this->table} a ON (a.idkelompok = k.idparent)
				WHERE k.idkelompok={$this->db->escape_string($iddata)}
				ORDER BY k.ordering,k.idkelompok";

 		} else if ($this->typeData == 'instansi') {
			/* detail data skpd :
			* akses : admin -> skpd atau skpd bersangkutan
			*/
 			$this->loadinstansi = $iddata ;
 			if ($this->userAkses == 'admin') {
 				switch ($this->mode_kelompok) {
 					case 'matrik':
 						# code...
						$sql_ins = "SELECT * FROM instansi i WHERE i.idinstansi ='{$this->db->escape_string($iddata)}'";
						$res_ins = $this->db->query($sql_ins);
						$data_ins = $this->db->fetchAssoc($res_ins);
						$add_link = '<button type="button" class="btn-flat-info" onClick="addKelompokInInstansi('.$iddata.')"><i class="fa fa-plus"></i> Tambah Indikator</button>';
						$parent_urai = "{$data_ins['nama_instansi']}";

						/* [anovedit][rewrite][users_table_ignored] */
						$sqlKelompok = $this->db->ci3db
						->from('kelompok_matrix')
						->select([
							'ordering',
							'idkelompok as started_id',
							'urai',
						])
						->where([
							'idparent' => 0,
							'pk_instansi' => $iddata,
						])
						->order_by('ordering,idkelompok');
 						break;
 					case 'kabupaten':
						$sql_ins = "SELECT * FROM instansi i WHERE i.idinstansi ='{$this->db->escape_string($iddata)}'";
						$res_ins = $this->db->query($sql_ins);
						$data_ins = $this->db->fetchAssoc($res_ins);
						$add_link = '<button type="button" class="btn-flat-info" onClick="addKelompokInInstansi('.$iddata.')"><i class="fa fa-plus"></i> Tambah Indikator</button>';
						$parent_urai = "{$data_ins['nama_instansi']}";

						/* [anovedit][rewrite][users_table_ignored] */
						$sqlKelompok = $this->db->ci3db
						->from('kelompok_kabupaten')
						->select([
							'ordering',
							'idkelompok as started_id',
							'urai',
						])
						->where([
							'idparent' => 0,
							'pk_instansi' => $iddata,
						])
						->order_by('ordering,idkelompok');
 						break;
					default: break;
 				}
 			}
 			//else if ($this->userAkses == 'instansi') {
 				// berikan aksed hanya kepada kelompok-nya milik skpd yg aktif
 				// 
 				// $sqlKelompok = "SELECT a.idkelompok,a.urai,k.idkelompok as started_id FROM `kelompok` k
					// 	JOIN `kelompok` a ON a.idkelompok = k.idparent
		 		// 		WHERE k.idparent IN (1,2,3,4) 
		 		// 		AND k.idinstansi='".$this->loadinstansi."'";	
 			// }
 		}

	 	$resKelompok = $sqlKelompok->get();

	 	// print_r($rowKelompok);
 		// die ($sqlKelompok);

	 	// table header, sesuaikan dengan pilihan tahun
 		$thead = "
 			<tr>
 				<th rowspan=2>No</th>
				<th rowspan=2>Kelompok/Sub Kelompok</th>
				<th rowspan=2>Satuan</th>
				<th rowspan=2>Formula</th>
				<th rowspan=2 class='not-visible no-print'>{ID}</th>
				<!--th colspan=2>SUPD (sinkronisasi)</th-->
				<!--th colspan=1>Sumber Data</th-->
				{$add_th_by_mode}
			</tr>
			<tr>
				<!--th>urusan - sub urusan</th-->
				<!--th>bidang supd</th-->
				<!--th>Instansi</th-->
			</tr>
		";
				// <th>Bidang</th>

		// table body
		$tbody = "
			<tr class='kelompok_parent'>
				<td></td><td colspan=5><b>{$parent_urai}</b></td>
				<td class='text-center'>{$add_link}</td>
			</tr>";//<td></td>

		$this->detailKelompok = '';
		$this->row_id = 1;

		if($resKelompok) foreach ($resKelompok->result_array() as $b) {
			$this->_lisdetailkelompok($b['started_id']);
		}

 		$tbody .= $this->detailKelompok; //join

		print json_encode(array('header' => $thead, 'body' => $tbody));
	}

	function _lisdetailkelompok($iddata,$tab=0)
	{
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
			//SIPD
			switch ($this->mode_kelompok) {
		  		case "sipd" :
						$sqlKlp	= "SELECT k.*,k_supd.idkelompok AS idkelompok_supd
							FROM {$this->table} k 
							LEFT JOIN konversi_kelompok kk ON kk.idkelompok_sipd = k.idkelompok
							LEFT JOIN kelompok_supd k_supd ON k_supd.idkelompok = kk.idkelompok_supd
							WHERE k.idkelompok={$this->db->escape_string($iddata)}";
		  		break;
		  		case "supd" :
						$sqlKlp	= "SELECT k.*,k.idkelompok AS idkelompok_supd
							FROM {$this->table} k 
							WHERE k.idkelompok={$this->db->escape_string($iddata)}";
		  		break;
		  		case "matrik" :
						$sqlKlp	= "SELECT
							k.*,
							k_supd.idkelompok AS idkelompok_supd,
							i.nama_instansi
							FROM {$this->table} k 
							LEFT JOIN konversi_kelompok kk ON kk.idkelompok_matrix = k.idkelompok 
							LEFT JOIN kelompok_supd k_supd ON k_supd.idkelompok = kk.idkelompok_supd 
							LEFT JOIN users u ON u.iduser = k.iduser
							LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
							WHERE k.idkelompok={$this->db->escape_string($iddata)}";
			  		break;
			  		case "kabupaten" :
			  			$add_th_by_mode = "
							<!--th rowspan=2>penggunaan</th-->
							<th rowspan=2>Aksi</th>
			  			";
			  		break;
		  	}
			// //LEFT JOIN users u ON u.iduser = k.iduser
			// 		LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
			// 		LEFT JOIN instansi_bidang ib ON ib.idbidang_instansi = u.idbidang_instansi 
			$sql_child= "SELECT idkelompok FROM {$this->table} WHERE idparent={$this->db->escape_string($iddata)} ORDER BY ordering ASC";

		} else if ($this->typeData == 'instansi') {
			/* [anovedit][override][users_table_ignored][bidang_is_suburusan] */
			$sqlKlp	= "SELECT k.*,u.*,i.*,
					k.pk_instansi as idinstansi, -- [legacy]
					s.idsub_urusan as idbidang_instansi -- [legacy]
					FROM {$this->table} k
					LEFT JOIN users u ON (u.iduser = k.iduser)
					LEFT JOIN instansi i ON (i.idinstansi = k.pk_instansi)
					LEFT JOIN suburusan_bidang s ON (s.kode_urusan = i.kode_urusan and s.kode_suburusan = i.kode_suburusan)
					WHERE idkelompok= {$this->db->ci3db->escape($iddata)}";
			$sql_child = "SELECT k.idkelompok
				FROM {$this->table} k
				WHERE k.idparent = {$this->db->ci3db->escape($iddata)}
				AND k.pk_instansi = {$this->db->ci3db->escape($this->loadinstansi)} -- [workaround]
				ORDER BY k.ordering ASC";
			// $this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();
		}

		// current kelompok
		$QKelompok = $this->db->query($sqlKlp);
		$dataKelompok = $this->db->fetchAssoc($QKelompok);

		/* [anovedit] penomoran */
		if (!array_key_exists($dataKelompok['idparent'], $this->table_kelompok_numb)) $this->table_kelompok_numb[$dataKelompok['idparent']] = 0;
		$this->table_kelompok_numb[$dataKelompok['idparent']]++;
		$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']];
		/* [anovedit] tingkatan */
		if ($dataKelompok['idparent'] == 0) {
			$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = 0;
		} else {
			$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = $this->table_kelompok_levl[$dataKelompok['idparent']]+1;
		}

		// current kelompok child
		$res_child 	= $this->db->query($sql_child);
		$n_child 	= $this->db->numRows($res_child);

		if (!empty($iddata)) {
			// URUSAN, bisa lebih dari 1 dalam 1 indikator SUPD
			$sql_urusan = "SELECT
				GROUP_CONCAT(CONCAT(su.kode_urusan,'.',su.kode_suburusan,'. ',su.urai) SEPARATOR '</br>') AS urusan
				FROM urusan su
			 	JOIN urusan u ON u.kode_urusan = su.kode_urusan AND u.kode_suburusan = ''
			 	WHERE CONCAT(su.kode_urusan,'-',su.kode_suburusan) 
		 		IN (
			 		SELECT CONCAT(kode_urusan,'-',kode_suburusan)
			 		FROM urusan_kelompok_supd
			 		WHERE idkelompok = '{$dataKelompok['idkelompok_supd']}'
			 	);";
			$res_urusan = $this->db->query($sql_urusan);
			$dataUrusan = $this->db->fetchAssoc($res_urusan);
			$dataKelompok['urusan'] = $dataUrusan['urusan'];

			// SUPD, bisa lebih dari 1 dalam 1 indikator
			$sql_supd = "SELECT
				GROUP_CONCAT(CONCAT(s.urai,' : ',sub.urai) SEPARATOR '</br>') AS supd_urai
				FROM supd sub
				JOIN supd s ON (s.idsupd = sub.idparent)
				WHERE sub.idsupd IN (
					SELECT idsupd
					FROM supd_kelompok
					WHERE idkelompok  = '{$dataKelompok['idkelompok_supd']}'
				)";
			$res_supd = $this->db->query($sql_supd);
			$dataSUPD = $this->db->fetchAssoc($res_supd);
			$dataKelompok['supd'] = $dataSUPD['supd_urai'];

			// PENGGUNAAN
			$penggunaan_sql = "SELECT
				GROUP_CONCAT(penggunaan) as penggunaan
				FROM penggunaan_indikator
				WHERE idpenggunaan IN (
					SELECT idpenggunaan
					FROM penggunaan_kelompok
					WHERE idkelompok  = '{$dataKelompok['idkelompok']}'
				)";
			$penggunaan_qry = $this->db->query($penggunaan_sql);
			$resPenggunaan = $this->db->fetchAssoc($penggunaan_qry);
			$dataKelompok['penggunaan'] = $resPenggunaan['penggunaan'];

			// SUB URUSAN
			$sub_sql = "SELECT * FROM suburusan_bidang WHERE idsub_urusan  = '{$dataKelompok['idsub_urusan']}'";
			$sub_qry = $this->db->query($sub_sql);
			$resSubUrs = $this->db->fetchAssoc($sub_qry);
			$dataKelompok['sub_urusan'] = $resSubUrs['sub_urusan'];

			$this->detailKelompok .= $this->rowData($iddata,$dataKelompok,$tab);
			$this->elemennumber++;
		}

		if ($n_child > 0) {
			// parent kelompok
			// idkelompok ini masih memiliki child didalamannya
  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
  			$this->_lisdetailkelompok($dataDetail['idkelompok'],$tab+1);
  		}
		}

	}

	private function rowData($iddata,$dataKelompok,$tab=0,$number=0)
	{

		if ($this->userAkses == 'admin') {
			$action = '
				<button onClick="moveUp('.$dataKelompok['idkelompok'].');" class="btn btn-success btn-flat btn-no-padding" data-toggle="tooltip" data-placement="top" title="Up"><i class="fa fa-arrow-up"></i></button>
				<button onClick="moveDown('.$dataKelompok['idkelompok'].');" class="btn btn-warning btn-flat btn-no-padding" data-toggle="tooltip" data-placement="top" title="Down"><i class="fa fa-arrow-down"></i></button>
				<button type="button" class="btn btn-primary btn-flat btn-no-padding" onClick="editKelompok('.$iddata.')" data-toggle="tooltip" data-original-title="Edit Indikator"><i class="fa fa-edit"></i></button>
				<button type="button" class="btn btn-info btn-flat btn-no-padding" onClick="addKelompokChild('.$iddata.')" data-toggle="tooltip" data-original-title="Tambah Sub Indikator"><i class="fa fa-plus"></i></button>
				<button type="button" class="btn btn-danger btn-flat btn-no-padding " onClick="trigger_removeKelompok('.$iddata.')" data-toggle="tooltip" data-original-title="Hapus Indikator"><i class="fa fa-minus"></i></button>
				';

			if ($dataKelompok['publish'] == 1) {
				$label = 'Ya';
				$text_color = 'text-green';
				$icon = 'fa-eye';

			} else {
				$label = 'Tidak';
				$text_color = 'text-red';
				$icon = 'fa-eye-slash';
			}

  		$publish_action ='
  			<button type="button" class="btn-flat btn-no-padding '.$text_color.'" onClick="changePublish('.$iddata.')" data-toggle="tooltip" data-original-title="click untuk mengubah Status Publikasi Indikator">
  				<i class="fa '.$icon.'"></i> '.$label.'
  			</button>
  		';

		} else if ($this->userAkses == 'instansi') {
			die();
  			// $action = '
	  		// 	<button type="button" class="btn-flat-info" onClick="editKelompok('.$iddata.')"><i class="fa fa-edit"></i></button>
	  		// ';
		}

  	// coloring row
  	$bg_row = '';
  	if ($_POST['cntmode'] == 'ins') {$bg_row = 'bg-info text-blue';}
  	// if ($_POST['cntmode'] == 'upd') {$bg_row = 'bg-warning';}

  	$add_colom_by_mode = "";
  	switch ($this->mode_kelompok) {
  		case "sipd" :
  			$add_colom_by_mode = "";
  		break;
  		case "supd" :
  			$add_colom_by_mode = "";
  		break;
  		case "matrik" :
  			$add_colom_by_mode = "
				<!--td class='penggunaan'>{$dataKelompok['penggunaan']}</td-->
				<td class='publish text-center'>{$publish_action}</td>
				<td nowrap class='text-center'>{$action}</td>
  			";
  		break;
  		case "kabupaten" :
  			$add_colom_by_mode = "
				<!--td class='penggunaan'>{$dataKelompok['penggunaan']}</td-->
				<td class='publish text-center'>{$publish_action}</td>
				<td nowrap class='text-center'>{$action}</td>
  			";
  		break;
  	}

  	$urai = $this->scr->utf8_encode($dataKelompok['urai']);
  	$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']);
		$return = "
		<tr id='kelompok_{$dataKelompok['idkelompok']}' data-pk_parent='{$dataKelompok['idparent']}' data-pk='{$dataKelompok['idkelompok']}' data-levl='{$dataKelompok['levl']}' data-numb='{$dataKelompok['numb']}' data-tab='{$dataKelompok['levl']}' class='{$bg_row}'>
		<td>{$this->elemennumber}</td>
		<td class='urai'>{$levl} {$dataKelompok['numb']}. <span>{$urai}</span></td>
		<td align=center class='satuan'>{$dataKelompok['satuan']}</td>
		<td align=center class='formula'>{$dataKelompok['formula']}</td>
		<td align=center class='not-visible no-print'>{$dataKelompok['idkelompok']}</td>
		<!--td class='urusan'>{$dataKelompok['urusan']}</td-->
		<!--td class='supd'>{$dataKelompok['supd']}</td-->
		<!--td class='nama_instansi'>{$dataKelompok['nama_instansi']}</td-->
		{$add_colom_by_mode}
		</tr>";
		// <td class='bidang'>".$dataKelompok['bidang']."</td>

		// $dataKelompok['sub_urusan']

		return $return;
  }

	private function _subUrusan($idinstansi)
	{
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

		} else $option = "";

  	return json_encode(array('options'=>$option)); 
	}

	private function _listKelompokAnalisa() {
		// membuat list json kelompok berdasrkan ura dicari
		// table kelompok : idkelompok, idparent, urai, formula, satuan 
		// persiapkan untuk custom analisa by user
		$keyword 		= $_GET['keyword']; //<!-- input keyword 

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT *
				FROM kelompok_matrix
				WHERE urai LIKE '%{$this->db->escape_string($keyword)}%'
				AND (formula <> '' OR idkelompok IN (SELECT DISTINCT(idkelompok) FROM kelompok_detail))";

			$res_kelompok	= $this->db->query($sql_kelompok);
			$json_data = array();
			while ($rec_kelompok = $this->db->fetchAssoc($res_kelompok)) {
				$id = $rec_kelompok['idkelompok'];
				// $json_data[$id]['name'] = $rec_kelompok['urai'];
				$json_data[$id]['label'] = $rec_kelompok['urai'];
				$json_data[$id]['value'] = $rec_kelompok['urai'];
				// $json_data[$id]['value'] = $id;
				$json_data[$id]['id'] = $id;
			}
			sort($json_data);
		} else {
			$json_data['message'] = 'akses ditolak!';
		} 
		print json_encode($json_data,JSON_NUMERIC_CHECK);
	}

	private function _ubahPublikasi($id)
	{
		/*
		* id = idkelompok
		*/
		$scr_idkelompok = $this->scr->filter($id);
		// $scr_publish = $this->scr->filter(strtolower($_POST['publish']));
		$md_kelp = $this->scr->filter(strtolower($_POST['tbmode']));
		$this->pilahKelompok($md_kelp);

		// load data indikator
		$sql = "SELECT k.*
			FROM {$this->table} k 
			WHERE k.idkelompok='{$scr_idkelompok}'";
		$result = $this->db->query($sql);
		$data = $this->db->fetchArray($result);
		
		$haskakses 	= $this->hasAksesOnKelompok($idkelompok);

		if ($haskakses) {
			// switch publish
			$new_publish = ($data['publish'] == 1) ? 0 : 1 ;
			$sql_upd = "UPDATE {$this->table} SET publish = '{$new_publish}' WHERE idkelompok='{$scr_idkelompok}'";
			$res_upd = $this->db->query($sql_upd);

			if ($res_upd) {
				if ($new_publish == 1) {$label = 'Ya'; $text_color = 'text-green'; $icon = 'fa-eye';}
				else {$label = 'Tidak'; $text_color = 'text-red'; $icon = 'fa-eye-slash';}
		  		$publish_action ='
		  			<button type="button" class="btn-flat-info '.$text_color.'" onClick="changePublish('.$scr_idkelompok.')" data-toggle="tooltip" data-original-title="click untuk mengubah Status Publikasi Indikator"><i class="fa '.$icon.'"></i>&nbsp;'.$label.'</button>
		  		';
		  		$message = 'ubah publish data berhasil!';
		  		$content = $publish_action;
			} else {
		  		$message = 'ubah publish data gagal!'.ERROR_TAG;
			}
		} else {
			$message = 'tidak ada akses ke indikator!'.ERROR_TAG;
		}
	  	
		if (isset($_POST['ajaxOn'])) {
			echo json_encode([
				'content' => $content,
				'message' => $message,
			]);
			die();
		}
	}

	private function _legendUpdate() {
		/*
		* id = idkelompok
		*/

		$scr_idkelompok = $this->scr->filter($_POST['idkelompok']);
		$haskakses 	= $this->hasAksesOnKelompok($scr_idkelompok);

		if ($haskakses) {
			// update legend
			$sql_upd = "UPDATE map_legend 
						SET
							label='{$this->scr->filter($_POST['label'])}',
							batas_bawah='{$this->scr->filter($_POST['batas_bawah'])}',
							batas_atas='{$this->scr->filter($_POST['batas_atas'])}',
							warna='#{$this->scr->filter($_POST['warna'])}'
						WHERE 
							idkelompok='{$scr_idkelompok}' AND idlegend='{$this->scr->filter($_POST['idlegend'])}'";

			// die($sql_upd);

			$res_upd = $this->db->query($sql_upd);

			if ($res_upd) {
		  		$message = 'ubah legenda berhasil!';
			} else {
		  		$message = 'ubah legenda gagal!'.ERROR_TAG;
			}
		} else {
			$message = 'tidak ada akses ke indikator!'.ERROR_TAG;
		}
	  	
		if (isset($_POST['ajaxOn'])) {
			echo json_encode([
				'message' => $message,
			]);
			die();
		}
	}
	
	private function _legendSave() {
		/*
		* id = idkelompok
		*/

		$scr_idkelompok = $this->scr->filter($_POST['id']);
		$haskakses 	= $this->hasAksesOnKelompok($scr_idkelompok);
		$initid = 0;

		if ($haskakses) {
			// insert new legend
			$result		= $this->db->query("SELECT max(idlegend) as mxid FROM map_legend");
			$dord		= $this->db->fetchArray($result);
			$maxid   	= ($dord['mxid'] <> '')?$dord['mxid'] +1 : 1;	
			$initid    	= $maxid;

			$sql_ins = "INSERT INTO map_legend 
						SET
							label='{$this->scr->filter($_POST['label'])}',
							batas_bawah='{$this->scr->filter($_POST['batas_bawah'])}',
							batas_atas='{$this->scr->filter($_POST['batas_atas'])}',
							warna='#{$this->scr->filter($_POST['warna'])}',
							idkelompok='{$scr_idkelompok}',
							idlegend='{$maxid}'";

			// die($sql_ins);

			$res_upd = $this->db->query($sql_ins);
			$content = "";

			if ($res_upd) {
				$data_legend = array(
					'idkelompok' => $scr_idkelompok,
					'idlegend' => $maxid,
					'label' => $_POST['label'],
					'batas_atas' => $_POST['batas_atas'],
					'batas_bawah' => $_POST['batas_bawah'],
					'warna' => ''.$_POST['warna'],
					);
				$idlegend = $data_legend['idlegend'];
				$content .= "
					<tr class='row_legend' id='legend_{$idlegend}' data-id='{$idlegend}'>
						<td>
						<form id='frm_legend_{$idlegend}' hidden>
							<input type='hidden' name='idkelompok' value='{$data_legend['idkelompok']}' form='frm_legend_{$idlegend}'>
							<input type='hidden' name='idlegend' value='{$idlegend}' form='frm_legend_{$idlegend}'>
						</form>
						<span>{$data_legend['label']}</span>
							<input type='text' class='form-control' name='label' id='label' placeholder='isikan label/keterangan' value='{$data_legend['label']}' form='frm_legend_{$idlegend}' hidden>
						</td>
						<td class='col-xs-2'><span>{$data_legend['batas_bawah']}</span>
							<input type='number' class='form-control' name='batas_bawah' id='batas_bawah' placeholder='isikan batas bawah' value='{$data_legend['batas_bawah']}' form='frm_legend_{$idlegend}' hidden></td>
						<td class='col-xs-2'><span>{$data_legend['batas_atas']}</span>
							<input type='number' class='form-control' name='batas_atas' id='batas_atas' placeholder='isikan batas atas' value='{$data_legend['batas_atas']}' form='frm_legend_{$idlegend}' hidden></td>
						<td class='col-xs-2 warna' style='background-color:{$data_legend['warna']};'><span>{$data_legend['warna']}</span>
							<input type='text' class='form-control' style='background-color:transparent;' name='warna' id='warna' placeholder='pilih warna' value='#{$data_legend['warna']}' form='frm_legend_{$idlegend}' hidden></td>
						<td class='col-xs-1 text-center' nowrap>
							<button type='button' class='btn_update btn btn-sm btn-info btn-flat'><i class='fa fa-save' hidden></i></button>
							<button type='button' class='btn_cancel btn btn-sm btn-warning btn-flat'><i class='fa fa-close' hidden></i></button>
							<button type='button' class='btn_edit btn btn-sm btn-success btn-flat'><i class='fa fa-edit'></i></button>
							<button type='button' class='btn_delete btn btn-sm btn-danger btn-flat'><i class='fa fa-close'></i></button>
						</td>
					</tr>";
		  		$message = 'menambahkan legenda berhasil!';
			} else {
		  		$message = 'menambahkan legenda gagal!'.ERROR_TAG;
			}
		} else {
			$message = 'tidak ada akses ke indikator!'.ERROR_TAG;
		}

		if (isset($_POST['ajaxOn'])) {
			echo json_encode([
				'message' => $message,
				'content' => $content,
				'initid' => $initid,
			]);
			die();
		}
	}

	private function _legendRemove() {
		/*
		* id = idkelompok
		*/

		$scr_idkelompok = $this->scr->filter($_POST['idkelompok']);
		$haskakses 	= $this->hasAksesOnKelompok($scr_idkelompok);

		if ($haskakses) {
			// update legend
			$sql_del = "DELETE FROM map_legend 
						WHERE 
							idkelompok='{$scr_idkelompok}' AND idlegend='{$this->scr->filter($_POST['idlegend'])}'";

			// die($sql_del);

			$res_upd = $this->db->query($sql_del);

			if ($res_upd) {
		  		$message = 'menghapus legenda berhasil!';
			} else {
		  		$message = 'menghapus legenda gagal!'.ERROR_TAG;
			}
		} else {
			$message = 'tidak ada akses ke indikator!'.ERROR_TAG;
		}
	  	
		if (isset($_POST['ajaxOn'])) {
			echo json_encode([
				'message' => $message,
			]);
			 die();
		}
	}
}
