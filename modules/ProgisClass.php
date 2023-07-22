<?php
/*
	[20180927][anovedit]

	$this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();
	$this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,$_FILES,get_defined_vars());die();
*/

Class ProgisClass extends ModulClass
{
	/* [anovedit] handle penomoran, dan tingkatan */
	private $table_kelompok_levl = []; // loop level
	private $table_kelompok_numb = []; // loop number

	// [anovedit] saya pindah sini, supaya tidak mengganggu getter.
	private $elemennumber = 1;

	function Manage()
	{
		# grid & manajemen data
		$this->title = '<i class="fa fa-th-large"></i> Matrik Data Indikator';		
		$this->pgScript = '<script src="{themepath}js/progis.js"></script>';

		# fixAkses
		$this->checkAkses();

		$use_seleksi_instansi = false;
		$use_seleksi_kelompok = false;
		$tahun_min = $tahun_max = $selected_instansi = 0 ;

		$TemplatWaras1 = TemplatWaras1::init();
		$TemplatWaras1->set_root($this);

 		// query kelompok
		if ($this->userAkses == 'admin') {
			$this->title .= ' (admin)';
			$use_seleksi_instansi = true;

			// cek request tahun
			if (isset($_GET['instansi'])) {
				$selected_instansi = $_GET['instansi'];
				if (isset($_GET['tahun'])) {
					$tahun_array = array($_GET['tahun']);
					$tahun_min = $tahun_max = $_GET['tahun'];
					$tableData = $this->_loadTableData($_GET['instansi'],'instansi',$tahun_array);
				} else {
					$tableData = $this->_loadTableData($_GET['instansi'],'instansi');
				}
			} else {
				// load data kosong
				$tableData = array('header' => '', $body => '', 'opsidata' => '');
			}

		} else if ($this->userAkses == 'instansi') {
			$dataInstansi = $this->activeInstansi;
			$this->title .= " ({$dataInstansi['nama_instansi']}) <input type='hidden' id='select_skpdinstansi' value='{$dataInstansi['idinstansi']}'>";

			// $Qparent = $this->db->query("SELECT idkelompok FROM kelompok_matrix k
			// 	LEFT JOIN users u ON k.iduser = u.iduser 
			// 	WHERE idparent = 0 AND u.idinstansi='".$dataInstansi['idinstansi']."'");
			// $recParent = $this->db->fetchAssoc($Qparent);
			// load related to user data

			// cek request tahun
			if (isset($_GET['tahun'])) {
				$tahun_array = array((int) $_GET['tahun']);
				$tahun_min = $tahun_max = (int) $_GET['tahun'];
				$tableData = $this->_loadTableData($dataInstansi['idinstansi'],'instansi',$tahun_array);
			} else {
				$tableData = $this->_loadTableData($dataInstansi['idinstansi'],'instansi');
			}
		} 

		$TemplatWaras1->data([
			'use_seleksi_instansi' => $use_seleksi_instansi,
			'selected_instansi' => $selected_instansi,
		]);

		$seleksi_instansi = null;
		if ($use_seleksi_instansi) {
			// filter/select by SKPD
			// instansi, idinstansi, nama_instansi, singkatan
			// instansi_vertical : idinstansi, instansi, singkatan_instansi 
			$seleksi_instansi = $this->db->ci3db->query("SELECT a.*,
	 			(
	 				select (CASE WHEN count(b.idkelompok) > 0 THEN 1 ELSE 0 END)
	 				from kelompok_matrix b
	 				where b.pk_instansi = a.idinstansi and b.idparent = 0
	 				limit 1
 				) as matrix
	 			from instansi a
	 			order by
	 			matrix desc,
	 			a.kode_urusan,a.kode_suburusan,a.kode_organisasi asc
			");
		}

		//opsi pilihan tahun
		$tahun_data = $this->db->ci3db->query("SELECT min(tahun) as `min`, max(tahun) as `max` FROM `kelompok_detail_matrix`")->row_array();
		$tahun_data = array_map('intval', $tahun_data);

		$tahun_list = (int) date('Y');
		$tahun_list = ['max' => $tahun_list, 'min' => 2000];

		// [anovedit][note:] supaya data pada tahun sebelumnya juga muncul, kalau ada
		if ($tahun_data['min'] > 0 && $tahun_data['min'] < $tahun_list['min']) $tahun_list['min'] = $tahun_data['min'];

		if ($tahun_min > 0 && $tahun_max > 0) {
			$tahun_data['min'] = $tahun_min;
			$tahun_data['max'] = $tahun_max;
		}

		// $this;dump(__LINE__.':'.__FILE__,$_GET,$_POST,get_defined_vars());die();

		$TemplatWaras1->data([
			'tableData' => $tableData,
			'tahun_data' => $tahun_data,
			'tahun_list' => $tahun_list,
			'seleksi_instansi' => $seleksi_instansi,
		]);
		$TemplatWaras1->load(ROOT_PATH.'/themes/'.THEME.'/_/progis.tpl');
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
				return $this->_frontlist_chart($id);
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
			case 'export':
				// export data kelompok ke file excell
				$type = $_GET['type'];
				$id = $_GET['id'];
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

	function FrontList(){
		# 
		// index tahun , opsional, bisa dipilih

		// daftar opsi urusan/grand parent
		$urusan .= '
		<!-- /.accordion -->
		<div class="box-group col-sm-5" id="accordion" role="tablist" aria-multiselectable="true" style="margin-bottom:10px;">';

		// id, idparent, urai, formula, satuan
		$is_collapse = 1;
		$sql = $this->db->query("SELECT * FROM kelompok WHERE idparent=4"); //analisa
  		while ($dataUrusan = $this->db->fetchAssoc($sql)) {
  			$collapse = ($is_collapse) ? '' : 'collapse' ;
  			$urusan .= '
  			<div class="panel box box-warning" style=" margin:0px;">
		    	<div class="box-header with-border" role="tab" id="heading'.$dataUrusan['idkelompok'].'">
			    	<h3 class="box-title" style="font-size:14px;">
			    	<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$dataUrusan['idkelompok'].'" aria-expanded="false" class="collapsed">
                        <i class="fa fa-circle-o text-aqua"></i><span>&nbsp;'.$dataUrusan['urai'].'</span>
                      </a>
			        </h3>
		    	</div>
		     	<div id="collapse'.$dataUrusan['idkelompok'].'" class="panel-collapse '.$collapse.'" aria-expanded="false" style="height: 0px;">
		      	<div class="box-body"><ul>
	      ';

  			$is_collapse = 0;
  			
  			$sqlsub = $this->db->query("SELECT * FROM kelompok WHERE idparent=".$dataUrusan['idkelompok']); //analisa
  			while ($dataSUrusan = $this->db->fetchAssoc($sqlsub)) {
  				$urusan .= "<li class='info-box-text'><a href='javascript:loadSubElement({$dataSUrusan['idkelompok']})'><i class='fa fa-angle-right'></i>&nbsp;{$dataSUrusan['urai']}</a></li>";
  			}
  			
  			$urusan .= "</ul></div>
	    		</div>
	    	</div>";

  		}

  		$urusan .= '
  		</div>

  		<div class="panel-group col-sm-7">
  		<div class="box box-danger"  id="sub_element">
	            <div class="box-header with-border">
	              <h3 class="box-title">Sub Kelompok</h3>
	            </div>
	            <!-- /.box-header -->   
	            <div class="box-body" style="height:450px;overflow-y:auto;overflow-x:hidden;">
					<div id="sub_element_content"></div>
	            </div>
	            <!-- /.box-body -->         
	    </div>
  		</div>';

		// batasi pilihan tahun sesui keberadaan data
		$qData = $this->db->query("SELECT MIN(tahun) as min, MAX(tahun) as max FROM kelompok_detail_matrix");
		$tahunData = $this->db->fetchAssoc($qData);

		$opsi = $opsi_analisa = "";
		for ($t = $tahunData['min']; $t <= $tahunData['max']; $t++) {
			$opsi .= '<label id="btn_'.$t.'" type="button" class="btn btn-default btn-year"><input type="checkbox" class="tahun_chart" id="tahun_chart[]" name="tahun_chart[]" checked="false" value="'.$t.'"> &nbsp;'.$t.'</label>'; //$t;
			$opsi_analisa .= '<label id="btn_'.$t.'" type="button" class="btn btn-default btn-year"><input type="checkbox" class="tahun_analisa" id="tahun_analisa[]" name="tahun_analisa[]" checked="false" value="'.$t.'"> &nbsp;'.$t.'</label>'; //$t;
			$tahun_chart[] = $t;
		}

		// Analisa Perbandingan Tabel
		// id, judul, id_kelompok1, id_kelompok2, id_kelompok3
		$jenis_analisa = "<select id='jenis_analisa' class='form-control' onChange='loadAnalisaChart(this.value);'>";
		$qAnalisa = $this->db->query("SELECT * FROM analisa_perbandingan");
		while ($rAnalisa = $this->db->fetchAssoc($qAnalisa)) {
			$jenis_analisa .= "<option value=\"{$rAnalisa['idkelompok']}\">{$rAnalisa['judul']}</option>";
		}
		$jenis_analisa .= "</select>";

		$sql_test = "SELECT k.idkelompok FROM kelompok k 
			JOIN konversi_kelompok kk ON kk.idkelompok_sipd = k.idkelompok
			JOIN kelompok_matrix km ON km.idkelompok = kk.idkelompok_matrix
			ORDER BY RAND() LIMIT 1;";
		$res_test = $this->db->query($sql_test);
		$data_test = $this->db->fetchAssoc($res_test);

		$test_id = $data_test['idkelompok'];
		
	$this->pgScript = '<script src="{themepath}plugins/Highcharts-4.2.3/js/highcharts.js"></script>
	<script src="{themepath}plugins/Highcharts-4.2.3/js/modules/exporting.js"></script>
	<script src="{themepath}js/progis.js"></script>
	<script>
	$(document).ready(function(){
		loadChart('.$test_id.');
		  $(\'input\').iCheck({
		    checkboxClass: \'icheckbox_square\',
		    radioClass: \'iradio_square\',
		    increaseArea: \'-10%\' // optional
		  });

	});
	</script>';
	$table = '<style>
.ui-menu-item{
  width: 0%;
  font-size: 12px;
}
.ui-autocomplete { max-height: 200px; overflow-y: scroll; overflow-x: hidden;}
</style>
		'.$urusan.'

			<!--{opsi tahun x-n (tahun data pertama diisi dan data terakhir terisi), lokasi}-->
			<!-- /.chart-display -->
			<div class="col-sm-12 col-md-12">
	          <div class="box box-info" id="chart_option">
	            <div class="box-header ui-sortable-handle" style="cursor: move;">
	              <i class="fa fa-fw fa-line-chart"></i>
	              <h3 class="box-title">Provinsi</h3>
	              <!-- tools box -->
	              <div class="pull-right box-tools">
	                <button id="" type="button" class="btn-refresh-chart btn btn-info btn-md" data-toggle="tooltip" title="" data-original-title="Refresh">
	                  <i class="fa fa-refresh"></i></button>
	              </div>
	              <!-- /. tools -->
	            </div>
	            <!-- /.box-header -->   
	            <div class="box-body">
					<div class="row">
					<div class="col col-lg-6  btn-group">'.$opsi.' </div>
					<div class="col col-lg-6"><input placeholder="ketik elemen data" class="form-control" name="elemen" id="elemen"></div>
					</div> 
	            </div> 

	            <!-- /.box-body -->         
	            <div class="box-body">
					<div id="chart1" class="bg-gray" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
	            </div>
	            <!-- /.box-body -->         
	          </div>
	        </div>

	        <!-- /.kabupaten data & chart-display -->
			<div class="col-sm-12 col-md-12">
	          <div class="box box-info" id="chart_option">
	            <div class="box-header ui-sortable-handle" style="cursor: move;">
	              <i class="fa fa-fw fa-line-chart"></i>
	              <h3 class="box-title">Kabupaten</h3>
	              <!-- /. tools -->
	            </div>
	            <!-- /.box-header -->   
	            <!-- /.box-body -->         
	            <div class="box-body">
					<div id="chart_kabupaten" class="bg-gray" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
	            </div>
	            <!-- /.box-body -->     
	            <span class="chartnav chartnav-l"><i class="fa fa-arrow-left"></i>
	            </span>
				<span class="chartnav chartnav-r"><i class="fa fa-arrow-right"></i>
				</span>
	          </div>
	        </div>

			';
		
		$this->menu = new MenuClass;
		$this->link = new LinkClass;
		// $this->berita = new BeritaClass;
		$this->slider = new SliderClass;
		$this->user = new UserClass;
		// $this->agenda = new AgendaClass;
		// $this->FrontDisplay();
		// $this->agenda->FrontDisplay();
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'menufooter'	=> $this->menu->FrontDisplay('B'),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> "8 KELOMPOK DATA",
						 'pagecontent'	=> $table,
						 'pagescript'	=> $this->pgScript,
						 // 'sidenews'		=> $this->sidenews,		
						 // 'link'			=> $this->link->FrontDisplay(),	
						 // 'latestnews'	=> $this->berita->LatestNews(),

						 'account_menu'	=> $this->user->AccountMenu(),

						 'home'			=> ROOT_URL,
						 'error_tag'	=> ERROR_TAG,
						 'tweetacc' 	=> TWEET_ACC,
						 'fbacc' 		=> FB_ACC,
						 'googleacc' 	=> GOOGLE_ACC,
						 'contactaddr' 	=> CONTACT_ADDR,
						 'contacttelp' 	=> CONTACT_TELP,
						 'contactweb' 	=> CONTACT_WEB,
						 'contactfb' 	=> FB_ACC,
						 'contactfax' 	=> CONTACT_FAX,
						 'contactemail' => CONTACT_EMAIL,
						 'hotline' 		=> HOTLINE,					 						 
				 		 'themepath'  	=> THEME_URL,
                );
		$this->template->init(THEME.'/detail.html');
		$this->template->defineTag($define);
		$this->template->printTpl(); 
	}
	function checkAkses(){
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
					// die($sqlInstansi);
			$qInstansi = $this->db->query($sqlInstansi);
			$this->activeInstansi = $this->db->fetchAssoc($qInstansi);
		} else if ($datausr['idgroup'] == 3) {
			$this->userAkses = 'bidang';
		}
	}
	function hasAksesOnKelompok($idkelompok){
		/** cek akses user ke kelompok 
		 * - load kelompok berdasarkan idkelompok dan isuser pada instansi
		 */

		$datausr 	= $this->auth->getDetail();
		$hasAkses = false;
		if ($datausr['idgroup'] == 1) {
			//akses admin, all granted
			$hasAkses = true;
		} else if ($datausr['idgroup'] == 2) {
			// instansi, cek seluruh user dibawah instnasi
			// [anovedit][users_table_ignored]
			$qKelompok = "SELECT idkelompok FROM `kelompok_matrix` k
				WHERE k.idkelompok= {$this->db->ci3db->escape($idkelompok)}
				and k.pk_instansi = {$this->db->ci3db->escape($datausr['idinstansi'])}";
			$resKelompok = $this->db->query($qKelompok);
			if ($this->db->numRows($resKelompok) > 0) 
				$hasAkses = true;
		} else if ($datausr['idgroup'] == 3) {
			// bidang, cek hanya user
			// $qKelompok = "SELECT idkelompok FROM `kelompok` k 
			// 				JOIN `users` u ON k.iduser = u.iduser 
			// 				WHERE k.idkelompok='".$this->db->escape_string($idkelompok)."' 
			// 					AND u.iduser='".$this->db->escape_string($datausr['iduser'])."'";
			// $resKelompok = $this->db->query($qKelompok);
			// if ($this->db->numRows($resKelompok) >0)
			// 	 $hasAkses = true;
		}
		return $hasAkses;
	}

	private function _loadTableData($iddata,$type='kelompok',$tahunData=array()){
		//load dataTabale untuk ditampilkan
		$this->checkAkses();

		$this->typeData  = $type; 
		$this->tahunData = $tahunData; 

		if (empty($this->tahunData)) {
			//opsi pilihan tahun
			$qData = $this->db->query("SELECT MIN(tahun) as min, MAX(tahun) as max FROM kelompok_detail_matrix");
			$tahunData = $this->db->fetchAssoc($qData);
			$th_data = array();
			for ($xt = $tahunData['min'];  $xt <= $tahunData['max']; $xt++) {$th_data[] = $xt;}
			$this->tahunData = $th_data;
		}

		// get tahun dari submit atau table
 		$tahun_count = count($this->tahunData);

 		$tahun_header = $th_checkbox =  "";
 		foreach ($this->tahunData as $key => $value) {
 			$tahun_header .= "<th>".$value."</th>";
 			$th_checkbox .= "<input id='tahun_chart[]' name='tahun_chart[]' class='tahun_chart' type='checkbox' checked value='{$value}' hidden>";
 		}

 		// seleksi index data : 1. kelompok 2. skpd
 		if ($this->typeData == 'kelompok') {
 			// detail data kelompok : parent Data
			// $sqlKelompok = "SELECT a.idkelompok,a.urai,k.idkelompok as started_id 
			// 				FROM `kelompok` k
			// 				JOIN `kelompok` a ON a.idkelompok = k.idparent
		 	// 					WHERE k.idkelompok=".$this->db->escape_string($iddata);
		 	// 					;
 			$parent_urai = "";
 		} else if ($this->typeData == 'instansi') {
 			/* detail data skpd :
 			 * akses : admin -> skpd atau skpd bersangkutan
 			 */
 			$this->loadinstansi = $iddata ;
 			if ($this->userAkses == 'admin') {
				// die($type);
 				// query ini menghasilkan result row bisa lebih dari satu, maka sebaiknya di seleksi satu2
 				// bukan langsung mengelarkan 1 nilai
	 			// $sqlKelompok = "SELECT DISTINCT(ka.idkelompok) AS started_id,kb.urai FROM `kelompok` k 
	 			// 	JOIN `kelompok` ka ON ka.idkelompok = k.idparent
	 			// 	JOIN `kelompok` kb ON kb.idkelompok = ka.idparent
				// JOIN `users` u ON k.iduser = u.iduser 
				// WHERE k.idparent < 100 
				// 	AND u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->db->escape_string($iddata)."')";

 				/* [anovedit][users_table_ignored] */
				$sqlKelompok = "SELECT
					a.idkelompok AS started_id,
					a.urai
					FROM kelompok_matrix a
					WHERE a.pk_instansi = {$this->db->ci3db->escape($iddata)}
					AND a.idparent = 0";
				$sql_l_instansi = "SELECT * FROM instansi WHERE idinstansi='".$this->db->escape_string($iddata)."'";

 			} else if ($this->userAkses == 'instansi') {
 				// berikan aksed hanya kepada kelompok-nya milik skpd yg aktif
 				/* [anovedit][users_table_ignored] */
 				$sqlKelompok = "SELECT
 					a.idkelompok AS started_id,
 					a.urai
 					FROM kelompok_matrix a
 					WHERE a.pk_instansi = {$this->db->ci3db->escape($iddata)}
 					AND a.idparent = 0";
				$sql_l_instansi = "SELECT * FROM instansi WHERE idinstansi='".$this->db->escape_string($iddata)."'";
 			}

 			$res_l_instansi = $this->db->query($sql_l_instansi);
 			$data_l_instansi = $this->db->fetchAssoc($res_l_instansi);
 			$parent_urai = $data_l_instansi['nama_instansi'];
 		}

	 	$resKelompok = $this->db->query($sqlKelompok);

	 	// print_r($rowKelompok);
 		// die ($sqlKelompok);

	 	// export, import, print button
	 	$data_option = '<button class="btn btn-flat btn-info no-print" onclick="cetakTabelDetail();"><i class="fa fa-print"> &nbsp; cetak</i></button>
	 		<button class="btn btn-flat btn-warning no-print" onclick="excelImport(\''.$iddata.'\',\''.$type.'\','.min($this->tahunData).','.max($this->tahunData).');"><i class="fa fa-file-excel-o"> &nbsp; import</i></button>
	 		<button class="btn btn-flat btn-success no-print" onclick="excelExport(\''.$iddata.'\',\''.$type.'\','.min($this->tahunData).','.max($this->tahunData).');"><i class="fa fa-file-excel-o"> &nbsp; export</i></button>';

	 	// table header, sesuaikan dengan pilihan tahun
 		$thead = "
 			<tr>
				<th rowspan=2>No</th>
				<th rowspan=2>Kelompok/Sub Kelompok</th>
				<th colspan=".$tahun_count.">Tahun{$th_checkbox}</th>
				<th rowspan=2>Satuan</th>
				<th rowspan=2>Pengentri</th>
				<th rowspan=2>Tanggal Update</th>
				<th rowspan=2 class='no-print'>Aksi</th>
			</tr>
			<tr>
			  ".$tahun_header."
			</tr>
		";

		// table body
		$empty_td = str_repeat("<td></td>", count($this->tahunData));
		$tbody = "
			<tr class='kelompok_parent'>
				<td></td>
				<td><b>{$parent_urai}</b></td>
				{$empty_td}
				<td></td>
				<td></td>
				<td></td>
				<td class='no-print'></td>
			</tr>";

		$this->detailKelompok = "";
		$this->row_id = 1;

	 	if ($this->db->numRows($resKelompok) <=0 ) {
	 		$tbody .= "
	 			<tr class='no-data'>
					<td></td>
					<td><i class='text-red'>tidak ada data !</i></td>
					{$empty_td}
					<td></td>
					<td></td>
					<td></td>
					<td class='no-print'></td>
				</tr>";
	 	}

	 	while ($rowKelompok = $this->db->fetchAssoc($resKelompok)) {
 			$this->_lisdetailtelement($rowKelompok['started_id']);
	 	}

 		$tbody .= $this->detailKelompok; //join
		return array('header' => $thead, 'body' => $tbody, 'opsidata' => $data_option);
	}

	private function _loadData($iddata,$type='kelompok')
	{
		// ajax
		// range tahun
		$tahun_awal = $_GET['tahun_awal'];
		$tahun_akhir = $_GET['tahun_akhir'];
		$th_data = array();
		if (!empty($tahun_awal) && !empty($tahun_akhir)) {
			for ($xt = $tahun_awal;  $xt <= $tahun_akhir; $xt++) {$th_data[] = $xt;}
		}
		$tableData = $this->_loadTableData($iddata,$type,$th_data);
		return json_encode($tableData); 
	}

	private function _lisdetailtelement($iddata,$tab=0){
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
			$sqlKlp	= "SELECT * FROM kelompok WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child	= "SELECT * FROM kelompok_matrik WHERE idparent={$this->db->escape_string($iddata)}";
		} else if ($this->typeData == 'instansi') {
			$sqlKlp	= "SELECT *
				FROM kelompok_matrix 
				WHERE  idkelompok=".$this->db->escape_string($iddata);
			$sql_child	= "SELECT *
				FROM kelompok_matrix k 
				WHERE k.idparent = {$this->db->ci3db->escape($iddata)}
				AND k.pk_instansi = {$this->db->ci3db->escape($this->loadinstansi)}";
		}

		$QKelompok 		= $this->db->query($sqlKlp);
		$dataKelompok 	= $this->db->fetchAssoc($QKelompok);

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

		$res_child 	= $this->db->query($sql_child);
		$n_child 	= $this->db->numRows($res_child);

		if (!empty($iddata)) {
			$urai = $this->scr->utf8_encode($dataKelompok['urai']);
			$levl = str_repeat('&nbsp; &nbsp; ', $dataKelompok['levl']);

			if ($dataKelompok['formula'] != '') {
				// punya formula, eksekusi
				$td_rekap = "";
				foreach ($this->tahunData as $key => $value) {
					$nilai_rekap = "";
					if (!empty($dataKelompok['formula'])) {
						// $nilai_rekap = "formula";	
						$snilai = $this->_evalFormula($dataKelompok['formula'],$value);
						$nilai_rekap = $this->numen->autoSeparator($snilai);
					}

					$td_rekap .= "<td class='nilai_formula {$value} align-right'>{$nilai_rekap}</td>";
				}

				$this->detailKelompok .= "<tr id='trparent_{$dataKelompok['idkelompok']}' class='data-level-{$dataKelompok['levl']} data-pk_parent='{$dataKelompok['idparent']}' data-pk='{$dataKelompok['idkelompok']}' data-numb='{$dataKelompok['numb']}' data-levl='{$dataKelompok['levl']}' inactive'>
				<td>{$this->elemennumber}</td>
				<td nowrap>{$levl} {$dataKelompok['numb']}. <span>{$urai}</span></td>
				{$td_rekap}
				<td align=center>".$this->scr->utf8_encode($dataKelompok['satuan'])."</td>
				<td></td>
				<td></td>
				<td class='no-print'></td>
				</tr>";

			} else {
  			// depest child (kelompok ini adalah yg terdetail)
  			$action = '
  			<button type="button" class="btn-flat-info" onClick="openChart('.$iddata.',\'matrik\')"><i class="fa fa-bar-chart-o"></i></button>';
				$curent_klp = "";
				$this->detailKelompok .= "
				<tr id='rows_{$this->row_id}' data-row-id='{$this->row_id}' class='rows_data data-level-{$dataKelompok['levl']}' data-pk_parent='{$dataKelompok['idparent']}' data-pk='{$dataKelompok['idkelompok']}' data-numb='{$dataKelompok['numb']}' data-levl='{$dataKelompok['levl']}'>
				<td>{$this->elemennumber}</td>
				<td nowrap>{$levl} {$dataKelompok['numb']}. <span>{$urai}</span></td>";
	  			$sqlDetail = "SELECT km.postdate,km.tahun,km.nilai,u.username
  						FROM `kelompok_detail_matrix` km
  						LEFT JOIN `users` u ON (u.iduser = km.iduser)
  						WHERE km.idkelompok={$iddata}";
  			$qDetail = $this->db->query($sqlDetail);
  			$data_arr = $postdate = array();
  			while($rDetail = $this->db->fetchAssoc($qDetail)) {
  				$data_arr[$rDetail['tahun']] = $rDetail['nilai'];
  				$postdate[] = $rDetail['postdate'];
  				$entree[$rDetail['postdate']] = $rDetail['username'];
  			}

  			$maxpost = max($postdate); // update date
				$pengentri = $entree[$maxpost]; // pengentri terakhir

				foreach ($this->tahunData as $key => $value) {
					// input detail jika ada data terdetail
					$lendata = strlen($data_arr[$value]);
					$this->detailKelompok .= "<td class='td_input'><input data-idkelompok='".$iddata."' data-tahun='".$value."' id='".$iddata."_".$value."' class='detail_input ".$value."' onkeypress='return JustNumbers(event);' onChange='updateData(this.id)' value='".$data_arr[$value]."' size=".$lendata."></td>";
				}
				$this->detailKelompok .= "<td align=center>".$this->scr->utf8_encode($dataKelompok['satuan'])."</td>
				<td>{$pengentri}</td>
				<td nowrap>".$maxpost."</td>
				<td nowrap class='no-print'>".$action."</td>
				</tr>";
			}

			$this->elemennumber++;
			$this->row_id++;
		}

		if ($n_child > 0) {
			// parent kelompok
			// idkelompok ini masih memiliki child didalamannya
			$this->numbering[$iddata] = 0;

			$this->elemennumber++;
  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
				$this->numbering[$iddata] += 1;
  			$this->_lisdetailtelement($dataDetail['idkelompok'],$tab+1);
  			//$n_child++;
  		}
		} // n_child
	}

	private function _listsubelement($idkelompok,$tab=0){
		/**
		 * lisitng sub element (child & g child)
		 * {$dataSUrusan['idkelompok']}
		 * ".str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$tab)."
		 */
		$sqlsub = "SELECT * FROM kelompok WHERE idparent=".$this->db->escape_string($idkelompok);
		$res_sub = $this->db->query($sqlsub);
  		while ($dataSUrusan = $this->db->fetchAssoc($res_sub)) {
  			$paddleft = (20*($tab+1));
  			$urusan .= "<tr><td style='padding;1px; padding-left:{$paddleft}px;'><a href='javascript:loadChart({$dataSUrusan['idkelompok']})'><i class='fa fa-genderless text-navy'></i> {$dataSUrusan['urai']} ({$dataSUrusan['satuan']})</a></td></tr>";
  			$urusan .= $this->_listsubelement($dataSUrusan['idkelompok'],$tab+1);
  		}
  		return $urusan;
	}
	private function _listelement($idkelompok){
		/*
		 * lisitng element & sub (parent & 1 child)
		 * id, idparent, urai, formula, satuan
		 * {$dataUrusan['idkelompok']}
		 */
		$sql 		= "SELECT * FROM kelompok 
						WHERE 
							idparent=".$this->db->escape_string($idkelompok);
		$res_sql 	= $this->db->query($sql); //analisa
		$urusan .= "<table class='table table-condensed table-hover'>";
  		while ($dataUrusan = $this->db->fetchAssoc($res_sql)) {
  			$urusan .= "<tr><td onClick='loadChart({$dataUrusan['idkelompok']});'><b> {$dataUrusan['urai']}</b></td></tr>";
  			$urusan .= $this->_listsubelement($dataUrusan['idkelompok']);
  		}
  		$urusan .= "</table>";
  		return $urusan;
	}
	
	private function _evalFormula($formula,$tahun,&$akumulasiFormula = '', &$tab = ''){
		// ubah formula menjadi value : 
		// contoh : {idkelompok}*{idkelompok} => 100*12
		// print $akumulasiFormula."<br>";
		preg_match_all("/\{([0-9]+)\}/", $formula, $arrmatches);
		if($akumulasiFormula == '') $akumulasiFormula = $formula;
		foreach ($arrmatches[1] as $idkelompok) {

			// cek ada formulanya atau tidak
			$sqlKelompok = "SELECT formula 
				    			  FROM 
				    			  	kelompok_matrix 
				    			  WHERE 
				    			  	idkelompok='".$this->db->escape_string($idkelompok)."'";
			$rKelompok = $this->db->query($sqlKelompok);
			$dataKelompok = $this->db->fetchAssoc($rKelompok);
			$formulaK = $dataKelompok['formula'];
			
			// jika kelompok itu masih punya formula maka rekursif
			if($formulaK <> ''){
				$akumulasiFormula = str_replace('{'.$idkelompok.'}', $formulaK, $akumulasiFormula); 
				$nilai = $this->_evalFormula($formulaK,$tahun,$akumulasiFormula,$tab);
				// LogClass::log('replace : '.'{'.$idkelompok.'} menjadi '.$nilai);
			}else{ 	
			  // parsing idkelompoknya 
			   $sqlDetail = "SELECT nilai 
			    			  FROM 
			    			  	kelompok_detail_matrix 
			    			  WHERE 
			    			  	idkelompok='".$this->db->escape_string($idkelompok)."' AND 
			    			  	tahun = '".$this->db->escape_string($tahun)."'";
			   $rDetail = $this->db->query($sqlDetail);
			   $dataDetail = $this->db->fetchAssoc($rDetail);
			   $res_nilai = trim($dataDetail['nilai']);
			   $nilai = (empty($res_nilai)) ? 0 : $dataDetail['nilai'];	
			   $akumulasiFormula = str_replace('{'.$idkelompok.'}', $nilai, $akumulasiFormula); 	   
			}
			 		
		}

		// evaluate
		$sqlEval = "SELECT ($akumulasiFormula) as hasilperhitungan";
		$rEval = $this->db->query($sqlEval);
		$dataEval = $this->db->fetchAssoc($rEval);	
		$hasilperhitungan = $dataEval['hasilperhitungan'];

		if(!is_numeric($hasilperhitungan)) $hasilperhitungan = $akumulasiFormula;

		// LogClass::log($tab."rumuse : ".$formula);
		// LogClass::log($tab."parsing : ".$akumulasiFormula);
		// LogClass::log($tab."sql : ".$sqlEval);
		// LogClass::log($tab."nilai : ".$hasilperhitungan);
		// LogClass::log($tab."\n");

		$tab = $tab.'      ';
		
		return $hasilperhitungan;  			
		
	}

	private function _chartSource($id,$tahun_chart=array(),$single_series=false)
	{
		/**
		* 
		*
		* load chart parameter
		* id = id kelompok di tabel keompok
		* tahun_chart = array tahun chart, eg (2015,2016)
		*/

		$id_kab = 0;

		$page 	= $_GET['page'];
		if ($page == 'sipd') {
			// replace id
			$sipd_id = $id;
			$sql_c = "SELECT
				idkelompok_matrix AS idkelompok,
				idkelompok_kabupaten
				FROM konversi_kelompok
				WHERE idkelompok_sipd={$this->db->ci3db->escape($sipd_id)}";
			$res_c = $this->db->query($sql_c);
			$data_c = $this->db->fetchAssoc($res_c);
			$id = $data_c['idkelompok'];
			$id_kab = $data_c['idkelompok_kabupaten'];
		}

		if (empty($id)) {
			// id, idparent, urai, formula, satuan
			$sql 	= $this->db->query("SELECT *,i.nama_instansi as sumber 
				FROM kelompok k 
				LEFT JOIN instansi i ON (i.idinstansi = k.pk_instansi)
				WHERE k.idkelompok={$this->db->ci3db->escape($sipd_id)}");
			$rTabel = $this->db->fetchAssoc($sql);
			$this->detailTable = 'kelompok_detail';
			$this->indikatorTable = 'kelompok';
			$id = $sipd_id;
		} else {
			/* [anovedit][users_table_ignored] */
			// id, idparent, urai, formula, satuan
			$sql 	= $this->db->query("SELECT k.*,i.*,i.nama_instansi as sumber
				FROM kelompok_matrix k 
				JOIN instansi i ON (i.idinstansi = k.pk_instansi)
				WHERE k.idkelompok={$this->db->ci3db->escape($id)}
			");
			$rTabel = $this->db->fetchAssoc($sql);
			$this->detailTable = 'kelompok_detail_matrix';
			$this->indikatorTable = 'kelompok_matrix';
		}

		$judul 	= $rTabel['urai'];
		$satuan = $rTabel['satuan'];
		$sumber = (empty($rTabel['sumber'])) ? 'Pusdalisbang (*)' : $rTabel['sumber'];

		$chart_type = 'line';

		// tahun chart
		// tahun default jika kosong, tampilkan semua data
		if (empty($tahun_chart)) {
			$th_sql = $this->db->query("SELECT GROUP_CONCAT(DISTINCT(tahun) ORDER BY tahun ASC) as tahun FROM {$this->detailTable}");
			$th_Tabel = $this->db->fetchAssoc($th_sql);
			$tahun_chart = array_map('intval', explode(',', $th_Tabel['tahun']));
		}

		// type chart berdasarkan jumlah item yg dibandingkan
		if (count($tahun_chart) == 1) $chart_type = 'column'; //<-- colum untuk single item / atau single year

		foreach ($tahun_chart as $tahun) $ye[] = $tahun; // [anovedit] kategori chart

		$series = array(); 
		// data series dari linechart
		$sql_tabel = "SELECT *
			FROM {$this->indikatorTable}
			WHERE idparent={$this->db->ci3db->escape($id)}
			ORDER BY idkelompok ASC";
		$qTabel = $this->db->query($sql_tabel);

		if ($single_series) {
			/*
			* tampilkan hanya satu set series saja 
			*/
			//load detail pertahun-nya
			foreach($tahun_chart as $tahun) {
				$nilai = 0;
				$sumChild = $this->recrusiveData($rTabel['idkelompok'],$rTabel['idkelompok'],$tahun);
				if ($sumChild[$rTabel['idkelompok']][$tahun] > 0) {
					$nilai = $sumChild[$rTabel['idkelompok']][$tahun];	
					$satuan = (empty($satuan)) ? $sumChild[$rTabel['idkelompok']]['satuan'] : $satuan;
				}
				$data[] = number_format($nilai,2,'.','');//$nilai;//
			}

			// series data untuk chart
			$series[$rTabel['idkelompok']]['name'] = $rTabel['urai'];
			$series[$rTabel['idkelompok']]['is_publish'] = (boolean) $rTabel['publish']; // [anovedit][workaround]
			$series[$rTabel['idkelompok']]['data'] = $data;

		} else if ($this->db->numRows($qTabel) > 0) {
			/* 
			* elemen dengan id tesebut adalah parent dari kelompok
			* - load data  detail childrennya
			*/
			while ($rTabel = $this->db->fetchArray($qTabel)) {
				$data = array(); 
				$satuan = (empty($satuan)) ? $rTabel['satuan'] : $satuan; // satuan jika kosong

		    //load detail pertahun-nya
		    foreach($tahun_chart as $tahun) {
		    	$nilai = $res_nilai = 0;
		    	$the_formula = trim($rTabel['formula']);
		    	if (!empty($the_formula)) {
			    	$res_nilai = $this->_evalFormula($rTabel['formula'], $tahun);
			    	// $sumChild[$rTabel['idkelompok']][$tahun] = $val;
			    	// $sumChild = $this->recrusiveData($rTabel['idkelompok'],$rTabel['idkelompok'],$tahun);

		    	} else {
				    $qVal = $this->db->query("SELECT * FROM {$this->detailTable}
				    	WHERE tahun={$this->db->ci3db->escape($tahun)}
				    	AND idkelompok={$this->db->ci3db->escape($rTabel['idkelompok'])}");
				    if ($rVal = $this->db->fetchArray($qVal)) {
				    	$res_nilai = number_format($rVal['nilai'],2);
				    } 
		    	}

		    	if ($res_nilai > 0) $nilai = $res_nilai;
		    	$data[] = number_format($nilai,2,'.','');//$nilai;//
		    }

		    // series data untuk chart
		    $series[$rTabel['idkelompok']]['name'] = $rTabel['urai'];
		    $series[$rTabel['idkelompok']]['is_publish'] = (boolean) $rTabel['publish']; // [anovedit][workaround]
		    $series[$rTabel['idkelompok']]['data'] = $data;
			}

		} else {
			// jika kelompok adalah yg terdetil / child with no child
			$data = array();
			$chart_type = "column";
			// satuan jika kosong
			$satuan = (empty($satuan)) ? $rTabel['satuan'] : $satuan;

		    //load detail pertahun-nya
		    foreach($tahun_chart as $tahun) {
		    	$nilai = 0;
		    	$qVal = $this->db->query("SELECT *
		    		FROM {$this->detailTable}
		    		WHERE tahun={$this->db->ci3db->escape($tahun)}
		    		AND idkelompok={$this->db->ci3db->escape($rTabel['idkelompok'])}");
		    	if ($rVal = $this->db->fetchArray($qVal)) {
		    	  $nilai = number_format($rVal['nilai'],2,'.',''); //$rVal['nilai'];//
		    	}
		    	$data[] = $nilai;
		    }

		    // series data untuk chart
		    $series[$rTabel['idkelompok']]['name'] = $rTabel['urai'];
		    $series[$rTabel['idkelompok']]['is_publish'] = (boolean) $rTabel['publish']; // [anovedit][workaround]
		    $series[$rTabel['idkelompok']]['data'] = $data;
		}

		sort($series);

		if ($id_kab > 0) {
			// data chart untuk kabupaten
			$sql_yar = "SELECT MAX(tahun) as tahun FROM kelompok_detail_kabupaten WHERE idkelompok = {$this->db->ci3db->escape($id_kab)}";
			$res_yar = $this->db->query($sql_yar);
			$data_yar = $this->db->fetchAssoc($res_yar);
			// die($sql_yar);
			$current_year = (int) $data_yar['tahun'];
			$chart_kab['havedata'] = 1;
			$chart_kab['judul'] = $judul;
			$chart_kab['sumber'] = $sumber ;
			$chart_kab['satuan'] = $satuan;
			$chart_kab['type'] = 'column';
			$chart_kab['series'][0]['name'] = $judul;

			// rule tahun
			// AND kd.tahun = 2010 
			$sql_kab = "SELECT kg.kabupaten,kd.nilai,kd.tahun
				FROM kabupaten_gis kg
				LEFT JOIN kelompok_detail_kabupaten kd ON (
					kd.idkabupaten = kg.kodepemda
					AND kd.idkelompok = {$this->db->ci3db->escape($id_kab)}
				)
				ORDER BY kg.kodepemda ASC";
			$res_kab = $this->db->query($sql_kab);
			while ($tmpdata = $this->db->fetchAssoc($res_kab)) {
				$kategori[$tmpdata['tahun']][] = $tmpdata['kabupaten'];
				if ($tmpdata['nilai'] > 0) {
					$data_kategori[$tmpdata['tahun']][] = $tmpdata['kabupaten'];
					$data_tahun[$tmpdata['tahun']][] = number_format($tmpdata['nilai'],2,'.','');
				}		
			}
			$chart_kab['kategori'] = $kategori[$current_year];
			$chart_kab['series'][0]['data'] = $data_tahun[$current_year];
			$chart_kab['current_year'] = $current_year;
			$chart_kab['data'] = $data_tahun;
			$chart_kab['datakategori'] = $data_kategori;
		} else {
			//belum ada data
			$chart_kab['havedata'] = 0;
			$chart_kab['message'] = "Data Kabupaten belum tersedia!";
		}

		return [
			'series' => $series,
			'judul' => $judul,
			'sumber' => $sumber,
			'satuan' => $satuan,
			'kategori' => $ye,
			'type' => $chart_type,
			'data' => $data,
			'chart_kab' => $chart_kab,
		];
	}

	private function _frontlist_chart($id)
	{
		/*
		* CHART JSON OUTPUT
		*/
		$tahun 	= $_GET['tahun_chart'];
		$data 	= $this->_chartSource($id,array_map('intval', array_unique($tahun)));
  		return json_encode($data,JSON_NUMERIC_CHECK); //print angka sebagai numeric
	}

	private function _urusan($id){
		// list opsi sub/jenis urusan
		$this->checkAkses();
		if ($id > 0) {
			if ($id < 5) {
				$sqlsub = "SELECT * FROM kelompok WHERE idparent=".$this->db->escape_string($id)." ORDER BY ordering ASC";
			} else if ($this->userAkses == 'instansi'){
				// $idinstansi = $this->loadinstansi;

				// $sqlsub = "SELECT * FROM kelompok k 
				// 			LEFT JOIN users u ON k.iduser = u.iduser 
				// 			WHERE u.iduser IN (SELECT iduser FROM users WHERE idinstansi='".$this->loadinstansi."') 
				// 			AND k.idparent=".$id;
			} else {
				$sqlsub = "SELECT * FROM kelompok WHERE idparent=".$this->db->escape_string($id)." ORDER BY ordering ASC";
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

	function excelExport($typeExport='kelompok',$idexport='719',$tahun_export=array())
	{
		/*
		* exporting content to excell format
		* typeExport : type jenis data, eg : skpd/instansi, kelompok
		* idexport : id kelompok sumber data, bisa id instansi / idkelompok
		*/

		// global vars
		$this->typeExport 	= $typeExport;
		// jika kelompok, maka berdasarkan iddata, 
		// jika instnasi/bidang : diselect seluruh kelompok dalam instansi/bidang nya
		$this->idexport = $idexport;
		$this->tahunExport = $tahun_export;

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
			// $excel_filename = $dataKelompok['idkelompok'].".".$dataKelompok['urai']."_".min($this->tahunExport)."_".max($this->tahunExport).".xlsx";
			// $judul_tabel	= $dataKelompok['urai'];
			// $idkelompok_start = $idexport;
		} else if ($this->typeExport == 'instansi') {
			$sqlIns	= "SELECT * FROM instansi WHERE idinstansi={$this->db->escape_string($idexport)}";
			$resIns = $this->db->query($sqlIns);
			$dataInstansi = $this->db->fetchAssoc($resIns);
			$excel_filename = $dataInstansi['idinstansi'].".".$dataInstansi['nama_instansi']."_".min($this->tahunExport)."_".max($this->tahunExport).".xlsx";
			$judul_tabel	= $dataInstansi['nama_instansi'];
			$this->exportInstansi = $idexport;

			// id start 
			// [anovedit][users_table_ignored]
			$sqlKelompok = "SELECT k.idkelompok AS started_id
				FROM kelompok_matrix k
				WHERE k.idparent = 0
				AND k.pk_instansi = {$this->db->ci3db->escape($this->exportInstansi)}";
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
		$objPHPExcel->getProperties()
		->setCreator($this->users['username'])
		->setLastModifiedBy($this->users['username'])
		->setTitle($judul_tabel)
		->setSubject($excel_filename)
		->setDescription("file excell ini di export dari pusdalisbang papua, untuk dapat dipergunakan sebagaimana mestinya...")
		->setKeywords("pusdalisbang;".$kategory)
		->setCategory($kategory);
		
		// set variabel, codes
		$xcode = $this->makeExcelFileCode($this->typeExport,$idexport,min($this->tahunExport),max($this->tahunExport));
		$objPHPExcel->getActiveSheet()->setCellValue('A1',$xcode);

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

		// set Header (mulai dari baris 2)
		$thead = array('No','Kelompok/Sub Kelompok Data','Satuan');

		// set judul
		$closecol = chr(67+count($thead)+count($this->tahunExport)-1);
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('C1:'.$closecol.'1');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', $judul_tabel);
		$objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->applyFromArray($head_align);

		$xh = 0;
		foreach ($thead as $heads) {
			# code...
			$col = chr(67+$xh);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells($col.'2:'.$col.'3');
			$objPHPExcel->getActiveSheet()->setCellValue($col.'2', $heads);
			$objPHPExcel->getActiveSheet()->getStyle($col.'2')->getAlignment()->applyFromArray($head_align);
			$xh++;
		}

		// set header tahun
		$col = chr(67+count($thead));
		$closecol = chr(67+count($thead)+count($this->tahunExport)-1);
		$objPHPExcel->setActiveSheetIndex(0)->mergeCells($col.'2:'.$closecol.'2');
		$objPHPExcel->getActiveSheet()->setCellValue($col.'2', 'Tahun');
		$objPHPExcel->getActiveSheet()->getStyle($col.'2')->getAlignment()->applyFromArray($head_align);

		// set tahun
		$xt = 0;
		foreach ($this->tahunExport as $tahun) {
			$colY = chr(67+count($thead)+$xt);
			// $objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue($colY.'3', "$tahun");
			$objPHPExcel->getActiveSheet()->getStyle($colY.'3')->getAlignment()->applyFromArray($head_align);
			$xt++;
		}

		// auto width & coloring cell
 		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
 		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
 		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
		$objPHPExcel->getActiveSheet()->getStyle('C1:'.$closecol.'3')->applyFromArray($fill_color);

		// TEST BORDERING
		$objPHPExcel->getDefaultStyle()->applyFromArray([
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN
				)
			)
		]);

		// hide kolom id
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setVisible(false);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setVisible(false);

		// Freeze panes
		$objPHPExcel->getActiveSheet()->freezePane('A4');

		// Rows to repeat at top
		$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(2, 2);

		// load recrisive data
		$res_kelp = $this->db->query($sqlKelompok);
		$this->exceldata = array();
		$this->cellrowid = 0;
		while ($dataKelompok = $this->db->fetchAssoc($res_kelp)) {
			$idkelompok_start = $dataKelompok['started_id'];
			$this->_createExcelDataObject($idkelompok_start); //<-- load data 
		}

		// set Cell Data
		$started_row = 3;
		$started_coll = 3;
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
		$start_cell = chr(67+count($thead)).($started_row+1);
		$end_cell	= chr(67+count($thead)+count($this->tahunExport)-1).($started_row+count($this->exceldata));
		$objPHPExcel->getActiveSheet()->getStyle($start_cell.':'.$end_cell)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);

		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$excel_filename.'"');
		header('Cache-Control: max-age=0');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Dec 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$this->SaveViaTempFile($objWriter);
		// $objWriter->save('php://output');
		exit;
	}

	function SaveViaTempFile($objWriter)
	{
			$filePath = ROOT_PATH.'files/kelompok/';
			/* [anovedit][workaround] pastikan dir harus ada, karena jika tidak, gagal */
			if (!file_exists($filePath) || !is_dir($filePath)) mkdir($filePath);

			$filePath = $filePath . rand(0, getrandmax()) . rand(0, getrandmax()) . '.tmp';

	    $objWriter->save($filePath);
	    readfile($filePath);
	    unlink($filePath);
	    exit;
	}

	private function _createExcelDataObject($iddata,$tab=0)
	{
		/*
		* excell data sheet ceation
		* array format : no,kelompok/sub,satuan,tahun <- tahun x-n
		* select berdasarkan typeExport
		*/

		if ($this->typeExport == 'kelompok') {
			// $sqlKlp	= "SELECT * FROM kelompok 
			// 		WHERE 
			// 			idkelompok=".$this->db->escape_string($iddata);

			// $sql_child = "SELECT idkelompok FROM kelompok 
			// 		WHERE 
			// 			idparent=".$this->db->escape_string($iddata);
		} else if ($this->typeExport == 'instansi') {
			$sqlKlp	= "SELECT *
				FROM kelompok_matrix
				WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child = "SELECT idkelompok
				FROM kelompok_matrix k
				WHERE k.idparent= {$this->db->ci3db->escape($iddata)}
				AND k.pk_instansi = {$this->db->ci3db->escape($this->exportInstansi)}";
		}

		$res_kelp 		= $this->db->query($sqlKlp);
		$dataKelompok 	= $this->db->fetchAssoc($res_kelp);
		$res_child 	= $this->db->query($sql_child);
		$n_child 	= $this->db->numRows($res_child);

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
		$levl = str_repeat(' ', $dataKelompok['levl']*5);

		/* [anovedit] commit entry */
		$this->cellrowid++;
		$this->exceldata[$this->cellrowid][] = $dataKelompok['idkelompok'];
		$this->exceldata[$this->cellrowid][] = $this->encodeId_excel($dataKelompok['idkelompok']);
		$this->exceldata[$this->cellrowid][] = $this->cellrowid;
		$this->exceldata[$this->cellrowid][] = sprintf('%s %s. %s', $levl, $dataKelompok['numb'], $dataKelompok['urai']);
		$this->exceldata[$this->cellrowid][] = $dataKelompok['satuan'];

		if ($n_child > 0) {
			// parent kelompok
			// idkelompok ini masih memiliki child didalamannya
  		// recrisuve child
  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
  			$this->_createExcelDataObject($dataDetail['idkelompok'],$tab+1);
  		}

		} else if (!empty($iddata)) {
			// kelompok tanpa child
			$sqlDetail = "SELECT tahun,nilai
				FROM kelompok_detail_matrix
				WHERE idkelompok={$this->db->escape_string($iddata)}";
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
		// get / return var = $this->cellrowid,$this->exceldata
	}

	function encodeId_excel($id) {
		return substr(md5($id),10,10);
	}

	function makeExcelFileCode($a,$b=2,$c=1,$d=0){
		return substr(md5($a.$b.$c.$d),10,20);
	}

	function import()
	{
		/*
		* excell import management 
		*/
		$this->title = 'import file';

		if (isset($_POST['id'])) {
			$content = "";

			if($_FILES['filedata']['name'] <> '') {

				$new_file_name = md5($_FILES['filedata']['name']).'.xlsx';
				$uploaddir = ROOT_PATH.'files/kelompok/';

				// [anovedit][workaround]
				if (!file_exists($uploaddir) || !is_dir($uploaddir)) mkdir($uploaddir);

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
					for ($col = 5; $col < $highestColumnIndex; $col++) {
						$tahun[$col] = $objWorksheet->getCellByColumnAndRow($col, 3)->getValue();
						$tahun_header .= "<th>{$tahun[$col]}</th>";
					}

					// cek file code dan submit variablenya
					$check_code = $objWorksheet->getCell('A1')->getValue();
					$submit_code = $this->makeExcelFileCode($_POST['type'], (int) $_POST['id'], (int) $_POST['tahun_awal'], (int) $_POST['tahun_akhir']);

					if ($check_code == $submit_code) {
						$content .= "<li>File excell terbaca.. </li>";

						// listing data
						$cellData = array();
						for ($row = $startedRow; $row <= $highestRow; $row++) {
							$getId = $objWorksheet->getCellByColumnAndRow(0, $row)->getValue();
							$getCode = $objWorksheet->getCellByColumnAndRow(1, $row)->getValue();
  							if (!empty($getId) && $getCode == $this->encodeId_excel($getId)) {
  								$cellData[$getId]['no'] = $objWorksheet->getCellByColumnAndRow(2, $row)->getValue();
  								$cellData[$getId]['uraian'] = $objWorksheet->getCellByColumnAndRow(3, $row)->getValue();
  								$cellData[$getId]['satuan'] = $objWorksheet->getCellByColumnAndRow(4, $row)->getValue();
  								for ($col = 5; $col < $highestColumnIndex; $col++) {
								    $cellData[$getId][$tahun[$col]] = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
								}
  							}
						}

						$table = "
							<div class='table-responsive'>
								<table id='table_kelompok_input' class='detail_data table table-striped table-condensed table-bordered'>
									<tr>
										<th rowspan=2>No</th>
										<th rowspan=2>Kelompok/Sub Kelompok</th>
										<th rowspan=2>Satuan</th>
										<th colspan=".count($tahun).">Tahun</th>
									</tr>
									<tr>{$tahun_header}</tr>";

						// listing and Processing data
						$datausr = $this->auth->getDetail();
						$updated = $inserted = $nochange = 0;
						foreach ($cellData as $id => $cellContent) {
							// cek ini kelompok terdetail apa bukan
							$sql_cek = "SELECT idkelompok FROM kelompok_matrix WHERE idparent='{$this->db->escape_string($id)}'";
							$res_cek 	= $this->db->query($sql_cek);
							$num_child 	= $this->db->numRows($res_cek);

							$table .= "<tr>";
							$table .= "<td>{$cellContent['no']}</td>";
							$table .= "<td>{$cellContent['uraian']}</td>";
							$table .= "<td>{$cellContent['satuan']}</td>";

							foreach ($tahun as $tahun_detail) {
								$nominal = (int) $cellContent[$tahun_detail];
								// update | inseritng data
								$bgc = '';
								if ($num_child == 0) {
									// kelompok_detail : idkelompok, tahun, nilai, postdate, iduser
									$sql_dtl = "SELECT *
										FROM kelompok_detail_matrix
										WHERE idkelompok='{$this->db->escape_string($id)}'
										AND tahun='{$this->db->escape_string($tahun_detail)}'";
									$res_dtl = $this->db->query($sql_dtl);
									$data_dtl = $this->db->fetchAssoc($res_dtl);
									$num_dtl = $this->db->numRows($res_dtl);

									if ($num_dtl == 0 && $nominal > 0) {
										//add record
										$sql_ins = "INSERT INTO kelompok_detail_matrix SET
											nilai='{$this->db->escape_string($nominal)}',
											tahun='{$this->db->escape_string($tahun_detail)}',
											iduser='{$this->db->escape_string($datausr['iduser'])}',
											postdate=NOW(),
											idkelompok='{$this->db->escape_string($id)}'";
										$this->db->query($sql_ins);
										$inserted++;
										$bgc = 'bg-info';

									} else if ($num_dtl > 0 && $data_dtl['nilai'] <> $nominal) {
										// [anovedit][workaround] kalau nilainya kosong, hapus saja karena
										if (empty($nominal)) {
											$sql_upd = "DELETE FROM kelompok_detail_matrix
												WHERE tahun='{$this->db->escape_string($tahun_detail)}'
												AND idkelompok='{$this->db->escape_string($id)}'";
										} else {
											$sql_upd = "UPDATE kelompok_detail_matrix SET
												nilai={$this->db->ci3db->escape($nominal)},
												iduser='{$this->db->escape_string($datausr['iduser'])}',
												postdate=NOW()
												WHERE tahun='{$this->db->escape_string($tahun_detail)}'
												AND idkelompok='{$this->db->escape_string($id)}'";
										}
										$this->db->query($sql_upd);

										$updated++;
										$bgc = 'bg-success';
									} else {
										$nochange++;
										$bgc = 'bg-warning';
									}
								} 
								$table .= "<td class='{$bgc}'>{$nominal}</td>";
							}
							$table .= "</tr>";
						}
						$table .= "</table></div>";
						$content .= ($inserted > 0) ? "<li><span class='bg-info'>{$inserted} detail data baru di masukkan</span></li>" : "" ;
						$content .= ($updated > 0) ? "<li><span class='bg-success'>{$updated} detail data telah diperbarui</span></li>" : "" ;
						$content .= ($nochange > 0) ? "<li><span class='bg-warning'>{$nochange} detail tidak mengalami perubahan data</span></li>" : "" ;
						$content .= "<li>import data berhasil!, silakan pilih menu disamping untuk melanjutkan</li>" ;
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
			$this->tahunImport = array((int) $_GET['tahun_awal'], (int) $_GET['tahun_akhir']);

			#get data	
			// format nama file, sesuaikan, skpd x, bidang n. etc
			if ($this->typeImport == 'kelompok') {
				$sqlKlp	= "SELECT *
					FROM kelompok_matrix
					WHERE idkelompok={$this->db->escape_string($idimport)}";
				$resKelp 		= $this->db->query($sqlKlp);
				$dataKelompok 	= $this->db->fetchAssoc($resKelp);
				$excel_filename = $dataKelompok['idkelompok'].".".$dataKelompok['urai']."_".min($this->tahunImport)."_".max($this->tahunImport).".xlsx";
			} else if ($this->typeImport == 'instansi') {
				$sqlIns	= "SELECT *
					FROM instansi
					WHERE idinstansi={$this->db->escape_string($idimport)}";
				$resIns 		= $this->db->query($sqlIns);
				$dataInstansi 	= $this->db->fetchAssoc($resIns);
				$excel_filename = $dataInstansi['idinstansi'].".".$dataInstansi['nama_instansi']."_".min($this->tahunImport)."_".max($this->tahunImport).".xlsx";
			}

			$excel_filename = (!empty($excel_filename)) ? ", Nama file : <b>{$excel_filename}</b>" : $excel_filename ;

			#build form
			$tplform = new TemplateClass;
			$tplform->defineTag([
				'type' => $this->typeImport,
				'id' => $idimport,
				'tahun_awal' => min($this->tahunImport),
				'tahun_akhir' => max($this->tahunImport),
				'nama_file' => $excel_filename,
				'rootdir' 	=> ROOT_URL,
				'action' 	=> 'import'
			]);
			$tplform->init(THEME.'/forms/progis_file.html');
			$form = $tplform->parse();	
			$this->content = $form; 
		}
	}

	private function _updateDetail($id) {
		// validate User Access
		// update data / insert jika tidak ada
		// update tanggal
		// update entries

		$idkelompok = $_POST['idkelompok'];
		$tahun_detail = $_POST['tahun'];
		$nilai = (int) $_POST['nilai'];

		if ($this->hasAksesOnKelompok($idkelompok)) { //validasi akses
			$datausr = $this->auth->getDetail();

			// cek existensi data
			$q_cekdata = "SELECT *
				FROM kelompok_detail_matrix
				WHERE idkelompok='{$this->db->escape_string($idkelompok)}'
				AND tahun='{$this->db->escape_string($tahun_detail)}'";
			$qCek = $this->db->query($q_cekdata);
			$cekCount = $this->db->numRows($qCek);

			// data ada, update, jika tidak, [anovedit] hapus
			if ($cekCount > 0) {
				if ($nilai == 0) {
					$qUpdateInsertDetail = "DELETE FROM kelompok_detail_matrix
						WHERE idkelompok={$this->db->ci3db->escape($idkelompok)}
						AND tahun={$this->db->ci3db->escape($tahun_detail)}";
				} else {
					$qUpdateInsertDetail = "UPDATE kelompok_detail_matrix SET
						nilai={$nilai},
						postdate=now(),
						iduser={$this->db->escape_string($datausr['iduser'])}
						WHERE idkelompok={$this->db->escape_string($idkelompok)}
						AND tahun={$this->db->escape_string($tahun_detail)}";
				}
			} else {
				$qUpdateInsertDetail = "INSERT INTO kelompok_detail_matrix SET
					nilai={$nilai},
					idkelompok={$this->db->ci3db->escape($idkelompok)},
					iduser={$this->db->ci3db->escape($datausr['iduser'])},
					tahun={$this->db->ci3db->escape($tahun_detail)},
					postdate=now()";
			}

			$parentdata = array();

			if ($this->db->query($qUpdateInsertDetail)) {
				$message = "update data detail kelompok berhasil";
			} else {
				$message = "update data detail kelompok gagal".ERROR_TAG;
			}
		} else {
			$message = "tidak ada akses, silakan login kembali".ERROR_TAG;
		}
		return json_encode(array('message'=>$message,'tahun'=>$tahun_detail,'parent'=>$parentdata));
	}
}
