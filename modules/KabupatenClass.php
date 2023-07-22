<?php
/*
	[20180925023726][anovedit][note]
	$this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();
*/

Class KabupatenClass extends ModulClass
{
	/* [anovedit] handle penomoran, dan tingkatan */
	private $table_kelompok_levl = []; // loop level
	private $table_kelompok_numb = []; // loop number

	// [anovedit] saya pindah sini, supaya tidak mengganggu getter.
	private $elemennumber = 1;

	function Manage()
	{
		# grid & manajemen data
		$this->title = '<i class="fa fa-th-large"></i> &nbsp; Data Distrik';

		$TemplatWaras1 = TemplatWaras1::init();
		$TemplatWaras1->set_root($this);

		# fixAkses
		$this->checkAkses();

		# default layout
		$use_opsi_kabupaten = false;
		$seleksi_kabupaten 	= "";
		$use_opsi_instansi 	= false;
		$seleksi_SKPD 		= "";
		$use_opsi_kelompok 	= false;
		$seleksi_kelompok 	= "";
		$show_table 	= false;

 		$seleksi_tahun = "";

		$has_data 	= false;

		$q_jenis = null;
		if ($this->userAkses == 'admin') {
			$this->title .= ' (admin)';
			// load data kosong
			$tableData = array('header' => '', $body => '', 'opsidata' => '');
	 		//opsi by jenis kelompok
	 		$started_id = 0; // 4 = 8 kelp data
	 		$q_jenis = "SELECT *
	 			FROM kelompok_kabupaten
	 			WHERE idparent={$this->db->escape_string($started_id)}";

	 		$use_opsi_kabupaten = true;
			$use_opsi_instansi 	= true;
			$use_opsi_kelompok = true;
			$show_table = true;
			$has_data = true;

		} else if ($this->userAkses == 'instansi') {
			$data_instansi = $this->activeInstansi;
			$this->title .= " ({$data_instansi['nama_instansi']})";

	 		$started_id = 0; // 4 = 8 kelp data
	 		$q_jenis = "SELECT *
	 			FROM kelompok_kabupaten
	 			WHERE pk_instansi = {$this->db->ci3db->escape($data_instansi['idinstansi'])}";

	 		$use_opsi_kabupaten = true;
			$use_opsi_kelompok = true;
			$show_table = true;
			$has_data = true;
		} else $has_data = false;

		$TemplatWaras1->data([
			'hakakses' => $this->userAkses,
			'use_opsi_kelompok' => $use_opsi_kelompok,
			'show_table' => $show_table,
		]);

		$option_kabupaten = null;
		if ($use_opsi_kabupaten) {
	  	$option_kabupaten = $this->db->ci3db
	  	->from('kabupaten_gis')
	  	->select(['kabupaten','kodepemda'])
	  	->order_by('kodepemda')
	  	->where('kodepemda !=','')
	  	->get();
 		}

 		$option_skpd = null;
 		if ($use_opsi_instansi) {
 			/* [anovsiradj][override][users_table_ignored] */
			$option_skpd = $this->db->ci3db->query("SELECT a.*,
				(
					select (CASE WHEN count(b.idkelompok) > 0 THEN 1 ELSE 0 END)
					from kelompok_kabupaten b
					where b.pk_instansi = a.idinstansi and b.idparent = 0
				) as matrix
				from instansi a
				order by
				matrix desc,
				a.kode_urusan,a.kode_suburusan,a.kode_organisasi asc
			");
	 	}

	 	$option_jenis = null;
	 	if ($use_opsi_kelompok && $q_jenis) $option_jenis = $this->db->ci3db->query($q_jenis);

	 	$tahun_data = $tahun_list = null;
	 	if ($show_table) {
			$tahun_data = $this->db->ci3db
			->from('kelompok_detail_kabupaten')
			->select(['min(tahun) as `min`', 'max(tahun) as `max`'])
			->get()
			->row_array();
			$tahun_data = array_map('intval', $tahun_data);

			$tahun_list = (int) date('Y');
			$tahun_list = ['max' => $tahun_list, 'min' => 2000];

			// [anovedit][note:] supaya data pada tahun sebelumnya juga muncul, kalau ada
			if ($tahun_data['min'] > 0 && $tahun_data['min'] < $tahun_list['min']) $tahun_list['min'] = $tahun_data['min'];
		}

		$TemplatWaras1->data([
			'available' => $has_data,
			'tahun_data' => $tahun_data,
			'tahun_list' => $tahun_list,
			'option_kabupaten' => &$option_kabupaten,
			'option_skpd' => &$option_skpd,
			'option_jenis' => &$option_jenis,
		]);
		$TemplatWaras1->load(ROOT_PATH.'/themes/'.THEME.'/_/kabupaten.tpl');
	}

	function getJSON($id)
	{
		// ajax
		$jmode = $_GET['ajaxmode'];

		switch ($jmode) {
			case 'elemen':
				return $this->_listelement($id);
			break;
			case 'chart':
				return $this->_chart($id);
			break;			
			case 'jenis_urusan':
				return $this->_urusan($id);
			break;
			case 'updatedetail':
				return $this->_updateDetail($id);
			break;			
			case 'loadtable':
				return $this->_loadData($id);
			break;				
			case 'loadtableskpd':
				return $this->_loadData($id,'instansi');
			break;					
			case 'loadtablekabupaten':
				return $this->_loadData($id,'kabupaten');
			break;			
			case 'export':
				// export data kelompok ke file excell
				$type 	= $_GET['type'];
				$id 	= $_GET['id'];
				// tahun data
				$tahun_export = array();
				$tahun_awal = (int) $_GET['tahun_awal'];
				$tahun_akhir = (int) $_GET['tahun_akhir'];
				for ($xy = $tahun_awal; $xy <= $tahun_akhir; $xy++) $tahun_export[] = $xy;
				return $this->excelExport($type,$id,$tahun_export);
			break;
			default:
			break;
		}
	}

	function checkAkses()
	{
		/* AKSES KELOMPOK 
		 * uraikan berdasarkan akses, 1:admin, 2:operator 3:skpd, 4:instansi_vertikal\
		 */
		$datausr = $this->auth->getDetail();
		$Qgroup = $this->db->query("SELECT * FROM `group` WHERE idgroup={$datausr['idgroup']}");
		$dataGroup = $this->db->fetchAssoc($Qgroup);

		if ($datausr['idgroup'] == 1) {
			$this->userAkses = 'admin';
		} else if ($datausr['idgroup'] == 2) {
			$this->userAkses = 'instansi';
			$sqlInstansi = "SELECT *
				FROM instansi as i
				LEFT JOIN users as u ON (u.idinstansi = i.idinstansi)
				WHERE u.iduser={$this->db->ci3db->escape($datausr['iduser'])}";
			$qInstansi = $this->db->query($sqlInstansi);
			$this->activeInstansi = $this->db->fetchAssoc($qInstansi);

		} else if ($datausr['idgroup'] == 3) {
			$this->userAkses = 'bidang';
		}
	}

	function hasAksesOnKelompok($idkelompok)
	{
		/** cek akses user ke kelompok 
		* - load kelompok berdasarkan idkelompok dan isuser pada instansi
		*/

		$datausr 	= $this->auth->getDetail();
		$hasAkses 	= false;
		if ($datausr['idgroup'] == 1) {
			//akses admin, all granted
			$hasAkses = true;

		} else if ($datausr['idgroup'] == 2) {
			// instansi, cek seluruh user dibawah instnasi
			$qKelompok = "SELECT idkelompok FROM kelompok_kabupaten k
				WHERE k.idkelompok={$this->db->ci3db->escape($idkelompok)}
				and k.pk_instansi = {$this->db->escape_string($datausr['idinstansi'])}";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) > 0) $hasAkses = true;

		} else if ($datausr['idgroup'] == 3) {
			// bidang, cek hanya user
			$qKelompok = "SELECT idkelompok FROM kelompok_kabupaten k
				WHERE k.idkelompok= {$this->db->ci3db->escape($idkelompok)}
				AND k.pk_instansi={$this->db->ci3db->escape($datausr['iduser'])}";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) >0) $hasAkses = true;
		}

		return $hasAkses;
	}

	// [anovedit] user:admin, type:instansi,kabupaten
	private function _loadData($iddata,$type='kelompok')
	{
		// ajax
		// range tahun
		$tahun_awal = (int) $_GET['tahun_awal'];
		$tahun_akhir = (int) $_GET['tahun_akhir'];
		$th_data = array();
		if (!empty($tahun_awal) && !empty($tahun_akhir)) {
			for ($xt = $tahun_awal;  $xt <= $tahun_akhir; $xt++) {$th_data[] = $xt;}
		}
		$tableData = $this->_loadTableData($iddata,$type,$th_data);
		return json_encode($tableData); 
	}

	private function _loadTableData($iddata,$type='kelompok',$tahunData=array()){
		//load dataTabale untuk ditampilkan
		$this->checkAkses();

		$this->typeData  = $type; 
		$this->tahunData = $tahunData;

		// [anovedit][note] tidak tereksekusi, karena tahun adalah array, dan $->length > 0 (selalu)
		if (empty($this->tahunData)) {
			//opsi pilihan tahun
			$qData = $this->db->query("SELECT
				MIN(tahun) as `min`,
				MAX(tahun) as `max`
				FROM kelompok_detail");
			$tahunData = $this->db->fetchAssoc($qData);
			$th_data = array();
			for ($xt = $tahunData['min'];  $xt <= $tahunData['max']; $xt++) $th_data[] = $xt;
			$this->tahunData = $th_data;
		}

		// get tahun dari submit atau table
 		$tahun_count = count($this->tahunData);

 		$tahun_header = $th_checkbox =  "";
 		foreach ($this->tahunData as $key => $value) {
 			$tahun_header .= "<th>{$value}</th>";
 			$th_checkbox .= "<input id='tahun_chart[]' name='tahun_chart[]' class='tahun_chart' type='checkbox' checked=true value='{$value}' hidden>";
 		}

 		// seleksi index data : 1. kelompok 2. skpd
 		$this->idkabupaten = 0;
 		if ($type == 'kelompok') {
 			// detail data kelompok : parent Data
 			// 	$tabel_header_name = "{INDIKATOR}";
			// $sqlKelompok = "SELECT a.idkelompok,a.urai,k.idkelompok as started_id,k.urai as title
			// 				FROM `kelompok_kabupaten` k
			// 				JOIN `kelompok_kabupaten` a ON a.idkelompok = k.idparent
	 		// 					WHERE k.idkelompok=".$this->db->escape_string($iddata);
	 		// 					;

 		} else if ($type == 'instansi') {
 			/* detail data skpd :
 			 * akses : admin -> skpd atau skpd bersangkutan
 			 */
 			$sqlCurrentIns 	= "SELECT *
 				FROM instansi i
 				WHERE i.idinstansi = '{$this->db->escape_string($iddata)}'";
			$resCurrentIns 	= $this->db->query($sqlCurrentIns);
			$dataCurrentIns = $this->db->fetchAssoc($resCurrentIns);
 			$tabel_header_name = $dataCurrentIns['nama_instansi'];

 			$this->loadinstansi = $iddata ;
 			if ($this->userAkses == 'admin') {
 				/* [anovedit][users_table_ignored][bugfixed:] tambah idparent, supaya data tidak dobel */
 				// load parent
				$sqlKelompok = "SELECT
					a.idkelompok AS started_id,
					a.urai
					FROM kelompok_kabupaten a
					WHERE a.pk_instansi = {$this->db->ci3db->escape($iddata)}
					and a.idparent = 0";
 			} 

		} else if ($type == 'kabupaten') {
			/*
			* akses : admin -> skpd atau skpd bersangkutan
			*/
			if ($iddata > 0) {
	 			$sqlCurrentKab 	= "SELECT *
	 				FROM kabupaten_gis kab
	 				WHERE kab.kodepemda ='{$this->db->escape_string($iddata)}'";
				$resCurrentKab 	= $this->db->query($sqlCurrentKab);
				$dataCurrentKab = $this->db->fetchAssoc($resCurrentKab);
	 			$tabel_header_name = $dataCurrentKab['kabupaten'];
 			} else if ($iddata == -1) {
	 			$tabel_header_name = "Semua Distrik";
 			}

 			$this->load_kabupaten = $iddata;
 			if ($this->userAkses == 'admin') {
 				// load parent
	 			$sqlKelompok = "SELECT
	 				0 AS started_id,
	 				kab.kabupaten AS urai
	 				FROM kabupaten_gis kab
					WHERE kab.kodepemda ='{$this->db->escape_string($iddata)}'";

 			} else if ($this->userAkses == 'instansi') {
 				// berikan aksed hanya kepada kelompok-nya milik skpd yg aktif
 				/* [anovedit][override][users_table_ignored] */
 				$sqlKelompok = "SELECT
 					idkelompok AS started_id
 					FROM kelompok_kabupaten
 					WHERE pk_instansi = {$this->db->ci3db->escape($this->activeInstansi['idinstansi'])}";
 			}

 			$this->idkabupaten = $iddata;
 		}

		$resKelompok = $this->db->query($sqlKelompok);

	 	// print_r($rowKelompok);
 		// die ($sqlKelompok);
	 	
	 	// export, import, print button
	 	$data_option = '
	 		<button class="btn btn-flat btn-info" onclick="javascript:window.print();"><i class="fa fa-print"> &nbsp; cetak</i></button>
	 		<button class="btn btn-flat btn-warning" onclick="excelImport(\''.$iddata.'\',\''.$type.'\','.min($this->tahunData).','.max($this->tahunData).');"><i class="fa fa-file-excel-o"> &nbsp; import</i></button>
	 		<button class="btn btn-flat btn-success" onclick="excelExport(\''.$iddata.'\',\''.$type.'\','.min($this->tahunData).','.max($this->tahunData).');"><i class="fa fa-file-excel-o"> &nbsp; export</i></button>';

	 	// table header, sesuaikan dengan pilihan tahun
 		$thead = "
 			<tr>
				<th rowspan=2>N</th>
				<th rowspan=2 colspan=2>Indikator / Distrik</th>
				<th colspan={$tahun_count}>Tahun{$th_checkbox}</th>
				<th rowspan=2>Satuan</th>
				<!--th rowspan=2>Pengentri</th-->
				<th rowspan=2>Tanggal Update</th>
				<th rowspan=2>Aksi</th>
			</tr>
			<tr>{$tahun_header}</tr>";

		// table body
		$empty_td = str_repeat("<td></td>", count($this->tahunData));
		$tbody = "
			<tr class='kelompok_parent'>
				<td></td>
				<td colspan=2><b>{$tabel_header_name}</b></td>
				{$empty_td}
				<td></td>
				<td></td>
				<td></td>
			</tr>";

		// load kabupaten
		$this->listKabupaten = array();
		$sql_kab = "SELECT
			kodepemda,
			kabupaten
			FROM kabupaten_gis
			where kodepemda != ''
			ORDER BY kodepemda ASC";
		$res_kab = $this->db->query($sql_kab);
		while($data_kab = $this->db->fetchAssoc($res_kab)) {
			$this->listKabupaten[$data_kab['kodepemda']] = $data_kab['kabupaten'];
		}

		$tahun_title = (min($this->tahunData) == max($this->tahunData)) ? ' Tahun '.max($this->tahunData) : (' Tahun ' . min($this->tahunData). ' s/d '. max($this->tahunData));
		$data_title .= 'Tabel data Distrik - ' . $tahun_title;

		$this->detailKelompok = '';
		$this->row_id = 1;
		while ($rowKelompok = $this->db->fetchAssoc($resKelompok)) {
	 		$this->_lisdetailtelement($rowKelompok['started_id']);
		}
 		$tbody .= $this->detailKelompok; //join

		return array('header' => $thead, 'body' => $tbody, 'opsidata' => $data_option, 'title' => $data_title);
	}

	private function _lisdetailtelement($iddata,$tab=0)
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

		$type = $this->typeData;

		if ($type == 'kelompok') {
			// $sqlKlp	= "SELECT k_kab.*,i.* FROM kelompok_kabupaten k_kab
			// 		LEFT JOIN users u on u.iduser = k_kab.iduser 
			// 		LEFT JOIN instansi i on i.idinstansi = u.idinstansi 
			// 		WHERE k_kab.idkelompok=".$this->db->escape_string($iddata);
			// $sql_child	= "SELECT * FROM kelompok_kabupaten WHERE idparent=".$this->db->escape_string($iddata);

		} else if ($type == 'instansi') {
			$sqlKlp	= "SELECT *
				FROM kelompok_kabupaten k_kab
				LEFT JOIN instansi i ON (i.idinstansi = '{$this->db->escape_string($this->loadinstansi)}')
				WHERE k_kab.idkelompok = {$this->db->escape_string($iddata)}";
			/* [anovedit][override][users_table_ignored] */
			$sql_child = "SELECT *
				FROM kelompok_kabupaten k
				WHERE k.idparent = {$this->db->ci3db->escape($iddata)}
				and k.pk_instansi = {$this->db->ci3db->escape($this->loadinstansi)}";

		} else if ($this->typeData == 'kabupaten') {
			/* [anovedit][override][users_table_ignored] */
			$sqlKlp = "SELECT a.*,b.*
				FROM kelompok_kabupaten a
				LEFT JOIN instansi b on (b.idinstansi = a.pk_instansi)
				WHERE a.idkelompok = {$this->db->ci3db->escape($iddata)}";
			$sql_child	= "SELECT * FROM kelompok_kabupaten WHERE idparent={$this->db->ci3db->escape($iddata)}";
		}

		/* [anovedit][!][bugfixed:] penyakit akut, tidak pernah initialize variables. */
		if (!isset($this->numbering)) $this->numbering = array();

		$QKelompok 		= $this->db->query($sqlKlp);
		$dataKelompok 	= $this->db->fetchAssoc($QKelompok);

		$res_child 	= $this->db->query($sql_child);
		$n_child 	= $this->db->numRows($res_child);

  	if ($n_child > 0) {
  		// parent kelompok
			// idkelompok ini masih memiliki child didalamannya
			$this->numbering[$iddata] = 0;
			if ($this->typeData == 'kabupaten' && !empty($dataKelompok['urai'])) {
				$val_td = "";
				foreach ($this->tahunData as $th_lookup) {
					// input detail jika ada data terdetail
					$val 	= $this->_evalFormula($this->load_kabupaten,$dataKelompok['formula'],$th_lookup);
					$data 	= number_format($val,2,'.','');
					$val_td .= "<td class='nilai'>{$data}</td>";
				}

				/* [anovedit][note:] OPEN:PARENTKELOMPOK-BY-DISTRIK */

				$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']] = ($this->table_kelompok_numb[$dataKelompok['idparent']] ?: 0)+1; /* [anovedit] penomoran */
				$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = ($dataKelompok['idparent'] == 0) ? 0 : ($this->table_kelompok_levl[$dataKelompok['idparent']]+1); /* [anovedit] tingkatan */
				$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']);

				$this->detailKelompok .= "
					<tr class='row_parent row-level-{$dataKelompok['levl']}'>
						<td>{$this->elemennumber}</td>
						<td colspan=2>{$levl} {$dataKelompok['numb']}. {$dataKelompok['urai']}</td>
						{$val_td}
						<td align=center>{$dataKelompok['satuan']}</td>
						<!--td>{$dataKelompok['nama_instansi']}</td-->
						<td></td>
						<td></td>
					</tr>";

				$this->elemennumber++; // [anovedit] supaya saya tidak bingung

				/* [anovedit][note:] CLOSE:PARENTKELOMPOK-BY-DISTRIK */

			} else if (!empty($dataKelompok['urai'])) {

				/* [anovedit][note:] OPEN:PARENTKELOMPOK-BY-SKPD */

				$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']] = ($this->table_kelompok_numb[$dataKelompok['idparent']] ?: 0)+1; /* [anovedit] penomoran */
				$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = ($dataKelompok['idparent'] == 0) ? 0 : ($this->table_kelompok_levl[$dataKelompok['idparent']]+1); /* [anovedit] tingkatan */
				$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']);

				$empty_td = str_repeat("<td></td>", count($this->tahunData));
				$this->detailKelompok .= "
					<tr class='row_parent row-level-{$dataKelompok['levl']}'>
						<td>{$this->elemennumber}</td>
						<td colspan=2>{$levl} {$dataKelompok['numb']}. {$dataKelompok['urai']}</td>
						{$empty_td}
						<td align=center>{$dataKelompok['satuan']}</td>
						<!--td>{$dataKelompok['nama_instansi']}</td-->
						<td></td>
						<td></td>
					</tr>";

					$this->elemennumber++; // [anovedit] supaya saya tidak bingung

					/* [anovedit][note:] CLOSE:PARENTKELOMPOK-BY-SKPD */
			}

  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
				$this->numbering[$iddata] += 1;
  			$this->_lisdetailtelement($dataDetail['idkelompok'],$tab+1);
  		}

  	} else if (!empty($iddata)) {
  			// depest child (kelompok ini adalah yg terdetail)
  			// seleksi , per kelompok - kab atau kab - kelompok 

  			if ($type == 'kabupaten' && $this->idkabupaten != -1) {
  				$action = '<button type="button" class="btn-flat-info" onClick="openChart('.$iddata.',\''.$this->idkabupaten.'\')"><i class="fa fa-bar-chart-o"></i></button>';

  				/* [anovedit][note:] OPEN:CHILDKELOMPOK-BY-DISTRIK */

  				$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']] = ($this->table_kelompok_numb[$dataKelompok['idparent']] ?: 0)+1; /* [anovedit] penomoran */
  				$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = ($dataKelompok['idparent'] == 0) ? 0 : ($this->table_kelompok_levl[$dataKelompok['idparent']]+1); /* [anovedit] tingkatan */
  				$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']);

					$this->detailKelompok .= "
						<tr id='rows_{$this->row_id}' data-row-id='{$this->row_id}' class='rows_data'>
							<td>{$this->elemennumber}</td>
							<td colspan=2>{$levl} {$dataKelompok['numb']}. {$dataKelompok['urai']}</td>";

					$this->elemennumber++; // [anovedit] supaya saya tidak bingung

					/* [anovedit][note:] CLOSE:CHILDKELOMPOK-BY-DISTRIK */

		  		$sqlDetail = "SELECT
		  			postdate,
		  			tahun,
		  			nilai
		  			FROM kelompok_detail_kabupaten
		  			WHERE idkabupaten = '{$this->scr->filter($this->load_kabupaten)}'
		  			AND idkelompok={$iddata}";
		  		$qDetail = $this->db->query($sqlDetail);
		  		$data_arr = $postdate = array();
		  		while($rDetail = $this->db->fetchAssoc($qDetail)) {
		  			$data_arr[$rDetail['tahun']] = $rDetail['nilai'];
		  			$postdate[] = $rDetail['postdate'];
		  		}
		  		$maxpost = max($postdate);
					foreach ($this->tahunData as $key => $tahun_detail) {
						// input detail jika ada data terdetail
						$lendata = strlen($data_arr[$tahun_detail]);
						$this->detailKelompok .= "
							<td class='td_input'>
								<input data-idkelompok='{$iddata}' data-tahun='{$tahun_detail}' data-default='{$data_arr[$tahun_detail]}' data-kodekabupaten='{$this->load_kabupaten}' id='{$iddata}_{$this->load_kabupaten}_{$tahun_detail}' class='detail_input {$tahun_detail}' onkeypress='return JustNumbers(event);' onChange='updateData(this.id)' value='{$data_arr[$tahun_detail]}' size={$lendata}>
							</td>";
					}
					$this->detailKelompok .= "
							<td align=center>{$dataKelompok['satuan']}</td>
							<!--td>{$dataKelompok['nama_instansi']}</td-->
							<td nowrap class='text-center'>{$maxpost}</td>
							<td nowrap class='text-center'>{$action}</td></tr>";
  				$this->row_id++;

  			} else if ($this->typeData == 'instansi' || $this->idkabupaten == -1) {
					// rekap data/indikator
					$td_content = "";
					foreach ($this->tahunData as $th_rekap) {
						$rekap_value = $this->_getRekap($iddata,$th_rekap,$dataKelompok['metode_kalkulasi']);
						$td_content .= "<td class='nilai' style='text-align:right;'>{$rekap_value}</td>";
					}

					/* [anovedit][note:] OPEN:CHILDKELOMPOK-BY-SKPD */

					$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']] = ($this->table_kelompok_numb[$dataKelompok['idparent']] ?: 0)+1; /* [anovedit] penomoran */
					$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = ($dataKelompok['idparent'] == 0) ? 0 : ($this->table_kelompok_levl[$dataKelompok['idparent']]+1); /* [anovedit] tingkatan */
					$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']);

					$this->detailKelompok .= "
						<tr class='row_rekap row-level-{$dataKelompok['levl']}'>
							<td>{$this->elemennumber}</td>
							<td colspan=2>{$levl} {$dataKelompok['numb']}. {$dataKelompok['urai']} {opsi:s/h-kab}</td>
							{$td_content}
							<td class='satuan'>{$dataKelompok['satuan']}</td>
							<!--td>{$dataKelompok['nama_instansi']}</td-->
							<td></td><!-- [anovedit] nama_instansi:replacement -->
							<td colspan=2></td>
						</tr>";

					$this->elemennumber++; // [anovedit] supaya saya tidak bingung

					/* [anovedit][note:] CLOSE:CHILDKELOMPOK-BY-SKPD */


					// load data per kabupaten
					$no_kab = 0;
					$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']+1);

					foreach ($this->listKabupaten as $kodepemda => $kabupaten) {
						$action = '<button type="button" class="btn-flat-info" onClick="openChart('.$iddata.',\''.$kodepemda.'\')"><i class="fa fa-bar-chart-o"></i></button>';

						$no_kab++;
						$this->detailKelompok .= "
							<tr id='rows_{$this->row_id}' data-row-id='{$this->row_id}' class='rows_data'>
								<td></td>
								<td colspan=2>{$levl} {$no_kab}. {$kabupaten}</td>";

		  			$sqlDetail = "SELECT
		  				postdate,
		  				tahun,
		  				nilai FROM kelompok_detail_kabupaten
		  				WHERE idkabupaten = '{$this->scr->filter($kodepemda)}'
		  				AND idkelompok={$iddata}";
		  			$qDetail = $this->db->query($sqlDetail);
		  			$data_arr = $postdate = array();
		  			while($rDetail = $this->db->fetchAssoc($qDetail)) {
		  				$data_arr[$rDetail['tahun']] = $rDetail['nilai'];
		  				$postdate[] = $rDetail['postdate'];
		  			}
		  			$maxpost = max($postdate);
						foreach ($this->tahunData as $key => $tahun_detail) {
							// input detail jika ada data terdetail
							$lendata = strlen($data_arr[$tahun_detail]);
							$this->detailKelompok .= "
								<td class='td_input'>
									<input data-idkelompok='{$iddata}' data-default='{$data_arr[$tahun_detail]}' data-tahun='{$tahun_detail}' data-kodekabupaten='{$kodepemda}' id='{$iddata}_{$kodepemda}_{$tahun_detail}' class='detail_input {$tahun_detail}' onkeypress='return JustNumbers(event);' onChange='updateData(this.id)' value='{$data_arr[$tahun_detail]}' size={$lendata}>
								</td>";
						}
						$this->detailKelompok .= "
							<td align=center>{$dataKelompok['satuan']}</td>
							<!--td></td-->
							<td nowrap class='text-center'>{$maxpost}</td>
							<td nowrap class='text-center'>{$action}</td>
						</tr>";
  					$this->row_id++;
					}
  			} // $this->typeData == ?

  	}
  		// get / return var : $this->detailKelompok;
	}

	private function _getRekap($iddata,$th_rekap,$method)
	{
		$sql_rep = "SELECT
			SUM(nilai) AS nilai
			FROM kelompok_detail_kabupaten
			WHERE idkelompok='{$this->scr->filter($iddata)}'
			AND tahun='{$this->scr->filter($th_rekap)}'";
		$res_rep = $this->db->query($sql_rep);
		$data_rep = $this->db->fetchAssoc($res_rep);

		$r_val = 0;
		switch ($method) {
			case 'SUM' :
				$r_val = $data_rep['nilai'];
			break;			
			case 'AVG' :
				// [anovedit][workaround] bagi berdasarkan jumlah data.
				$r_val = number_format(($data_rep['nilai']/$this->db->numRows($res_rep)),2,'.','');
			break;
		}
		return $r_val;
	}

	private function _evalFormula($idkabupaten,$formula,$tahun,&$akumulasiFormula = '', &$tab = ''){
		// ubah formula menjadi value : 
		// contoh : {idkelompok}*{idkelompok} => 100*12
		// print $akumulasiFormula."<br>";
		preg_match_all("/\{([0-9]+)\}/", $formula, $arrmatches);
		if($akumulasiFormula == '') $akumulasiFormula = $formula;
		foreach ($arrmatches[1] as $idkelompok) {

			// cek ada formulanya atau tidak
			$sqlKelompok = "SELECT formula
				FROM kelompok_kabupaten
				WHERE idkelompok='{$this->db->escape_string($idkelompok)}'";
			$rKelompok = $this->db->query($sqlKelompok);
			$dataKelompok = $this->db->fetchAssoc($rKelompok);
			$formulaK = $dataKelompok['formula'];

			// jika kelompok itu masih punya formula maka rekursif
			if($formulaK <> '') {
				$akumulasiFormula = str_replace('{'.$idkelompok.'}', $formulaK, $akumulasiFormula);
				$nilai = $this->_evalFormula($idkabupaten,$formulaK,$tahun,$akumulasiFormula,$tab);

			} else {
				// parsing idkelompoknya
				$sqlDetail = "SELECT nilai
			   	FROM kelompok_detail_kabupaten
			   	WHERE idkelompok='{$this->db->escape_string($idkelompok)}'
			   	AND idkabupaten = '{$this->db->escape_string($idkabupaten)}'
			   	AND	tahun = '{$this->db->escape_string($tahun)}'";
		   	$rDetail = $this->db->query($sqlDetail);
		   	$dataDetail = $this->db->fetchAssoc($rDetail);
		   	$nilai = $dataDetail['nilai'];	
		   	$akumulasiFormula = str_replace('{'.$idkelompok.'}', $nilai, $akumulasiFormula);    
			}	
		}

		// evaluate
		$sqlEval = "SELECT ($akumulasiFormula) as hasilperhitungan";
		$rEval = $this->db->query($sqlEval);
		$dataEval = $this->db->fetchAssoc($rEval);	
		$hasilperhitungan = $dataEval['hasilperhitungan'];

		if(!is_numeric($hasilperhitungan)) $hasilperhitungan = $akumulasiFormula;

		return $hasilperhitungan;
	}

	private function _chartSource($id,$idkabupaten=0,$tahun_chart=array(),$single_series=false){
		/**
		* load chart parameter
		* id = id kelompok di tabel keompok
		* tahun_chart = array tahun chart, eg (2015,2016)
		*/

		/* [anovedit][users_table_ignored] */
		// id, idparent, urai, formula, satuan
		$sql 	= $this->db->query("SELECT
			*,
			i.nama_instansi as sumber
			FROM kelompok_kabupaten k
			JOIN instansi i ON (i.idinstansi = k.pk_instansi)
			WHERE idkelompok={$id}");
		$rTabel = $this->db->fetchAssoc($sql);

		$q_kab = $this->db->query("SELECT *
			FROM kabupaten_gis
			WHERE kodepemda='{$idkabupaten}'");
		$data_kab = $this->db->fetchAssoc($q_kab);

		$judul 	= $rTabel['urai']." ({$data_kab['kabupaten']})";
		$satuan = $rTabel['satuan'];
		$sumber = (!empty($rTabel['sumber'])) ? $rTabel['sumber'] : "Pusdalisbang (*)" ;

		$chart_type = "line";

		// tahun chart
		// tahun default jika kosong, tampilkan semua data
		if (empty($tahun_chart)) {
			$th_sql = $this->db->query("SELECT GROUP_CONCAT(DISTINCT(tahun) ORDER BY tahun ASC) as tahun FROM kelompok_detail");
			$th_Tabel = $this->db->fetchAssoc($th_sql);
			$tahun_chart =  explode(',', $th_Tabel['tahun']);
		}

		// type chart berdasarkan jumlah item yg dibandingkan
		if (count($tahun_chart) == 1) $chart_type = "column"; //<-- colum untuk single item / atau single year
		foreach ($tahun_chart as $tahun) $ye[] = $tahun;
  		
		/* cek
		 */ 		
		$series = array(); 
		$data = array(); 
		$chart_type = "line";
		    
		// satuan jika kosong
		// $satuan = (empty($satuan)) ? $rTabel['satuan'] : $satuan ;

		//load detail pertahun-nya
		foreach($tahun_chart as $tahun) {
			$nilai = 0;
			$qVal = $this->db->query("SELECT *
				FROM kelompok_detail_kabupaten
				WHERE tahun={$tahun}
				AND idkabupaten='{$idkabupaten}'
				AND idkelompok={$rTabel['idkelompok']}");
			if ($rVal = $this->db->fetchArray($qVal)) {
		      $nilai = number_format($rVal['nilai'],2,'.',''); //$rVal['nilai'];//
	    }
	    $data[] = $nilai;    
		}

	 	// series data untuk chart
		$series[0]['name'] = $rTabel['urai'];
		$series[0]['data'] = $data;
		sort($series);

		return [
			'series' => $series,
			'judul' => $judul,
			'sumber' => $sumber,
			'satuan' => $satuan,
			'kategori' => $ye,
			'type' => $chart_type,
			'data' => $data,
		];
	}

	private function _chart($id)
	{
		/*
		* CHART JSON OUTPUT
		*/
		$tahun = $_GET['tahun_chart'];
		$idkabupaten = $_GET['idkabupaten'];
		$data = $this->_chartSource($id,$idkabupaten,$tahun);
		return json_encode($data,JSON_NUMERIC_CHECK); //print angka sebagai numeric
	}

	private function _urusan($id){
		// list opsi sub/jenis urusan
		$this->checkAkses();
		if ($id > 0) {
			if ($id < 5) {
				$sqlsub = "SELECT *
					FROM kelompok_kabupaten
					WHERE idparent={$this->db->escape_string($id)}
					ORDER BY ordering ASC";

			} else if ($this->userAkses == 'instansi') {
				// $idinstansi = $this->loadinstansi;

				// $sqlsub = "SELECT * FROM kelompok k 
				// 			LEFT JOIN users u ON k.iduser = u.iduser 
				// 			WHERE u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->loadinstansi."') 
				// 			AND k.idparent=".$id;
			} else {
				$sqlsub = "SELECT *
					FROM kelompok_kabupaten
					WHERE idparent={$this->db->escape_string($id)}
					ORDER BY ordering ASC";
			}

			$qsub = $this->db->query($sqlsub);
	  		while ($dataSUrusan = $this->db->fetchAssoc($qsub)) {
	  			$option .= "<option value='{$dataSUrusan['idkelompok']}'>{$dataSUrusan['urai']}</option>";
	  		}
		} else {
			$option = "";
		}
  	return json_encode(array('options'=>$option)); 
	}

	function excelExport($typeExport='kelompok',$idexport='719',$tahun_export=array()) {
		/*
		* exporting content to excell format
		* typeExport : type jenis data, eg : skpd/instansi, kelompok
		* idexport : id kelompok sumber data, bisa id instansi / idkelompok
		*/
		$this->checkAkses();

		// global vars
		$this->typeExport = $typeExport;
		// jika kelompok, maka berdasarkan iddata, 
		// jika instnasi/bidang : diselect seluruh kelompok dalam instansi/bidang nya
		$this->idexport = $idexport;
		$this->tahunExport = array_map('intval', $tahun_export);

		// tahun default jika kosong, tampilkan semua data
		if (empty($this->tahunExport)) {
			$th_sql = "SELECT GROUP_CONCAT(DISTINCT(tahun) ORDER BY tahun ASC) as tahun FROM kelompok_detail";
			$th_res = $this->db->query($th_sql);
			$th_Tabel = $this->db->fetchAssoc($th_res);
			$this->tahunExport =  array_map('intval', explode(',', $th_Tabel['tahun']));
		}

		// format nama file, sesuaikan, skpd x, bidang n. etc
		if ($this->typeExport == 'kelompok') {
			// $sqlKlp	= "SELECT * FROM kelompok 
			// 			WHERE idkelompok=".$this->db->escape_string($idexport);
			// $resKelp 		= $this->db->query($sqlKlp);
			// $dataKelompok 	= $this->db->fetchAssoc($resKelp);
			// $excel_filename = $dataKelompok['idkelompok'].".".$dataKelompok['urai']."_".min($this->tahunExport)."_".max($this->tahunExport)."_distrik.xlsx";
			// $judul_tabel	= $dataKelompok['urai'];
			// $idkelompok_start = $idexport;

		} else if ($this->typeExport == 'instansi') {
			$sqlIns	= "SELECT *
				FROM instansi
				WHERE idinstansi={$this->db->escape_string($idexport)}";
			$resIns 		= $this->db->query($sqlIns);
			$dataInstansi 	= $this->db->fetchAssoc($resIns);
			$excel_filename = $dataInstansi['idinstansi'].".".$dataInstansi['nama_instansi']."_".min($this->tahunExport)."_".max($this->tahunExport)."_distrik.xlsx";
			$judul_tabel	= $dataInstansi['nama_instansi'];
			$this->exportInstansi = $idexport;

			// load kabupaten
			$this->listKabupaten = array();
			$sql_kab = "SELECT
				kodepemda,
				kabupaten
				FROM kabupaten_gis
				where kodepemda != ''
				ORDER BY kodepemda ASC";
			$res_kab = $this->db->query($sql_kab);
			while($data_kab = $this->db->fetchAssoc($res_kab)) {
				$this->listKabupaten[$data_kab['kodepemda']] = $data_kab['kabupaten'];
			}

			// load kelompok kabupaten
			/* [anovedit][users_table_ignored] */
			$sqlKelKab = "SELECT *
				FROM kelompok_kabupaten
				WHERE idparent=0
				AND pk_instansi={$this->db->ci3db->escape($idexport)}";
			$resKelKab = $this->db->query($sqlKelKab);
			// $idkelompok_start = 0 ;//$dataKelompok['started_id'];

		} else if ($this->typeExport == 'kabupaten') {
			if ($idexport > 0) {
				$sqlKab	= "SELECT *
					FROM kabupaten_gis
					WHERE kodepemda={$this->db->escape_string($idexport)}";
				$resKab 	= $this->db->query($sqlKab);
				$dataKab 	= $this->db->fetchAssoc($resKab);
				$excel_filename = $dataKab['kodepemda'].".".$dataKab['kabupaten']."_".min($this->tahunExport)."_".max($this->tahunExport).".xlsx";
				$judul_tabel	= $dataKab['kabupaten'];
				$this->exportKabupaten = $dataKab;

			} else if ($idexport == -1) {
				// $sqlKab	= "SELECT * FROM kabupaten_gis";
				// $resKab 	= $this->db->query($sqlKab);
				// $dataKab 	= $this->db->fetchAssoc($resKab);
				$excel_filename = $this->activeInstansi['idinstansi'].".".$this->activeInstansi['nama_instansi']."_Kabupaten_".min($this->tahunExport)."_".max($this->tahunExport).".xlsx";
				$judul_tabel	= 'Semua Kabupaten';

				// load kabupaten
				$this->listKabupaten = array();
				$sql_kab = "SELECT
					kodepemda,
					kabupaten
					FROM kabupaten_gis
					where kodepemda != ''
					ORDER BY kodepemda ASC";
				$res_kab = $this->db->query($sql_kab);
				while($data_kab = $this->db->fetchAssoc($res_kab)) {
					$this->listKabupaten[$data_kab['kodepemda']] = $data_kab['kabupaten'];
				}
			}
			$this->exportIdKabupaten = $idexport;

			// load kelompok kabupaten
			if ($this->userAkses == 'admin') {
				$sqlKelKab = "SELECT * FROM kelompok_kabupaten WHERE idparent=0";

			} else if ($this->userAkses == 'instansi') {
				$sqlKelKab = "SELECT *
				FROM kelompok_kabupaten
				WHERE idparent=0
				AND pk_instansi={$this->db->ci3db->escape($this->activeInstansi['idinstansi'])}";
			}
			$resKelKab = $this->db->query($sqlKelKab);
		}

		/* [anovedit][workaround][!?] kok sampai london, timezonenya */
		date_default_timezone_set(@date_default_timezone_get());

		set_time_limit(0);
		$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_sqlite;

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Create a first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Set document properties
		// dokumen diset propertiesnya untuk di cek saat import data
		//
		$this->users 	= $this->auth->getDetail();
		$kategory 		= $this->typeExport.";".$idexport.";".min($this->tahunExport)."-".max($this->tahunExport);
		$objPHPExcel->getProperties()->setCreator($this->users['username'])
									 ->setLastModifiedBy($this->users['username'])
									 ->setTitle($judul_tabel)
									 ->setSubject($excel_filename)
									 ->setDescription("file excell ini di export dari aplikasi pusdalisbang, untuk dapat dipergunakan sebagaimana mestinya.")
									 ->setKeywords("pusdalisbang;".$kategory)
									 ->setCategory($kategory);
		
		// set variabel, codes
		$xcode = $this->makeExcelFileCode($this->typeExport,$idexport,min($this->tahunExport),max($this->tahunExport));
		$objPHPExcel->getActiveSheet()->setCellValue('A1',$xcode);

		$typecode = $this->makeExcelFileCode($this->typeExport);
		$objPHPExcel->getActiveSheet()->setCellValue('A2',$typecode);

		$objPHPExcel->getActiveSheet()->setCellValue('C1',$idexport);
		$objPHPExcel->getActiveSheet()->setCellValue('D1',$this->encodeId_excel($idexport));

		//cells formats
		$head_align = array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
		);		
		$fill_color = array(
			'fill' => array(
            		'type' => PHPExcel_Style_Fill::FILL_SOLID,
            		'color' => array('rgb' => 'cccccc')
        	)
		);
		$font_bold = array(
			'font' => array(
				'bold' => true
				)
		);
		$font_bold_18 = array(
			'font' => array(
				'bold' => true,
				'size' => 18
				)
		);
		// TEST BORDERING
		$bordered_1 = array(
		      'borders' => array(
		          'allborders' => array(
		              'style' => PHPExcel_Style_Border::BORDER_THIN
		          )
		      )
		  );
		$objPHPExcel->getDefaultStyle()->applyFromArray($bordered_1);

		// set Header (mulai dari baris 2)
		$thead = array('No','Distrik / Kelompok / Sub Kelompok Data','Satuan');

		// set judul
		$closecol = chr(67+count($thead)+count($this->tahunExport)+1);
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('E1:'.$closecol.'1');
		$objPHPExcel->getActiveSheet()->setCellValue('E1', $judul_tabel);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->applyFromArray($head_align);
		$objPHPExcel->getActiveSheet()->getStyle('E1')->applyFromArray($font_bold_18);

		$xh = 2;
		foreach ($thead as $heads) {
			# code...
			$col = chr(67+$xh);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells($col.'2:'.$col.'3');
			$objPHPExcel->getActiveSheet()->setCellValue($col.'2', $heads);
			$objPHPExcel->getActiveSheet()->getStyle($col.'2')->getAlignment()->applyFromArray($head_align);
			$objPHPExcel->getActiveSheet()->getStyle($col.'2')->applyFromArray($font_bold)->applyFromArray($bordered_1);
			$xh++;
		}

		// set header tahun
		$col = chr(67+count($thead)+2);
		$closecol = chr(67+count($thead)+count($this->tahunExport)+1);
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells($col.'2:'.$closecol.'2');
		$objPHPExcel->getActiveSheet()->setCellValue($col.'2', 'Tahun');
		$objPHPExcel->getActiveSheet()->getStyle($col.'2')->getAlignment()->applyFromArray($head_align);
		$objPHPExcel->getActiveSheet()->getStyle($col.'2')->applyFromArray($font_bold);

		// set tahun
		$xt = 2;
		foreach ($this->tahunExport as $tahun) {
			$colY = chr(67+count($thead)+$xt);
			// $objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue($colY.'3', "$tahun");
			$objPHPExcel->getActiveSheet()->getStyle($colY.'3')->getAlignment()->applyFromArray($head_align);
			$objPHPExcel->getActiveSheet()->getStyle($colY.'3')->applyFromArray($font_bold);
			$xt++;
		}

		// auto width & coloring cell
 		$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
 		$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
 		$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getStyle('E1:'.$closecol.'3')->applyFromArray($fill_color);

		// hide kolom id
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setVisible(false);

		// Freeze panes
		$objPHPExcel->getActiveSheet()->freezePane('A4');

		// Rows to repeat at top
		$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 2);

		// load recrisive data
		$this->exceldata = array();
		$this->cellrowid = 0;
		while ($dataKelKab = $this->db->fetchAssoc($resKelKab)) {
			$idkelompok_start = $dataKelKab['idkelompok'];
			$this->_createExcelDataObject($idkelompok_start); //<-- load data 
		}
		// print "<pre>";print_r($this->exceldata);
		// die();

		// set Cell Data
		$started_row = 3;
		$started_coll = 5;
		foreach ($this->exceldata as $rid => $row_data) {
			$cell_row = $rid+$started_row;
			foreach ($row_data as $no => $isi) {
				$cell_col = chr(65+$no);
				$objPHPExcel->getActiveSheet()->setCellValue($cell_col.$cell_row , $isi);
			}
		}

		// disable cell, then select cell with no protection (nilainya)
		$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true); 
		// enabling locked cell
		$start_cell = chr(67+count($thead)+2).($started_row+1);
		$end_cell	= chr(67+count($thead)+2+count($this->tahunExport)-1).($started_row+count($this->exceldata));
		$objPHPExcel->getActiveSheet()->getStyle($start_cell.':'.$end_cell)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$excel_filename.'"');
		header('Cache-Control: max-age=0');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		ob_end_clean();
		$objWriter->save('php://output');
		exit;
	}

	private function _createExcelDataObject($iddata,$tab=0){
		/*
		* excell data sheet ceation
		* array format : no,kelompok/sub,satuan,tahun <- tahun x-n
		* select berdasarkan typeExport
		*/

		if ($this->typeExport == 'kelompok') {
			$sqlKlp	= "SELECT *
				FROM kelompok
				WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child = "SELECT idkelompok
				FROM kelompok_kabupaten
				WHERE idparent={$this->db->escape_string($iddata)}";

		} else if ($this->typeExport == 'instansi') {
			$sqlKlp	= "SELECT *
				FROM kelompok_kabupaten
				WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child = "SELECT idkelompok
				FROM kelompok_kabupaten k
				WHERE k.idparent={$this->db->escape_string($iddata)}";

		} else if ($this->typeExport == 'kabupaten') {
			$sqlKlp	= "SELECT *
				FROM kelompok_kabupaten
				WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child = "SELECT idkelompok
				FROM kelompok_kabupaten
				WHERE idparent={$this->db->escape_string($iddata)}";
		}

		$res_kelp = $this->db->query($sqlKlp);
		$dataKelompok = $this->db->fetchAssoc($res_kelp);
		$res_child = $this->db->query($sql_child);
		$n_child = $this->db->numRows($res_child);

		/*
			[anovedit][?] ini apa??!
			saya cari `$this->loadedIns` tidak ketemu.
		*/
		/*
		if ($tab == 0) {
			if ($this->loadedIns[$dataKelompok['iduser']] != true) {
				$sqlIns = "SELECT * FROM instansi i WHERE i.`idinstansi` IN (SELECT idinstansi FROM users WHERE iduser='".$dataKelompok['iduser']."')";
				$resIns = $this->db->query($sqlIns);
				$dataIns = $this->db->fetchAssoc($resIns);

				$this->cellrowid++;
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = "";
  			$this->exceldata[$this->cellrowid][] = $dataIns['nama_instansi'];
  			$this->exceldata[$this->cellrowid][] = "";
				$this->loadedIns[$dataKelompok['iduser']] = true;
			}
		}
		*/

		$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']] = ($this->table_kelompok_numb[$dataKelompok['idparent']] ?: 0)+1; /* [anovedit] penomoran */
		$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = ($dataKelompok['idparent'] == 0) ? 0 : ($this->table_kelompok_levl[$dataKelompok['idparent']]+1); /* [anovedit] tingkatan */
		$levl = str_repeat(' ', $dataKelompok['levl']*5);

		if ($n_child > 0) {
			// parent kelompok
			// idkelompok ini masih memiliki child didalamannya
			if ($dataKelompok['idkelompok'] > 0) {
				$this->cellrowid++;
				$this->cellnumberid++;
				$this->exceldata[$this->cellrowid][] = $dataKelompok['idkelompok'];
				$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($dataKelompok['idkelompok']);
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = $this->cellnumberid;
				$this->exceldata[$this->cellrowid][] = sprintf('%s %s. %s', $levl, $dataKelompok['numb'], $dataKelompok['urai']);
				$this->exceldata[$this->cellrowid][] = $dataKelompok['satuan'];
			}

  		// recrisuve child
  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
  			$this->_createExcelDataObject($dataDetail['idkelompok'],$tab+1);
  		}

		} else if (!empty($iddata)) {
			if ($this->typeExport == 'kabupaten' && $this->exportIdKabupaten != -1) {
				// load data per kabupaten
				// foreach ($this->listKabupaten as $kodepemda => $namakab) {
				$kodepemda = $this->exportKabupaten['kodepemda'];
				$namakab = $this->exportKabupaten['kabupaten'];

  			$this->cellrowid++;
  			$this->cellnumberid++;
  			$this->exceldata[$this->cellrowid][] = $dataKelompok['idkelompok'];
				$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($dataKelompok['idkelompok']);
  			$this->exceldata[$this->cellrowid][] = $kodepemda;
				$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($kodepemda);
				$this->exceldata[$this->cellrowid][] = $this->cellnumberid;
  			$this->exceldata[$this->cellrowid][] = sprintf('%s %s. %s', $levl, $dataKelompok['numb'], $dataKelompok['urai']);
  			$this->exceldata[$this->cellrowid][] = $dataKelompok['satuan'];
  			$sqlDetail = "SELECT
  				tahun,
  				nilai FROM kelompok_detail_kabupaten
  				WHERE idkelompok={$this->db->ci3db->escape($dataKelompok['idkelompok'])}
  				AND idkabupaten={$this->db->ci3db->escape($kodepemda)}";
				$resDetail = $this->db->query($sqlDetail);
  			$data_arr = array();
  			while($rDetail = $this->db->fetchAssoc($resDetail)) {
  				$data_arr[$rDetail['tahun']] = $rDetail['nilai'];
  			}

  			// fixing index, supaya urutan data bersarkan tahunnya, bukan isi dari tabelnya
				foreach ($this->tahunExport as $key => $value) {
					$this->exceldata[$this->cellrowid][] = $data_arr[$value];
				}
			} else if ($this->typeExport == 'instansi' || $this->exportIdKabupaten == -1) {
				// load data per kabupaten
  			// kelompok tanpa child
  			$this->cellrowid++;
  			$this->cellnumberid++;
				$this->exceldata[$this->cellrowid][] = $dataKelompok['idkelompok'];
				$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($dataKelompok['idkelompok']);
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = "";
				$this->exceldata[$this->cellrowid][] = $this->cellnumberid;
  			$this->exceldata[$this->cellrowid][] = sprintf('%s %s. %s', $levl, $dataKelompok['numb'], $dataKelompok['urai']);
  			$this->exceldata[$this->cellrowid][] = $dataKelompok['satuan'];
  			foreach ($this->tahunExport as $key => $value) {
					$this->exceldata[$this->cellrowid][] = "";
				}

				/*
					[anovedit][note:] untuk loop distrik ...
					tambah tab1, karena merupakan nested terakhir.
					nomor biasa saja. karena tidak pakai recursive.
				*/
				$levl = str_repeat(' ', ($dataKelompok['levl']+1)*5);
				$numb = 0;
				foreach ($this->listKabupaten as $kodepemda => $namakab) {
					$numb++;
					$this->cellrowid++;
					$this->cellnumberid++;
					$this->exceldata[$this->cellrowid][] = $dataKelompok['idkelompok'];
					$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($dataKelompok['idkelompok']);
					$this->exceldata[$this->cellrowid][] = $kodepemda;
					$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($kodepemda);
					$this->exceldata[$this->cellrowid][] = $this->cellnumberid;
					$this->exceldata[$this->cellrowid][] = sprintf('%s %s. %s', $levl, $numb, $namakab);
					$this->exceldata[$this->cellrowid][] = $dataKelompok['satuan'];

	  			$sqlDetail = "SELECT
	  				tahun,
	  				nilai FROM kelompok_detail_kabupaten
	  				WHERE idkelompok={$this->db->escape_string($iddata)}
	  				AND idkabupaten='{$this->db->escape_string($kodepemda)}'";
	  			$resDetail = $this->db->query($sqlDetail);
	  			$data_arr = array();
	  			while($rDetail = $this->db->fetchAssoc($resDetail)) {
	  				$data_arr[$rDetail['tahun']] = $rDetail['nilai'];
	  			}
	  			// fixing index, supaya urutan data bersarkan tahunnya, bukan isi dari tabelnya
					foreach ($this->tahunExport as $key => $value) {
						$this->exceldata[$this->cellrowid][] = $data_arr[$value];
					}
				}
			}
		} // iddata
		// get / return var = $this->cellrowid,$this->exceldata
	}

	function encodeId_excel($id) {
		return substr(md5($id),10,10);
	}

	function makeExcelFileCode($a,$b=2,$c=1,$d=0){
		return substr(md5($a.$b.$c.$d),10,20);
	}

	function import() {
		/*
		* excell import management 
		*/
		$this->title = 'import file';
		$this->checkAkses();

		if (isset($_POST['id'])) {
			if($_FILES['filedata']['name'] <> '') {

				$new_file_name = md5($_FILES['filedata']['name']).'.xlsx';
				$uploaddir = ROOT_PATH.'files/kelompok/';		
				if (!file_exists($uploaddir) || !is_dir($uploaddir)) mkdir($uploaddir); // [anovedit][workaround] buat dir kalo belum ada.

				// uploading files
				$upload = new UploadClass();
				$upload->SetFileName($new_file_name);
				$upload->SetTempName($_FILES['filedata']['tmp_name']);
				$upload->SetUploadDirectory($uploaddir); //Upload directory, this should be writable
				$upload->SetValidExtensions(array('xlsx')); 
				$upload->SetMaximumFileSize(30000000); //Maximum file size in bytes
				$upload->ReplaceFile(true);
				if ($upload->UploadFile()) {
					$_SESSION['temp_xlsfile'] = $uploaddir.$new_file_name;
					$content .= "<li>File berhasil di upload</li>";
					// lanjutkan proses import : validasi file

					$objReader = PHPExcel_IOFactory::createReader('Excel2007');
					$objReader->setReadDataOnly(true);

					$objPHPExcel = $objReader->load($_SESSION['temp_xlsfile']);
					$objWorksheet = $objPHPExcel->getActiveSheet();

					$startedRow = 4;
					$highestRow = $objWorksheet->getHighestRow(); 
					$highestColumn = $objWorksheet->getHighestColumn(); 

					$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); 

					// index tahun
					for ($col = 7; $col < $highestColumnIndex; $col++) {
						$tahun[$col] = (int) $objWorksheet->getCellByColumnAndRow($col, 3)->getValue(); // [anovedit][note:] tidak boleh float
						$tahun_header .= "<th>{$tahun[$col]}</th>";
					}

					// cek file code dan submit variablenya
					$check_code = $objWorksheet->getCell('A1')->getValue();
					$submit_code = $this->makeExcelFileCode($_POST['type'],$_POST['id'], (int) $_POST['tahun_awal'], (int) $_POST['tahun_akhir']);

					// import mode : instansi , kabupaten & idimportmode
					$check_importcode 	= $objWorksheet->getCell('A2')->getValue();
					$check_importid 	= $objWorksheet->getCell('C1')->getValue();
					$check_importid_ec 	= $objWorksheet->getCell('D1')->getValue();

					if ($check_code == $submit_code) {
						$content .= "<li>File excell terbaca.. </li>";

						// listing data
						$cellData = array();
						for ($row = $startedRow; $row <= $highestRow; $row++) {
							$getId 		= $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
							$getCode 	= $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
							$getIdKab 	= $objWorksheet->getCellByColumnAndRow(2, $row)->getValue();
							$getCodeKab = $objWorksheet->getCellByColumnAndRow(3, $row)->getValue();
  							if (!empty($getId) && $getCode == $this->encodeId_excel($getId)) { 
  								if(!empty($getIdKab) && $getCodeKab == $this->encodeId_excel($getIdKab)) {
	  								$cellData[$getId][$getIdKab]['no'] = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();
	  								$cellData[$getId][$getIdKab]['uraian'] = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
	  								$cellData[$getId][$getIdKab]['satuan'] = $objWorksheet->getCellByColumnAndRow(6, $row)->getValue();
	  								for ($col = 7; $col < $highestColumnIndex; $col++) {
									    $cellData[$getId][$getIdKab][$tahun[$col]] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
									}
								} else {
									$cellData[$getId][0]['no'] = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();
	  								$cellData[$getId][0]['uraian'] = $objWorksheet->getCellByColumnAndRow(5, $row)->getValue();
	  								$cellData[$getId][0]['satuan'] = $objWorksheet->getCellByColumnAndRow(6, $row)->getValue();
	  								for ($col = 9; $col < $highestColumnIndex; $col++) {
									    $cellData[$getId][0][$tahun[$col]] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
									}
								}
  							}
						}

						$table = "
									<table id='table_kelompok_input' class='detail_data table table-striped table-condensed table-bordered'>
									<tr>
										<th rowspan=2>No</th>
										<th rowspan=2>Kabupaten/Kelompok/Sub Kelompok</th>
										<th rowspan=2>Satuan</th>
										<th colspan=".count($tahun).">Tahun</th>
									</tr>
									<tr>{$tahun_header}</tr>";

						// listing and Processing data
						$datausr = $this->auth->getDetail();
						$updated = $inserted = $nochange = $noakses = 0;
						foreach ($cellData as $idkelp => $cellKab) {

							// [anovedit][note:] user:admin, true
							if ($this->hasAksesOnKelompok($idkelp)) {
								// cek ini kelompok terdetail apa bukan
								$sql_cek = "SELECT idkelompok
									FROM kelompok_kabupaten
									WHERE idparent={$this->db->ci3db->escape($idkelp)}";
								$res_cek 	= $this->db->query($sql_cek);
								$num_child 	= $this->db->numRows($res_cek);

								foreach ($cellKab as $idkab => $cellContent) {
									$table .= "<tr>";
									$table .= "<td>{$cellContent['no']}</td>";
									$table .= "<td>{$cellContent['uraian']}</td>";
									$table .= "<td>{$cellContent['satuan']}</td>";

									// [anovedit]
									if (empty($idkab)) continue;

									foreach ($tahun as $tahun_detail) {
										$nominal = (float) $cellContent[$tahun_detail];
										// update | inseritng data
										$bgc = '';

										if ($num_child == 0) {
											/* [anovedit][note:] ambil data sekarang sebelumnya berubah */
											$current = $this->db->ci3db
											->from('kelompok_detail_kabupaten')
											->where([
												'tahun' => $this->db->ci3db->escape($tahun_detail),
												'idkelompok' => $this->db->ci3db->escape($idkelp),
												'idkabupaten' => $this->db->ci3db->escape($idkab),
											],null,false)
											->get()
											->row_array();
											/*
												[anovedit][note:] kalau data sebelumnya tidak ada, dan nominal nol,
												SKIP. karena kalau disimpan, hanya berupa data kosong.
											*/
											if ($current === null && empty($nominal)) { /* nothing */ } else {
												$this->db->ci3db
												->from('kelompok_detail_kabupaten')
												->set([
													'idkelompok_detail_kabupaten' => $this->db->ci3db->escape($current ? $current['idkelompok_detail_kabupaten'] : null),
													'tahun' => $this->db->ci3db->escape($tahun_detail),
													'idkelompok' => $this->db->ci3db->escape($idkelp),
													'idkabupaten' => $this->db->ci3db->escape($idkab),
													'postdate' => 'now()',
													'nilai' => ((float) $nominal),
													'iduser' => $this->db->ci3db->escape($datausr['iduser']),
												],null,false)->replace();
											} // current

										}
										$table .= "<td class='{$bgc}'>{$nominal}</td>";
									} // (tahun as tahun_detail)									
									$table .= "</tr>";
								} // cellKab@idkab>cellContent

							} else { // tidak ada akses ke kelompok
								// $content .= "<li>akses ditolak, silakan kembali kehalaman sebelumnya</li>";
								$noakses++;
							}
						}
						$table .= "</table>";

						if ($noakses > 0) {
							$content .= "<li><span class='bg-warning'>akses ditolak, file mungkin bermasalah, silakan kembali kehalaman sebelumnya</span></li>";
						} else {
							$content .= "<li>import data berhasil!, silakan pilih menu disamping untuk melanjutkan</li>";
						}
						$content .= $table;

						// remove files
						unlink($_SESSION['temp_xlsfile']);

					} else {
						$content .= "<li>file ditolak, file dihapus, silakan kembali kehalaman sebelumnya</li>";
					}

				} else {
					$content = "file gagal di upload, file tidak dapat di upload ke server";
				}

			} else {
				$content = "file gagal di upload, file tidak ditemukan";
			}

			$this->content = "<div class='import_process'>{$content}</div>"; 

		} else {
			$idimport 			= $_GET['id'];
			$this->typeImport 	= $_GET['type'];
			$this->tahunImport = [(int) $_GET['tahun_awal'], (int) $_GET['tahun_akhir']];

			# get data	
			// format nama file, sesuaikan, skpd x, bidang n. etc
			if ($this->typeImport == 'kelompok') {
				// $sqlKlp	= "SELECT * FROM kelompok 
				// 			WHERE idkelompok=".$this->db->escape_string($idimport);
				// $resKelp 		= $this->db->query($sqlKlp);
				// $dataKelompok 	= $this->db->fetchAssoc($resKelp);
				// $excel_filename = $dataKelompok['idkelompok'].".".$dataKelompok['urai']."_".min($this->tahunImport)."_".max($this->tahunImport).".xlsx";
			} else if ($this->typeImport == 'instansi') {
				$sqlIns	= "SELECT *
					FROM instansi
					WHERE idinstansi={$this->db->escape_string($idimport)}";
				$resIns = $this->db->query($sqlIns);
				$dataInstansi = $this->db->fetchAssoc($resIns);
				$excel_filename = $dataInstansi['idinstansi'].".".$dataInstansi['nama_instansi']."_".min($this->tahunImport)."_".max($this->tahunImport)."_distrik.xlsx";

			} else if ($this->typeImport == 'kabupaten') {
				if ($idimport > 0) {
					$sqlKab	= "SELECT *
						FROM kabupaten_gis
						WHERE kodepemda={$this->db->escape_string($idimport)}";
					$resKab = $this->db->query($sqlKab);
					$dataKab = $this->db->fetchAssoc($resKab);
					$excel_filename = $dataKab['kodepemda'].".".$dataKab['kabupaten']."_".min($this->tahunImport)."_".max($this->tahunImport).".xlsx";

				} else if ($idimport == -1) {
					$excel_filename = $this->activeInstansi['idinstansi'].".".$this->activeInstansi['nama_instansi']."_Kabupaten_".min($this->tahunImport)."_".max($this->tahunImport).".xlsx";
				}
			}

			$excel_filename = (!empty($excel_filename)) ? ", Nama file : <b>{$excel_filename}</b>" : $excel_filename;

			#build form
			$define = array (
							'type' => $this->typeImport,
							'id' => $idimport,
							'tahun_awal' => min($this->tahunImport),
							'tahun_akhir' => max($this->tahunImport),
							'nama_file' => $excel_filename,
							'rootdir' 	=> ROOT_URL,
							'action' 	=> 'import'
							 );		

			$tplform = new TemplateClass;
			$tplform->init(THEME.'/forms/kabupaten_file.html');
			$tplform->defineTag($define);	
			$form = $tplform->parse();
			$this->content = $form;
		}
	}

	private function _updateDetail($id) {
		// validate User Access
		// update data / insert jika tidak ada
		// update tanggal
		// update entries

		//
		$idkabupaten = $_POST['kodekabupaten'];
		$idkelompok = $_POST['idkelompok'];
		$tahun_detail = $_POST['tahun'];
		$nilai = (int) $_POST['nilai'];

		if ($this->hasAksesOnKelompok($idkelompok)) { //validasi akses
			$datausr = $this->auth->getDetail();

			// cek existensi data
			$q_cekdata = "SELECT *
				FROM kelompok_detail_kabupaten
				WHERE idkelompok='{$this->db->escape_string($idkelompok)}'
				AND idkabupaten='{$this->db->escape_string($idkabupaten)}'
				AND tahun='{$this->db->escape_string($tahun_detail)}'";
			$qCek = $this->db->query($q_cekdata);
			$cekCount = $this->db->numRows($qCek);

			// data ada, update, jika tidak, insert
			if ($cekCount > 0) {
				if ($nilai == 0) {
					$qUpdateInsertDetail = "DELETE from kelompok_detail_kabupaten
						WHERE idkelompok = {$this->db->ci3db->escape($idkelompok)}
						AND idkabupaten = {$this->db->ci3db->escape($idkabupaten)}
						AND tahun = {$this->db->ci3db->escape($tahun_detail)}";
				} else {
					$qUpdateInsertDetail = "UPDATE kelompok_detail_kabupaten SET
						nilai={$nilai},
						postdate=now(),
						iduser='{$this->db->escape_string($datausr['iduser'])}'
						WHERE idkelompok='{$this->db->escape_string($idkelompok)}'
						AND idkabupaten='{$this->db->escape_string($idkabupaten)}'
						AND tahun='{$this->db->escape_string($tahun_detail)}'";
				}
			} else {
				$qUpdateInsertDetail = "INSERT INTO kelompok_detail_kabupaten SET
					nilai={$nilai},
					idkelompok='{$this->db->escape_string($idkelompok)}',
					iduser='{$this->db->escape_string($datausr['iduser'])}',
					idkabupaten='{$this->db->escape_string($idkabupaten)}',
					tahun='{$this->db->escape_string($tahun_detail)}',
					postdate=now()";
			}

			if ($this->db->query($qUpdateInsertDetail)) {
				$message = "update data detail berhasil";
			} else {
				$message = "update data detail gagal" . ERROR_TAG;
			}
		} else {
			$message = "tidak ada akses, silakan login kembali" . ERROR_TAG;
		}
		return json_encode(array('message' => $message)); 
	}

}
