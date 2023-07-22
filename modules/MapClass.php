<?php
Class MapClass extends ModulClass{

	/**
	* MapClass dipakai untuk menampilkan data di peta interaktif
	* menggunakan view dari openlayer 3
	* jangan lupa menambahkan ol.css, map.css, ol.js, map.js
	* @author Bruri <bruri@gi.co.id>, modified by culis@gi.co.id
	* @version 1.0
	* @package MapClass
	**/
	
	function buildForm(){
		# menampilkan form
	}
	function Insert(){
		# query insert 
	}
	function Update(){
		# query update 
	}
	function Delete(){
		# query delete 
	}
	function Manage(){
		# grid & manajemen data
	}
	function FrontDisplay(){
		# tampilan depan
	}
	function FrontList(){
		# tampilan peta
		
		$this->pgScript = '
			<link rel="stylesheet" href="{themepath}/css/ol.css">
			<link rel="stylesheet" href="{themepath}/css/map.css">
			<script src="{themepath}plugins/Highcharts-4.2.3/js/highcharts.js"></script>
			<script src="{themepath}plugins/Highcharts-4.2.3/js/modules/exporting.js"></script>			
			<script type="text/javascript" src="{themepath}/js/number_format.js"></script>
			<script type="text/javascript" src="{themepath}/js/ol.js"></script>
			<script type="text/javascript" src="{themepath}/js/map.js"></script>
		';	

		$kat = strtoupper(trim($this->scr->filter($_GET['kat'])));
		$use_opsi_kabupaten = false;
		$use_opsi_tahun	= false;
		$use_menu_indikator = false;

		$map_type = "";
		
		$judul_peta = '';
		$pilihan_peta = '';
		$pilihan_analisa = '';
		$opsi_wilayah = '';

		if ($kat=='PROVINSI') {
			// tambahkan validasi ukuran file, jika melebihi x, maka akan ada konfirmasi
			$judul_peta = 'PETA SIMTARU';
			$pilihan_analisa = "
				<select id='jenis_analisa' class='form-control' onChange='haltFile(this.value);'>
				<option value='0'>-- Pilih Peta --</option>
			";
			foreach (glob(ROOT_PATH."/files/simtaru/*.shp") as $filename) {
				$file_size = filesize($filename);
				$pilihan_analisa .= "<option data-filesize='{$file_size}' value=\"".basename($filename)."\">".str_replace("_"," ",basename(strtoupper($filename),".SHP"))."</option>
				";
			}
			$pilihan_analisa .= '</select>

			<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							Konfirmasi
						</div>
						<div class="modal-body">
							Ukuran File Peta terlalu besar, lanjutkan memuat ?
						</div>
						<div class="modal-footer">
							<button class="btn btn-danger btn-ok">Lanjutkan</button>
							<button type="button" class="btn btn-default btn-cancel" data-dismiss="modal">Batalkan</button>
						</div>
					</div>
				</div>
			</div>
			';

		} else if ($kat=='TEMATIK') {
			$judul_peta = 'PETA TEMATIK KABUPATEN';

			// ramdom load id
			$sql_rand = "SELECT idkelompok FROM kelompok_kabupaten 
						WHERE idparent <> 0 
							AND idkelompok > 601 AND idkelompok < 615
							AND idkelompok NOT IN (SELECT idparent FROM kelompok_kabupaten)
						ORDER BY RAND() LIMIT 1;";
			$res_rand = $this->db->query($sql_rand);
			$data_rand = $this->db->fetchAssoc($res_rand);

			$this->pgScript .= "
			<script>
			$(document).ready(function(){
				loadKelompok('{$data_rand['idkelompok']}');
				// loadKabupatenMap_detail('0');
			});
			</script>
			";

			$map_type = "tematik";

			$use_opsi_kabupaten = true;
			$use_menu_indikator = true;
			$use_opsi_tahun = true;
		}  else if ($kat=='WILAYAHADAT') {
			$judul_peta = 'PETA WILAYAH ADAT';

			$opsi_wilayah .= "Wilayah Adat : 
				<select id='jenis_analisa' class='map-control' onChange='loadWilayahMap(this.value);'>
				<option value='0'>-- Semua Wilayah --</option>
			";

			$sql_kabgm = "SELECT * 
						FROM wilayah w
						ORDER BY wilayah ASC
						";
			$qKab = $this->db->query($sql_kabgm);
			while ($rKab = $this->db->fetchAssoc($qKab)) {
				$opsi_wilayah .= "<option value=\"{$rKab['idwilayah']}\">{$rKab['wilayah']}</option>";
			}
			$opsi_wilayah .= "</select>";

			// ramdom load id
			$sql_rand = "SELECT idkelompok FROM kelompok_kabupaten 
						WHERE idparent <> 0 
							AND idkelompok > 601 AND idkelompok < 615
							AND idkelompok NOT IN (SELECT idparent FROM kelompok_kabupaten)
						ORDER BY RAND() LIMIT 1;";
			$res_rand = $this->db->query($sql_rand);
			$data_rand = $this->db->fetchAssoc($res_rand);

			$this->pgScript .= "
			<script>
			$(document).ready(function(){
				loadKelompok('{$data_rand['idkelompok']}');
				// loadWilayahMap('0');
			});
			</script>
			";

			$map_type = "wilayah_adat";

			$use_opsi_kabupaten = false;
			$use_menu_indikator = true;
			$use_opsi_tahun = true;
		} else {
			$judul_peta = 'ANALISA KABUPATEN';

			// ramdom load id
			$sql_rand = "SELECT idanalisis FROM analisa_kabupaten
						ORDER BY RAND() LIMIT 1;";
			$res_rand = $this->db->query($sql_rand);
			$data_rand = $this->db->fetchAssoc($res_rand);

			$this->pgScript .= "
			<script>
			$(document).ready(function(){
				loadKabupatenMap('{$data_rand['idanalisis']}');
			});
			</script>
			";

			// Analisa Perbandingan Tabel
			$pilihan_analisa = "
				<select id='jenis_analisa' class='map-control' onChange='loadKabupatenMap(this.value);'>
				<option value='0'>-- Pilih Analisa --</option>
			";
			$qAnalisa = $this->db->query("SELECT * FROM analisa_kabupaten");
			while ($rAnalisa = $this->db->fetchAssoc($qAnalisa)) {
				$selected = ($data_rand['idanalisis'] == $rAnalisa['idanalisis']) ? "selected" : "" ;
				$pilihan_analisa .= "<option {$selected} value=\"{$rAnalisa['idanalisis']}\">{$rAnalisa['judul']}</option>";
			}
			$pilihan_analisa .= "</select>";

			$map_type = "analisa_kabupaten";

			$use_menu_indikator = false;
			$use_opsi_kabupaten = false;
			$use_opsi_tahun = true;
		}			

		$this->pgScript .= '<script>
			$(document).data(\'print_title\',\''.$judul_peta.'\');
		</script>';			
					
		$opsi_kabupaten = "";
		if ($use_opsi_kabupaten){
			$opsi_kabupaten .= "Lokasi : 
				<select id='jenis_analisa' class='map-control' onChange='loadKabupatenMap_detail(this.value);'>
				<option value='0'>-- Semua Kabupaten --</option>
			";

			$sql_kabgm = "SELECT k.kodepemda,k.kabupaten 
						FROM kabupaten_gis k
						ORDER BY k.kabupaten
						";
			$qKab = $this->db->query($sql_kabgm);
			while ($rKab = $this->db->fetchAssoc($qKab)) {
				$opsi_kabupaten .= "<option value=\"{$rKab['kodepemda']}\">{$rKab['kabupaten']}</option>";
			}
			$opsi_kabupaten .= "</select>";
		}

		$opsi_tahun = "";
		if ($use_opsi_tahun) {

			// Indikator kelompok Data {masukkan ke map}
			$sql_min = "SELECT min(tahun) as min FROM kelompok_detail_kabupaten LIMIT 1";
			$res_min = $this->db->query($sql_min);
			$data_min = $this->db->fetchAssoc($res_min);
			$tahun_awal = $data_min['min'];
			$tahun_akhir = date("Y");

			$opsi_tahun = "Tahun : <select id='tahun' data-map-type='{$map_type}' class='map-control'>";
			for ($x_tahun = $tahun_awal; $x_tahun <= $tahun_akhir; $x_tahun++) {
				$opsi_tahun .= "<option>{$x_tahun}</option>";
			}
			$opsi_tahun .= "</select>";
		}

		$menu_indikator = "";
		if ($use_menu_indikator) {
			$menu_indikator = '
				&nbsp;Indikator : <a href="#" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="judul">{Kelompok/Indikator}</span> <span class="caret"></span></a>
				<input id="indikator_kelompok" hidden>
				<ul class="dropdown-menu">';
			// load kelompok-indikator
			$sql_kelompok = "SELECT * FROM kelompok_kabupaten WHERE idparent=0;";
			$res_kelompok = $this->db->query($sql_kelompok);
			while ($row_kelompok = $this->db->fetchAssoc($res_kelompok)) {
				//no
			}
			$this->recursiveMenu($menu_indikator,0);
			$menu_indikator .= "</ul>";
		}

		// die($menu_indikator);

		$this->pgContent = '
			<div class="col-md-12">
			  	<div class="box box-primary" id="search_panel">
				   	<div class="box-header with-border">
					  	<h3 class="box-title">'.$judul_peta.'</h3>
					  	<br><br>
					  	<div>
							<strong><i class="fa fa-file-text-o margin-r-5"></i> Notes</strong>
							<ul>
							<li>pilihan analisa / indikator, wilayah / kabupaten dan tahun ada diatas peta</li>
							<li>untuk menampilkan chart, tabel data dan pilihan cetak ada di tombol navigasi kanan atas peta </li>
							</ul>
					  	</div>
					  	<div class="pull-right box-tools">
		                  <h5><a href="#" id="btn_map_tips" class="text-success" onclick="startIntro_page(\'PETA INTERAKTIF '.$kat.'\');"><i class="fa fa-question-circle"></i>&nbsp;Tips</a></h5>
		                </div>
					  	<div>'.$pilihan_peta.'</div>
					</div>
					<!-- /.box-header -->

					<div class="box-body">
						<!-- PAGE CONTENT WRAPPER -->
						<div class="page-content-wrap " id="map_content">    

							<div id="over_layer">

								<div id="judul_peta" class="judul-peta">
									<div class="dropdown" style="position:relative">
										'.$pilihan_analisa.'
										'.$menu_indikator.'
										'.$opsi_kabupaten.'
										'.$opsi_wilayah.'
										'.$opsi_tahun.'
									</div>
								</div>

								<div id="map_title" class="map_title ol-unselectable ol-control">
									<table>
										<tr><td class="judul">Peta Pusdalisbang Papua</td><td rowspan=3><img src="{home}/files/skpd/basic.png" height=100px></td></tr>
										<tr><td class="sumber">Sumber : Pusdalisbang *</td></tr>
										<tr><td>Didukung oleh : <b>Pusdalisbang Provinsi Papua</b></td></tr>
									</table>
								</div>

								<div id="options" class="no-print">
									<button onClick="cetakHalamanMap();" class="btn btn-warning btn-sm "  data-toggle="tooltip" title="Cetak" ><i class="fa fa-print"></i> Print</button>
									<button class="btn btn-info btn-sm ol-icon chart-button" data-toggle="tooltip" title="Chart" ><i class="fa fa-area-chart"></i></button>
									<button class="btn btn-info btn-sm ol-icon table-button" data-toggle="tooltip" title="Lihat Tabel Data" ><i class="fa fa-table"></i></button>
									<button id="reset_btn" class="btn btn-info btn-sm ol-icon" onClick="resetMap();" data-toggle="tooltip" title="Reset Map" ><i class="fa fa-refresh"></i></button>
								</div>  

								<div class="box box-solid" id="table_data" hidden>
									<div class="box-header ui-sortable-handle" style="cursor: move;">
									  <!-- tools box -->
									  <div class="pull-right box-tools no-print">
										<button type="button" class="btn btn-warning btn-sm pull-right" data-toggle="tooltip" title="Close Tabel" onClick="$(\'#table_data\').hide();">
										  <i class="fa fa-close"></i></button>
									  </div>
									  <!-- /. tools -->
									  <i class="fa fa-table"></i>
									  <h3 class="box-title">
										Data Table
									  </h3>
									</div>
									<div class="box-body table-container" style="">
									
									</div>
									<!-- /.box-body-->
								</div>

								<div class="box box-solid" id="chart" hidden>
									<div class="box-header ui-sortable-handle" style="cursor: move;">
									  <!-- tools box -->
									  <div class="pull-right box-tools no-print">
										<button type="button" class="btn btn-warning btn-sm pull-right" data-toggle="tooltip" title="Close Chart" onClick="$(\'#chart\').hide();">
										  <i class="fa fa-close"></i></button>
									  </div>
									  <!-- /. tools -->
									  <i class="fa fa-area-chart"></i>
									  <h3 class="box-title">
										Chart
									  </h3>
									</div>
									<div class="box-body chart-container" style="position:relative;">
										<div id="chart1" class="" style="position:relative; width: auto; height: 300px; margin: 0 auto"></div>
									</div>
									<!-- /.box-body-->
								</div>

							</div>
							<!-- end of div over layer -->

							<div class="papua_map fullscreen" id="papua_map"></div>
							<div id="popup" class="ol-popup">
						  		<div id="popup-content"></div>
							</div>
						</div>
						<!-- PAGE CONTENT WRAPPER --> 
					</div>
					<!-- end of box-body -->
			  	</div>
			</div>
		';
		
		$this->menu = new MenuClass;
		// $this->link = new LinkClass;
		// $this->berita = new BeritaClass;
		// $this->slider = new SliderClass;
		$this->user = new UserClass;
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'menufooter'	=> $this->menu->FrontDisplay('B'),
						 // 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> "PETA INTERAKTIF ".$kat,
						 'pagecontent'	=> $this->pgContent,
						 'pagescript'	=> $this->pgScript,
						 // 'sidenews'		=> $this->sidenews,		
						 // 'latestnews'	=> $this->berita->LatestNews(),	
						 'account_menu'	=> $this->user->AccountMenu(),
						 'home'			=> ROOT_URL,
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
	function GetDetail($id){
		# detail artikel
	}
	function getJSON($id){
		// print_r($_GET);
		# tampilan depan
		//echo 'VOID';
		$jmode = $_GET['ajaxmode'];
		switch ($jmode) {
			case 'provinsi':
				return $this->_getDataProvinsi($id);
			break;
			case 'kabupaten':
				return $this->_getDataKabupaten($id);
			break;				
			case 'wilayah_detail':
				return $this->_getDataWilayahDetail($id);
			break;					
			case 'kabupaten_detail':
				return $this->_getDataKabupatenDetail($id);
			break;			
			case 'gerbangmas':
				return $this->_getDataGerbangmas($id);
			break;
			case 'listsubkelompok_kabupaten':
				return $this->_listSubKelompok_Kabupaten($id);
			break;			
			case 'chart_kabupaten':
				return $this->_chart_Kabupaten($id);
			break;
			default:
				# code...
			break;
		}
	}

	function recursiveMenu( &$sresult = "", $parent = 0, $level = 0,$parent_urai = array()){
			
			$sql = "SELECT *, idkelompok as id 
				FROM kelompok_kabupaten 	
				WHERE idparent = '$parent' 
				order by ordering ";

			$dataSource = $this->db->query($sql);

			if($this->db->numRows($dataSource) > 0){
				$sresult .= ($level > 0)?"<ul class=\"dropdown-menu sub-menu\">\n":"";
				while($data = $this->db->fetchArray($dataSource)){
					
					// cek child menu
					$sql_child = "SELECT count(idkelompok) as nummenu FROM kelompok_kabupaten 	
									WHERE idparent = '$data[idkelompok]'";
					$data_child = $this->db->query($sql_child);
					$rec_child = $this->db->fetchAssoc($data_child);

					// menu yg aktif
					// $c_active = ($_GET['mode'].'/' == $menuData['url']) ? 'active' : '' ;
					$c_active = "";

					// // menu url
					// $url = str_replace('{home}', ROOT_URL, $data['url']);
					// $url = (substr($url,0,4)=='http')?$url:ROOT_URL.$url;
					// $url = ( in_array(strtolower($data['menu']), array("home","beranda")))?ROOT_URL:$url;

					//
					$item = wordwrap($data['urai'],48,"</br>");

					// select menu. drop down or not
					if ($rec_child['nummenu'] > 0) {
						$sresult .="<li class=\"$c_active\">
						<a href=\"javascript:void();\" class=\"trigger right-caret\">".$item."</a>\n";
						$parent_urai[$data['id']] = ($level == 0) ? "" : $parent_urai[$data['idparent']]." <i class='fa fa-arrow-circle-right'></i> ";	
						$parent_urai[$data['id']] .= $data['urai'];	
						$this->RecursiveMenu($sresult,$data['idkelompok'],$level+1,$parent_urai);
						$sresult .="</li>\n";
					} else {
						$sresult .="<li class=\"$c_active \">
						<a id='indikator_{$data['id']}' data-judul=\"{$parent_urai[$data['idparent']]} <i class='fa fa-arrow-circle-right'></i> {$data['urai']}\" onClick=\"loadKelompok('{$data['id']}');\">".$item."</a>\n
						</li>\n";						
					}
				}
				$sresult .= ($level > 0)?"</ul>\n":"";
			}			
	}

	private function _getDataProvinsi($id){
		// load kabupaten geometry
		$id = $this->db->escape_string($_POST['id']);
		$legend = Array();
		$data = Array();
		$legend[] = array('urai'=> 'test', 'hexcolor' => '#'.dechex(255).dechex(204).dechex(0)); 		
		
		echo '{"legend":[{"urai":"test","hexcolor":"#ffcc0"}],"map":[';
		try {
			$ShapeFile = new ShapeFile(ROOT_PATH."/files/simtaru/".$id);
			
			while ($record = $ShapeFile->getRecord(SHAPEFILE::GEOMETRY_WKT)) {
				if ($record['dbf']['deleted']) continue;
				$row['id'] = $record['num'];	
				$row['urai'] = '';	
				$row['data'] = $record['dbf']; 				
				$row['color'] = array(255,204,0,0.8);
				$row['stroke'] = array(0,0,0,0.2);	
				$row['wkt'] = $record['shp'];
				unset($row['data']['deleted']); 
				if ($row['id']>1) echo ",";
				echo json_encode($row);
				//$data[] = $row;	
			}
			
		} catch (ShapeFileException $e) {
			$legend = Array();
			$legend[] = array('urai'=> 'Error '.$e->getMessage(), 'hexcolor' => '#000000'); 
		}
		echo ']}';
		//$result['legend'] = $legend;
		//$result['map'] = $data; 
		//print json_encode($result);
	}

	private function _createMapLegend(&$legend=array(),$scr_idkelompok=0,$scr_tahun=2010,$multi_legend=true,$num_legend = 3,$rcolor=255,$gcolor=255,$bcolor=255) {

		// cek map_legend untuk custom legenda
		/*$sql_legend = "SELECT *
				FROM map_legend 
				WHERE idkelompok='{$scr_idkelompok}'";
		$res_legend = $this->db->query($sql_legend)
		if ($this->db->numRows($res_legend) > 0) {
			$data_legend = $this->db->fetchAssoc($res_legend);
		} else {

		}*/

		// nilai maximal dan minimal
		$sql_max_min = "SELECT max(nilai) as max_nilai,min(nilai) as min_nilai 
				FROM kelompok_detail_kabupaten 
				WHERE idkelompok='{$scr_idkelompok}' AND tahun='{$scr_tahun}'";
		$data_range = $this->db->fetchAssoc($this->db->query($sql_max_min));
		// + limitasi jika kosong, set 100
		$data_range['max_nilai'] = ($data_range['max_nilai'] == 0) ? 100 : $data_range['max_nilai'] ;
		$range_nilai = $data_range['max_nilai'] - $data_range['min_nilai'];

		// data legend
		if ($multi_legend) {
			for ($x_legend = 1; $x_legend <= $num_legend; $x_legend++) {
				$upper_value = ceil($data_range['max_nilai']-($x_legend-1)*$range_nilai/($num_legend));
				$lower_value = $upper_value - floor($data_range['max_nilai']/($num_legend));
				$xa = $upper_value/$data_range['max_nilai']*100;
				$legend[] = array('urai'=> "{$lower_value} - {$upper_value} {$data_klp['satuan']}", 'hexcolor' => '#'.dechex($rcolor-$xa).dechex($gcolor-$xa).dechex($bcolor-$xa)); 	
			}
		}
		return $data_range;
	}

	private function _getDataKabupaten($id){
		// $scr_idkelompok = $this->scr->filter($_POST['idkelompok']);
		$scr_tahun = $this->scr->filter($_POST['tahun']);
		$scr_id = $this->scr->filter($id);
		$legend_title = Array();
		$sql_analisa_kab = "SELECT akab.*,ak.*,k.*,k.urai,k.idkelompok,i.nama_instansi 
					FROM analisis_kelompok_kabupaten ak 
					LEFT JOIN kelompok_kabupaten k ON (k.idkelompok = ak.idkelompok) 
					LEFT JOIN analisa_kabupaten akab ON (akab.idanalisis = ak.idanalisis) 
					LEFT JOIN instansi i ON i.`idinstansi` IN (SELECT idinstansi FROM users WHERE iduser=k.`iduser`)
					WHERE ak.idanalisis=".$scr_id." LIMIT 2";
		// die($sql_analisa_kab);
		$sumber 	= array();
		$res_akab 	= $this->db->query($sql_analisa_kab);
		$data_kelp1 	= $this->db->fetchAssoc($res_akab);

		$kelompok1 	= $data_kelp1['idkelompok'];
		$legend_title[] = array('urai'=> $data_kelp1['urai'], 'hexcolor' => ''); 
		$sumber[] 	= "1. ".$data_kelp1['nama_instansi'];
		// $data_range1 = $this->_createMapLegend($legend,$kelompok1,$scr_tahun,true,3,220,130,220);
		// $legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 	

		$createLegend 	= $this->_createMapLegendForAnalisa($data_kelp1['idkelompok'],$scr_tahun);
		$legend 		= $legend_title+$createLegend['legend'];
		$data_range1 	= $createLegend['data_range'];
		$custom_legend 	= $createLegend['custom'];

		// ledenda kedua
		$data_kelp2 = $this->db->fetchAssoc($res_akab);
		$kelompok2 = $data_kelp2['idkelompok'];
		$legend[] = array('urai'=> $data_kelp2['urai'], 'hexcolor' => '#'.dechex(255).dechex(204).dechex(0)); 
		// $legend[] = array('urai'=> $data_kelp2['urai'], 'hexcolor' => ''); 
		$data_range2 = $this->_createMapLegend($legend,$kelompok2,$scr_tahun,false);
		$sumber[] = "2. ".$data_kelp2['nama_instansi'];

		// legenda untuk nilai kosong

		// load kabupaten geometry
		$data = Array();
		$sql = "SELECT kel.*,kd2.nilai AS nilai2, kd.*,k.*,
					ASTEXT(k.geom) AS wkt_1,
					ASTEXT(CENTROID(k.geom)) AS wkt_2
					FROM kabupaten_gis k					
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$kelompok1}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$kelompok1}' AND kd.tahun='{$scr_tahun}'
					LEFT JOIN kelompok_detail_kabupaten kd2 ON kd2.idkabupaten=k.kodepemda AND kd2.idkelompok = '{$kelompok2}' AND kd2.tahun='{$scr_tahun}'";
		$chart['type'] = 'column';
		// die($sql);
		$res = $this->db->query($sql);

		$chart['judul'] = $data_kelp1['judul'];
		$chart['sumber'] = (!empty($data_kelp1['sumber'])) ? $data_kelp1['sumber'] : "Pusdalisbang (*)" ;

		$chart['series1']['satuan'] = $data_kelp1['satuan'];
		$chart['series1']['name'] 	= $data_kelp1['urai'];

		$chart['series2']['satuan'] = $data_kelp2['satuan'];
		$chart['series2']['name'] = $data_kelp2['urai'];
			
		$table_data = "<table id='tbl_detail' class='table table-bordered table-striped detail_data'><tr><th>No</th>";
		$table_data .= "<th>Kabupaten</th>";
		$table_data .= "<th>{$data_kelp1['urai']} ({$data_kelp1['satuan']})</th>";
		$table_data .= "<th>{$data_kelp2['urai']} ({$data_kelp2['satuan']})</th></tr>";

		$i = 0;

		$data_peta['namapeta'] = $data_kelp1['judul']." th.".$scr_tahun;
		$data_peta['sumberpeta'] = "Sumber :<br>".implode('<br>',$sumber);

		while($tmpdata = $this->db->fetchAssoc($res)){

			$i++;

			$table_data .= "<tr><td>{$i}</td>";
			$table_data .= "<td>{$tmpdata['kabupaten']}</td>";
			$table_data .= "<td>".number_format($tmpdata['nilai'],2,'.','')."</td>";
			$table_data .= "<td>".number_format($tmpdata['nilai2'],2,'.','')."</td>
				</tr>";

			$chart['kategori'][] = $tmpdata['kabupaten'];
			$chart['series1']['data'][] = number_format($tmpdata['nilai'],2,'.','');
			$chart['series2']['data'][] = number_format($tmpdata['nilai2'],2,'.','');

			$row['id'] = $tmpdata['kodepemda'];	
			$row['tdata'] = '<li>{data row}</li>';
			$row['urai'] = 'Kab '.$tmpdata['kabupaten'];	
			$row['data_1'] = number_format($tmpdata['nilai'],2,'.','');
			$row['data_2'] = number_format($tmpdata['nilai2'],2,'.','');

			$row['data_1_satuan'] = $data_kelp1['satuan']; 
			$row['data_2_satuan'] = $data_kelp2['satuan']; 
				
			$row['kelompok_1'] = $legend[0]['urai'];
			$row['kelompok_2'] = $legend[1]['urai'];
			$row['pct_1'] = $row['data_1']; 
			$row['pct_2'] = $row['data_2']/$data_range2['max_nilai']*100; // radius effect
				
			// if ($row['pct_1'] == null) {
			// 	$row['color_1'] = array(225,225,225,0.8);	
			// } else {
			// 	$a = $row['pct_1']/$data_range1['max_nilai']*100; 
			// 	$row['color_1'] = array(220-$a,130-$a,220-$a,0.8);	
			// }

			// coloring polygon
			if ($row['pct_1'] == null) {
				$row['color_1'] = array(225,225,225,0.8);	
			} else {
				if ($custom_legend) {
					$row['color_1'] = $this->_getColorPolygonForAnalisa($row['pct_1']);
				} else {
					$a = $row['pct_1']/$data_range['max_nilai']*100; 
					$row['color_1'] = array(220-$a,130-$a,220-$a,0.8);	
				}
			}

			$row['color_2'] = array(255,204,0,0.8);
			$row['stroke_1'] = array(0,0,0,0.2);	
			$row['stroke_2'] = array(255,204,0,0.2);				
			$row['wkt_1'] = $tmpdata['wkt_1'];	
			$row['wkt_2'] = $tmpdata['wkt_2'];			
			$data[] = $row;				
		}
		
		$table_data .= "</table>";
		$result['table_data'] = $table_data;
		$result['chart'] = $chart;
		$result['legend'] = $legend;
		$result['map'] = $data; 
		$result['data'] = $data_peta; 
		print json_encode($result,JSON_NUMERIC_CHECK);
	}	

	private function _createMapLegendForAnalisa($idkelompok,$tahun) {
		// $legend = $this->_createMapLegendForAnalisa($scr_idkelompok,$scr_tahun);
		// cek map_legend untuk custom legenda
		$sql_legend = "SELECT *
				FROM map_legend 
				WHERE idkelompok='{$idkelompok}' ORDER BY batas_atas DESC";
		$res_legend = $this->db->query($sql_legend);
		if ($this->db->numRows($res_legend) > 0) {

			$this->customColorLegend_Analisa = $maxval = array();
			while($data_legend = $this->db->fetchAssoc($res_legend)) {
				$legend[] = array(
					'urai'		=> "{$data_legend['batas_bawah']} - {$data_legend['batas_atas']} {$data_legend['satuan']} </td><td>({$data_legend['label']})", 
					'hexcolor' 	=> $data_legend['warna']
				); 	
				$maxval[] = $data_legend['batas_atas'];
				$this->customColorLegend_Analisa[] = array(
					'warna' => $data_legend['warna'],
					'max' 	=> $data_legend['batas_atas'],
					'min' 	=> $data_legend['batas_bawah']
					);
			}

			// jika tidak ada setelah untuk data kosong
			if (min($maxval) > 0) {
				$legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 
			}

			// nilai maximal dan minimal
			$sql_max_min = "SELECT max(batas_atas) as max_nilai,min(batas_bawah) as min_nilai 
					FROM map_legend 
					WHERE idkelompok='{$idkelompok}'";
			$data_range = $this->db->fetchAssoc($this->db->query($sql_max_min));
			// + limitasi jika kosong, set 100
			$data_range['max_nilai'] = ($data_range['max_nilai'] == 0) ? 100 : $data_range['max_nilai'] ;
			$iscustom = true;

		} else {
			// nilai maximal dan minimal
			$sql_max_min = "SELECT max(nilai) as max_nilai,min(nilai) as min_nilai 
					FROM kelompok_detail_kabupaten 
					WHERE idkelompok='{$idkelompok}' AND tahun='{$tahun}'";
			$data_range = $this->db->fetchAssoc($this->db->query($sql_max_min));
			// + limitasi jika kosong, set 100
			$data_range['max_nilai'] = ($data_range['max_nilai'] == 0) ? 100 : $data_range['max_nilai'] ;
			$range_nilai = $data_range['max_nilai'] - $data_range['min_nilai'];

			$num_legend = 5;

			// data legend
			for ($x_legend = 1; $x_legend <= $num_legend; $x_legend++) {
				$upper_value = ceil($data_range['max_nilai']-($x_legend-1)*$range_nilai/($num_legend));
				$lower_value = $upper_value - floor($data_range['max_nilai']/($num_legend));
				$xa = $upper_value/$data_range['max_nilai']*100;
				$legend[] = array('urai'=> "{$lower_value} - {$upper_value} {$data_klp['satuan']}", 'hexcolor' => '#'.dechex(220-$xa).dechex(130-$xa).dechex(220-$xa)); 	
			}
			$legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 
			$iscustom = false;
		}
		// die(print_r($this->customColorLegend_Analisa,1));

		return array(
			'legend' 		=> $legend,
			'data_range' 	=> $data_range,
			'custom' 		=> $iscustom
			);
	}

	private function _getColorPolygonForAnalisa($nilai) {
		$warna = "";
		foreach ($this->customColorLegend_Analisa as $clegend) {
			if ($nilai <= $clegend['max'] && $nilai >= $clegend['min']) {
				$warna = $clegend['warna'];
			}
		}
		return array(hexdec(substr($warna,1,2)),hexdec(substr($warna,3,2)),hexdec(substr($warna,5,2)),0.8);
	}

	private function _getDataWilayahDetail($id){
		$scr_idkelompok = $this->scr->filter($_POST['idkelompok']);
		$scr_tahun = $this->scr->filter($_POST['tahun']);
		$id = $this->db->escape_string($_POST['id']);

		$legend = Array();
		$res_klp = $this->db->query("SELECT k.*,i.nama_instansi AS sumber 
										FROM kelompok_kabupaten k 
										LEFT JOIN users u ON u.iduser = k.iduser
										LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
										WHERE idkelompok='".$scr_idkelompok."'");
		$data_klp = $this->db->fetchAssoc($res_klp);
		// $legend[] = array('urai'=> $data_klp['urai'].", tahun {$scr_tahun}", 'hexcolor' => ''); 

		// nilai maximal dan minimal
		$sql_max_min = "SELECT max(nilai) as max_nilai,min(nilai) as min_nilai 
				FROM kelompok_detail_kabupaten 
				WHERE idkelompok='{$scr_idkelompok}' AND tahun='{$scr_tahun}'";
		$data_range = $this->db->fetchAssoc($this->db->query($sql_max_min));
		// + limitasi jika kosong, set 100
		$data_range['max_nilai'] = ($data_range['max_nilai'] == 0) ? 100 : $data_range['max_nilai'] ;
		$range_nilai = $data_range['max_nilai'] - $data_range['min_nilai'];
		$num_legend = 5;

		// data legend
		for ($x_legend = 1; $x_legend <= $num_legend; $x_legend++) {
			$upper_value = ceil($data_range['max_nilai']-($x_legend-1)*$range_nilai/($num_legend));
			$lower_value = $upper_value - floor($data_range['max_nilai']/($num_legend));
			$xa = $upper_value/$data_range['max_nilai']*100;
			// $legend[] = array('urai'=> "{$lower_value} - {$upper_value} {$data_klp['satuan']}", 'hexcolor' => '#'.dechex(220-$xa).dechex(130-$xa).dechex(220-$xa)); 	
		}
		// $legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 	
		$sql_wil = "SELECT * FROM wilayah";
		$res_wil = $this->db->query($sql_wil);
		while ($data_wil = $this->db->fetchAssoc($res_wil)){
			$wilayah[$data_wil['idwilayah']] = $data_wil['wilayah'];
			$color[$data_wil['idwilayah']]['r'] = hexdec(substr($data_wil['color'],1,2));
			$color[$data_wil['idwilayah']]['g'] = hexdec(substr($data_wil['color'],3,2));
			$color[$data_wil['idwilayah']]['b'] = hexdec(substr($data_wil['color'],5,2));
			$legend[] = array('urai'=> '&nbsp;'.$data_wil['wilayah'], 'hexcolor' => $data_wil['color']); 	
		}

		// load kabupaten geometry
		$data = Array();
		if ($id > 0) {
			$sql = "SELECT kel.*,kd.*,k.*,
					AsText(k.geom) as wkt_1,
					AsText(centroid(k.geom)) as wkt_2
					FROM kabupaten_gis k
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$scr_idkelompok}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$scr_idkelompok}'
					WHERE k.kodepemda = '".$id."'
					GROUP BY kd.tahun";
			$chart['type'] = 'line';
		} else if ($id == 0) {
			$sql = "SELECT kel.*,kd.*,k.*,
					ASTEXT(k.geom) AS wkt_1,
					ASTEXT(CENTROID(k.geom)) AS wkt_2
					FROM kabupaten_gis k					
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$scr_idkelompok}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$scr_idkelompok}' AND kd.tahun={$scr_tahun}
					";			
			// $sql = "SELECT k_gis.*,
			// 			ASTEXT(GROUP_CONCAT(k_gis.geom)) AS wkt_1,
			// 			ASTEXT(CENTROID(k_gis.geom)) AS wkt_2
			// 		FROM kabupaten_gis k_gis
			// 		GROUP BY idwilayah
			// 		";
			$chart['type'] = 'column';
		}
		// die($sql);
		$res = $this->db->query($sql);

		$chart['judul'] = $data_klp['urai'];
		$chart['sumber'] = (!empty($data_klp['sumber'])) ? $data_klp['sumber'] : "Pusdalisbang (*)" ;
		$chart['satuan'] = $data_klp['satuan'];
		$chart['series'][0]['name'] = $data_klp['urai'];
			
		$data_peta['namapeta'] = $chart['judul']." th.".$scr_tahun;
		$data_peta['sumberpeta'] = "Sumber : ".$chart['sumber'];

		$table_data = "<table id='tbl_detail' class='table table-bordered table-striped detail_data'><tr><th>No</th>";
		$table_data .= ($id > 0) ? "<th>Tahun</th>" : "<th>Kabupaten</th>";
		$table_data .= "<th>{$data_klp['urai']} ({$data_klp['satuan']})</th></tr>";

		$i = 0;

		while($tmpdata = $this->db->fetchAssoc($res)){

			$i++;

			$table_data .= "<tr><td>{$i}</td>";
			$table_data .= ($id > 0) ? "<td>{$tmpdata['tahun']}</td>" : "<td>{$tmpdata['kabupaten']}</td>";
			$table_data .= "<td>".number_format($tmpdata['nilai'],2,'.','')."</td>
				</tr>";

			$chart['kategori'][] = ($id > 0) ? $tmpdata['tahun'] : $tmpdata['kabupaten'];
			$chart['series'][0]['data'][] = number_format($tmpdata['nilai'],2,'.','');

				$row['id'] = $tmpdata['kodepemda'];	
				$row['tdata'] = '<li>{data row}</li>';
				$row['urai'] = 'Kab '.$tmpdata['kabupaten'];	
				$row['data_1'] = number_format($tmpdata['nilai'],2,'.','');
				$row['data_2'] = $tmpdata['test2']; 

				$row['data_1_satuan'] = $tmpdata['satuan']; 
				
				$row['kelompok_1'] = $legend[0]['urai'];
				$row['kelompok_2'] = $legend[1]['urai'];
				$row['pct_1'] = $row['data_1']; 
				$row['pct_2'] = $row['data_2'];

				$color_k = $color[$tmpdata['idwilayah']];
				
				// if ($row['pct_1'] == null) {
					// $row['color_1'] = array(225,225,225,0.8);	
					$row['color_1'] = array($color_k['r'],$color_k['g'],$color_k['b'],0.8);	
				// } else {
				// 	$a = $row['pct_1']/$data_range['max_nilai']*100; 
				// 	$row['color_1'] = array(220-$a,130-$a,220-$a,0.8);	
				// }
				$row['color_2'] = array(255,204,0,0.8);
				$row['stroke_1'] = array(0,0,0,0.2);	
				$row['stroke_2'] = array(255,204,0,0.2);				
				$row['wkt_1'] = $tmpdata['wkt_1'];	
				$row['wkt_2'] = $tmpdata['wkt_2'];			
				$data[] = $row;				
		}
		
		$table_data .= "</table>";
		$result['table_data'] = $table_data;
		$result['chart'] = $chart;
		$result['legend'] = $legend;
		$result['map'] = $data; 
		$result['data'] = $data_peta; 
		print json_encode($result,JSON_NUMERIC_CHECK);
	}	

	private function _getDataKabupatenDetail($id){
		$scr_idkelompok = $this->scr->filter($_POST['idkelompok']);
		$scr_tahun = $this->scr->filter($_POST['tahun']);
		$id = $this->db->escape_string($_POST['id']);

		$legend = Array();
		$res_klp = $this->db->query("SELECT k.*,i.nama_instansi AS sumber 
										FROM kelompok_kabupaten k 
										LEFT JOIN users u ON u.iduser = k.iduser
										LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
										WHERE idkelompok='".$scr_idkelompok."'");
		$data_klp = $this->db->fetchAssoc($res_klp);
		// $legend[] = array('urai'=> $data_klp['urai'].", tahun {$scr_tahun}", 'hexcolor' => ''); 

		$createLegend 	= $this->_createMapLegendForDetail($scr_idkelompok,$scr_tahun);
		$legend 		= $createLegend['legend'];
		$data_range 	= $createLegend['data_range'];
		$custom_legend 	= $createLegend['custom'];

		// load kabupaten geometry
		$data = Array();
		if ($id > 0) {
			$sql = "SELECT kel.*,kd.*,k.*,
					AsText(k.geom) as wkt_1,
					AsText(centroid(k.geom)) as wkt_2
					FROM kabupaten_gis k
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$scr_idkelompok}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$scr_idkelompok}'
					WHERE k.kodepemda = '".$id."'
					GROUP BY kd.tahun";
			$chart['type'] = 'line';
		} else if ($id == 0) {
			$sql = "SELECT kel.*,kd.*,k.*,
					ASTEXT(k.geom) AS wkt_1,
					ASTEXT(CENTROID(k.geom)) AS wkt_2
					FROM kabupaten_gis k					
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$scr_idkelompok}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$scr_idkelompok}' AND kd.tahun={$scr_tahun}
					";			
			$chart['type'] = 'column';
		}
		// die($sql);
		$res = $this->db->query($sql);

		$chart['judul'] 			= $data_klp['urai'];
		$chart['sumber'] 			= (!empty($data_klp['sumber'])) ? $data_klp['sumber'] : "Pusdalisbang (*)" ;
		$chart['satuan'] 			= $data_klp['satuan'];
		$chart['series'][0]['name'] = $data_klp['urai'];

		$data_peta['namapeta'] 		= $chart['judul']." th.".$scr_tahun;
		$data_peta['sumberpeta'] 	= "Sumber : ".$chart['sumber'];
			
		$table_data = "<table id='tbl_detail' class='table table-bordered table-striped detail_data'><tr><th>No</th>";
		$table_data .= ($id > 0) ? "<th>Tahun</th>" : "<th>Kabupaten</th>";
		$table_data .= "<th>{$data_klp['urai']} ({$data_klp['satuan']})</th></tr>";

		$i = 0;

		while($tmpdata = $this->db->fetchAssoc($res)){

			$i++;

			$table_data .= "<tr><td>{$i}</td>";
			$table_data .= ($id > 0) ? "<td>{$tmpdata['tahun']}</td>" : "<td>{$tmpdata['kabupaten']}</td>";
			$table_data .= "<td>".number_format($tmpdata['nilai'],2,'.','')."</td>
				</tr>";

			$chart['kategori'][] = ($id > 0) ? $tmpdata['tahun'] : $tmpdata['kabupaten'];
			$chart['series'][0]['data'][] = number_format($tmpdata['nilai'],2,'.','');

			$row['id'] = $tmpdata['kodepemda'];	
			$row['tdata'] = '<li>{data row}</li>';
			$row['urai'] = 'Kab '.$tmpdata['kabupaten'];	
			$row['data_1'] = number_format($tmpdata['nilai'],2,'.','');
			$row['data_2'] = $tmpdata['test2']; 

			$row['data_1_satuan'] = $tmpdata['satuan']; 
			
			$row['kelompok_1'] = $legend[0]['urai'];
			$row['kelompok_2'] = $legend[1]['urai'];
			$row['pct_1'] = $row['data_1']; 
			$row['pct_2'] = $row['data_2'];
			
			// coloring polygon
			if ($row['pct_1'] == null) {
				$row['color_1'] = array(225,225,225,0.8);	
			} else {
				if ($custom_legend) {
					$row['color_1'] = $this->_getColorPolygonForDetail($row['pct_1']);
				} else {
					$a = $row['pct_1']/$data_range['max_nilai']*100; 
					$row['color_1'] = array(220-$a,130-$a,220-$a,0.8);	
				}
			}

			$row['color_2'] = array(255,204,0,0.8);
			$row['stroke_1'] = array(0,0,0,0.2);	
			$row['stroke_2'] = array(255,204,0,0.2);				
			$row['wkt_1'] = $tmpdata['wkt_1'];	
			$row['wkt_2'] = $tmpdata['wkt_2'];			
			$data[] = $row;				
		}
		
		$table_data .= "</table>";
		$result['table_data'] = $table_data;
		$result['chart'] = $chart;
		$result['legend'] = $legend;
		$result['map'] = $data; 
		$result['data'] = $data_peta; 
		print json_encode($result,JSON_NUMERIC_CHECK);
	}	

	private function _createMapLegendForDetail($idkelompok,$tahun) {
		// $legend = $this->_createMapLegendForDetail($scr_idkelompok,$scr_tahun);
		// cek map_legend untuk custom legenda
		$sql_legend = "SELECT *
				FROM map_legend 
				WHERE idkelompok='{$idkelompok}' ORDER BY batas_atas DESC";
		$res_legend = $this->db->query($sql_legend);
		if ($this->db->numRows($res_legend) > 0) {

			$this->customColorLegend = $maxval = array();
			while($data_legend = $this->db->fetchAssoc($res_legend)) {
				$legend[] = array(
					'urai'		=> "{$data_legend['batas_bawah']} - {$data_legend['batas_atas']} {$data_legend['satuan']} </td><td>({$data_legend['label']})", 
					'hexcolor' 	=> $data_legend['warna']
				); 	
				$maxval[] = $data_legend['batas_atas'];
				$this->customColorLegend[] = array(
					'warna' => $data_legend['warna'],
					'max' 	=> $data_legend['batas_atas'],
					'min' 	=> $data_legend['batas_bawah']
					);
			}

			// jika tidak ada setelah untuk data kosong
			if (min($maxval) > 0) {
				$legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 
			}

			// nilai maximal dan minimal
			$sql_max_min = "SELECT max(batas_atas) as max_nilai,min(batas_bawah) as min_nilai 
					FROM map_legend 
					WHERE idkelompok='{$idkelompok}'";
			$data_range = $this->db->fetchAssoc($this->db->query($sql_max_min));
			// + limitasi jika kosong, set 100
			$data_range['max_nilai'] = ($data_range['max_nilai'] == 0) ? 100 : $data_range['max_nilai'] ;
			$iscustom = true;

		} else {
			// nilai maximal dan minimal
			$sql_max_min = "SELECT max(nilai) as max_nilai,min(nilai) as min_nilai 
					FROM kelompok_detail_kabupaten 
					WHERE idkelompok='{$idkelompok}' AND tahun='{$tahun}'";
			$data_range = $this->db->fetchAssoc($this->db->query($sql_max_min));
			// + limitasi jika kosong, set 100
			$data_range['max_nilai'] = ($data_range['max_nilai'] == 0) ? 100 : $data_range['max_nilai'] ;
			$range_nilai = $data_range['max_nilai'] - $data_range['min_nilai'];

			$num_legend = 5;

			// data legend
			for ($x_legend = 1; $x_legend <= $num_legend; $x_legend++) {
				$upper_value = ceil($data_range['max_nilai']-($x_legend-1)*$range_nilai/($num_legend));
				$lower_value = $upper_value - floor($data_range['max_nilai']/($num_legend));
				$xa = $upper_value/$data_range['max_nilai']*100;
				$legend[] = array('urai'=> "{$lower_value} - {$upper_value} {$data_klp['satuan']}", 'hexcolor' => '#'.dechex(220-$xa).dechex(130-$xa).dechex(220-$xa)); 	
			}
			$legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 
			$iscustom = false;
		}
		// die(print_r($this->customColorLegend,1));

		return array(
			'legend' 		=> $legend,
			'data_range' 	=> $data_range,
			'custom' 		=> $iscustom
			);
	}

	private function _getColorPolygonForDetail($nilai) {
		$warna = "";
		foreach ($this->customColorLegend as $clegend) {
			if ($nilai <= $clegend['max'] && $nilai >= $clegend['min']) {
				$warna = $clegend['warna'];
			}
		}
		return array(hexdec(substr($warna,1,2)),hexdec(substr($warna,3,2)),hexdec(substr($warna,5,2)),0.8);
	}
	
	private function _listSubKelompok_Kabupaten($idkelompok) {
		// list opsi sub/jenis urusan
		if ($idkelompok > 0) {
			$sqlsub = "SELECT * FROM kelompok_kabupaten 
					WHERE idparent=".$this->db->escape_string($idkelompok)." ORDER BY ordering ASC";

			$qsub = $this->db->query($sqlsub);
			while ($dataSUrusan = $this->db->fetchAssoc($qsub)) {
				$option .= "<option value='".$dataSUrusan['idkelompok']."'>{$dataSUrusan['urai']}</option>";
			}
		} else {
			$option = "";
		}
		return json_encode(array('options'=>$option)); 
	}

	private function _chartSource($id,$tahun_chart=array(),$single_series=false){
		/**
		 * load chart parameter
		 * id = id kelompok di tabel keompok
		 * tahun_chart = array tahun chart, eg (2015,2016)
		 */

		// id, idparent, urai, formula, satuan
		$sql 	= $this->db->query("SELECT *,i.nama_instansi as sumber FROM kelompok_kabupaten k 
			JOIN users u ON u.iduser = k.iduser
			JOIN instansi i ON i.idinstansi = u.idinstansi
			WHERE k.kabupaten_detailidkelompok=".$id);
		$rTabel = $this->db->fetchAssoc($sql);
		// print_r($rTabel);
		$judul 	= $rTabel['urai'];
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
		if (count($tahun_chart) == 1) {$chart_type = "column";} //<-- colum untuk single item / atau single year

		foreach ($tahun_chart as $tahun) {
			// $year .= "'".$y."',";
			$ye[] = $tahun;
		}
		
		/* cek
		 */ 		
		$series = array(); 
		// data series dari linechart
		$qTabel = $this->db->query("SELECT * FROM kelompok WHERE idparent=".$id." ORDER BY idkelompok ASC");
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
					$satuan = (empty($satuan)) ? $sumChild[$rTabel['idkelompok']]['satuan'] : $satuan ;
				}
				$data[] = number_format($nilai,2,'.','');//$nilai;//
			}

			// series data untuk chart
			$series[$rTabel['idkelompok']]['name'] = $rTabel['urai'];
			$series[$rTabel['idkelompok']]['data'] = $data;
		}
		else if ($this->db->numRows($qTabel) > 0) {
			/* 
			 * elemen dengan id tesebut adalah parent dari kelompok
			 * - load data  detail childrennya
			 */
			while ($rTabel = $this->db->fetchArray($qTabel)){
				$data = array(); 
				// satuan jika kosong
				$satuan = (empty($satuan)) ? $rTabel['satuan'] : $satuan ;

				//load detail pertahun-nya
				foreach($tahun_chart as $tahun) {
					$nilai = 0;
				  // $qVal = $this->db->query("SELECT * FROM detail 
				  // 			WHERE tahun=".$tahun." and idkelompok=".$rTabel['id']." ");
				  // if ($rVal = $this->db->fetchArray($qVal)) {
				  //   $nilai = number_format($rVal['nilai'],2);
				  // } 
					$sumChild = $this->recrusiveData($rTabel['idkelompok'],$rTabel['idkelompok'],$tahun);
				if ($sumChild[$rTabel['idkelompok']][$tahun] > 0) {
					$nilai = $sumChild[$rTabel['idkelompok']][$tahun];	
				}
				  $data[] = number_format($nilai,2,'.','');//$nilai;//
				}

				// series data untuk chart
				$series[$rTabel['idkelompok']]['name'] = $rTabel['urai'];
				$series[$rTabel['idkelompok']]['data'] = $data;
			}

		} else {
		// jika kelompok adalah yg terdetil / child with no child
			$data = array(); 
			$chart_type = "column";
			// satuan jika kosong
			$satuan = (empty($satuan)) ? $rTabel['satuan'] : $satuan ;

			//load detail pertahun-nya
			foreach($tahun_chart as $tahun) {
				$nilai = 0;
			  $qVal = $this->db->query("SELECT * FROM kelompok_detail 
							WHERE tahun=".$tahun." and idkelompok=".$rTabel['idkelompok']." ");
			  if ($rVal = $this->db->fetchArray($qVal)) {
				$nilai = number_format($rVal['nilai'],2,'.',''); //$rVal['nilai'];//
			  } 
			  $data[] = $nilai;    
			}

			// series data untuk chart
			$series[$rTabel['idkelompok']]['name'] = $rTabel['urai'];
			$series[$rTabel['idkelompok']]['data'] = $data;
		}

		// print_r($data);

		sort($series);
		// print_r($series);

		$return = array(
			'series' => $series,
			'judul' => $judul,
			'sumber' => $sumber,
			'satuan' => $satuan,
			'kategori' => $ye,
			'type' => $chart_type,
			'data' => $data,
		);

		return $return;
	}
	private function _chart_Kabupaten($id){
		/*
		 * CHART JSON OUTPUT
		 */
		$tahun = $_GET['tahun_chart'];
		$data = $this->_chartSource($id,$tahun);
		return json_encode($data,JSON_NUMERIC_CHECK); //print angka sebagai numeric
	}
	
}

?>
