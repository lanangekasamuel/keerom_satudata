<?php
Class GerbangmasClass extends ModulClass{

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
	private function __getDataKabupatenOf($kodepemda,$idkelompok){
		# fungsi untuk emndapatkan data maximal dari suatu kelompok data di sebuah wil-kabupaten
		$sql = "SELECT k.*,MAX(kd.nilai) AS nilai 
			FROM kelompok_kabupaten k 
			LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkelompok = k.idkelompok
			WHERE k.idkelompok='".$this->scr->filter($idkelompok)."' 
				AND kd.nilai <> 0 
				AND kd.idkabupaten = '".$this->scr->filter($kodepemda)."';";
		// die($sql);
		$data = $this->db->fetchAssoc($this->db->query($sql));
		$nilai = (substr_count($data['nilai'], ".") > 0) ? number_format($data['nilai'],2,',','.') : number_format($data['nilai'],0,',','.') ;
		return $nilai;//.' '.$data['satuan'];
	} 	
	private function __getDataWilayahOf($idwilayah,$idkelompok){
		# fungsi untuk emndapatkan data maximal dari suatu kelompok data di sebuah wil-kabupaten
		$sql = "SELECT *,SUM(k.nilai) AS nilai
			FROM kelompok_detail_kabupaten k
			WHERE k.idkelompok='".$this->scr->filter($idkelompok)."' 
			AND k.`tahun` = (SELECT MAX(tahun) FROM kelompok_detail_kabupaten WHERE idkelompok = '".$this->scr->filter($idkelompok)."' AND nilai <> 0)
			AND k.idkabupaten IN (SELECT kodepemda FROM kabupaten_gis WHERE idwilayah = '".$this->scr->filter($idwilayah)."')
			;";
		// die($sql);
		$data = $this->db->fetchAssoc($this->db->query($sql));
		$nilai = (substr_count($data['nilai'], ".") > 0) ? number_format($data['nilai'],2,',','.') : number_format($data['nilai'],0,',','.') ;
		return $nilai;//.' '.$data['satuan'];
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
			<script type="text/javascript" src="{themepath}/js/gerbangmas.js"></script>
		';	

		// ramdom load id
		$sql_rand = "SELECT idkelompok FROM kelompok_kabupaten 
					WHERE idparent <> 0 
						AND idkelompok > 601 AND idkelompok < 615
						AND idkelompok NOT IN (SELECT idparent FROM kelompok_kabupaten)
					ORDER BY RAND() LIMIT 1;";
		$res_rand = $this->db->query($sql_rand);
		$data_rand = $this->db->fetchAssoc($res_rand);

		// Indikator kelompok Data {masukkan ke map}
		$sql_min = "SELECT min(tahun) as min FROM kelompok_detail_kabupaten LIMIT 1";
		$res_min = $this->db->query($sql_min);
		$data_min = $this->db->fetchAssoc($res_min);
		$tahun_awal = $data_min['min'];
		$tahun_akhir = date("Y");

		$opsi_tahun = "Tahun : <select id='tahun' class='map-control'>";
		for ($x_tahun = $tahun_awal; $x_tahun <= $tahun_akhir; $x_tahun++) {
			$opsi_tahun .= "<option>{$x_tahun}</option>";
		}
		$opsi_tahun .= "</select>";

		$opsi_kabupaten .= "Lokasi : 
			<select id='jenis_analisa' class='map-control' onChange='loadKabupatenMap_detail(this.value);'>
			<option value='0'>-- Semua Lokasi Gerbangmas --</option>
		";

		$this->pgScript .= "
		<script>
		$(document).ready(function(){
			loadKelompok('{$data_rand['idkelompok']}');
			// loadKabupatenMap_detail('0');
		});
		</script>
		";
		$sql_kabgm = "SELECT k.kodepemda,k.kabupaten 
					FROM kabupaten_gis k
					WHERE gerbangmas = '1'
					ORDER BY k.kabupaten
					";
		$qKab = $this->db->query($sql_kabgm);
		while ($rKab = $this->db->fetchAssoc($qKab)) {
			$opsi_kabupaten .= "<option value=\"{$rKab['kodepemda']}\">{$rKab['kabupaten']}</option>";
		}
		$opsi_kabupaten .= "</select>";
						
		$menu_indikator = "";
		// load kelompok-indikator
		// $sql_kelompok = "SELECT * FROM kelompok_kabupaten WHERE idparent=0;";
		// $res_kelompok = $this->db->query($sql_kelompok);
		// while ($row_kelompok = $this->db->fetchAssoc($res_kelompok)) {
		// 	//no
		// }
		$this->recursiveMenu($menu_indikator,0);

		$data_wilayah = "<table class='table table-bordered table-stripped detail_data' id='table_gerbangmas'>
			<tr>
			<th>No</th>
			<th>Wilayah / Kabupaten</th>
			<th>Pusat Pelayanan</th>
			<th>Akses</th>
			<th>Penduduk (jiwa)</th>
			<th>IPM</th>
			<th>AHH</th>
			<th>AMH (%)</th>
			<th>RLS (tahun)</th>
			</tr>
		";

		$roman_number = array(1=>"I",2=>"II",3=>"III",4=>"IV",5=>"V");
		$no_wil = 1;
		$sql_wil = "SELECT * FROM wilayah ORDER BY wilayah ASC";
		$qry_wil = $this->db->query($sql_wil);
		while ($row_wil = $this->db->fetchAssoc($qry_wil)) {
			$jml_pddk_wil = $this->__getDataWilayahOf($row_wil['idwilayah'],702);
			$data_wilayah .= "<tr class='row-wilayah'><td>{$roman_number[$no_wil]}</td><td>{$row_wil['wilayah']}</td>
			<td></td>
			<td class='align-center'>{$row_wil['akses']}</td>
			<td class='align-right'>{$jml_pddk_wil}</td>
			<td colspan=4></td>
			</tr>";
			$no_wil++;
			// kabupaten
			$no_kab = 1;
			$sql_kab = "SELECT * FROM kabupaten_gis WHERE idwilayah = '{$row_wil['idwilayah']}' AND gerbangmas = 1";
			$qry_kab = $this->db->query($sql_kab);
			while ($row_kab = $this->db->fetchAssoc($qry_kab)) {
				$jml_pddk = $this->__getDataKabupatenOf($row_kab['kodepemda'],702);
				$nilai_ipm = $this->__getDataKabupatenOf($row_kab['kodepemda'],602);
				$nilai_ahh = $this->__getDataKabupatenOf($row_kab['kodepemda'],802);
				$nilai_amh = $this->__getDataKabupatenOf($row_kab['kodepemda'],613);
				$nilai_rls = $this->__getDataKabupatenOf($row_kab['kodepemda'],612);
				$data_wilayah .= "<tr>
				<td>{$no_kab}</td><td>{$row_kab['kabupaten']}</td>
				<td>{$row_kab['pusat_pelayanan']}</td>
				<td class='align-center'>{$row_kab['akses']}</td>
				<td class='align-right'>{$jml_pddk}</td>
				<td class='align-right'>{$nilai_ipm}</td>
				<td class='align-right'>{$nilai_ahh}</td>
				<td class='align-right'>{$nilai_amh}</td>
				<td class='align-right'>{$nilai_rls}</td>
				</tr>";
				$no_kab++;
			}
		}
		$data_wilayah .= "</table>";

		$this->pgTitle = 'GERBANGMAS';

		$judul_peta = 'PETA TEMATIK PROGRAM GERBANGMAS';
		$content .= '

		<div class="col-md-6" id="">
			<div class="box box-primary bg-cendrawasih" id="gerbangmas_fokus">
			   <div class="box-header with-border">
				  <h3 class="box-title">FOKUS PILOT PROJECT GERBANGMAS</h3>
				 <!-- /.box-header -->
				</div>
				<div class="box-body">
					<!-- PAGE CONTENT WRAPPER -->
					<div class="page-content-wrap  list-gerbangmas">  
				  	<ul>
				  	<li>BERDAYA EKOMAS (PEMBERDAYAAN EKONOMI MASYARAKAT):</li>
				  		<ul>
				  			<li>Integrasi tanam Petik Olah dan Jual</li>
				  			<li>Pengembangan Komoditas berbasis 5 wilayah pembangunan</li>
				  			<li>Pengembangan ekonomi dan kelembagaan kampung</li>
				  		</ul>
				  	<li>GEMAS (GENERASI EMAS PAPUA):</li>
				  		<ul>
				  			<li>Seribu Hari Pertama Kehidupan (1000 HPK)</li>
				  			<li>Tuntas Buta Aksara</li>
				  			<li>Wajib Belajar 9 tahun</li>
				  		</ul>
				  	</ul>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-6" id="">
			  <div class="box box-primary bg-cendrawasih-mirror" id="gerbangmas_program">
			   <div class="box-header with-border">
				  <h3 class="box-title">GERAKAN MASIF DAN TERINTEGRASI</h3>
				 <!-- /.box-header -->
				</div>
				<div class="box-body">
					<!-- PAGE CONTENT WRAPPER -->
					<div class="page-content-wrap list-gerbangmas"> 
					<ul>
				  	<li>REFORMASI BIROKRASI:</li>
				  		<ul>
				  			<li>Restrukturisasi</li>
				  			<li>Refungsionalisasi</li>
				  			<li>Revitaslisasi</li>
				  		</ul>
				  	<li>GEMAS (GENERASI EMAS PAPUA):</li>
				  		<ul>
				  			<li>Jaminan Seribu Hari Pertama Kehidupan (1000 HPK)</li>
				  			<li>Tuntas Buta Aksara</li>
				  			<li>Wajib Belajar 9 tahun</li>
				  			<li>Prestasi Olah rage, seni dan budaya</li>
				  			<li>Pengembangan daya saing Papua</li>
				  		</ul>				  	
				  	<li>PEMBERDAYAAN EKONOMI:</li>
				  		<ul>
				  			<li>Prospek (Prog Strategis Pemb. Ek. &Kelembagaan Kampung)</li>
				  			<li>Pengwilayahan Komoditas</li>
				  			<li>Tanam ,petik, olah, jual</li>
				  		</ul>				  	
				  	<li>INFRASTRUKTUR & PRASARANA DASAR:</li>
				  		<ul>
				  			<li>Transportasi (Darat, Laut, Udara,ASDP)</li>
				  			<li>Penyediaan Energi Listrik</li>
				  			<li>Perumahan Layak Huni</li>
				  			<li>Penyediaan Air Bersih</li>
				  		</ul>
				  	</ul>	 
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-12" id="">
			<div class="box box-primary bg-cendrawasih-mirror" id="gerbangmas_wilayah">
			   <div class="box-header with-border">
			   <!--PEMBAGIAN WILAYAH GERBANGMAS-->
				  <h3 class="box-title">KABUPATEN PILOT PROJECT PEMBANGUNAN GERBANGMAS HASRAT PAPUA</h3>
				  <div></div>
				  <div class="text-notes">untuk data lainnya beserta lokasi penyeberannya lebih rinci bisa lihat di <a href="#content_peta">peta <i class="fa fa-map-marker"></i></a></div>
				 <!-- /.box-header -->
				</div>
				<div class="box-body">
					<!-- PAGE CONTENT WRAPPER -->
					<div class="page-content-wrap ">  
				  	<div>Tabel. wilayah dan kabupaten gerbangmas</div>
					'.$data_wilayah.'
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-12" id="content_peta">
			<div class="box box-primary" id="">
			   	<div class="box-header with-border">
				  	<h3 class="box-title">'.$judul_peta.'</h3>
					<div class="pull-right box-tools">
	                  <h5><a href="javascript:startIntro_map(\''.$this->pgTitle.'\');" id="btn_map_tips" class="text-success" onclick=""><i class="fa fa-question-circle"></i>&nbsp;Tips</a></h5>
	                </div>
					<div>'.$pilihan_peta.'</div>
	            </div>
				<!-- /.box-header  no-padding-side -->
				<div class="box-body">
					<!-- PAGE CONTENT WRAPPER -->
					<div class="page-content-wrap " id="map_content">    

						<div id="over_layer">

							<div id="judul_peta" class="judul-peta">
								<div class="dropdown" style="position:relative">
									<a href="#" class="btn btn-info dropdown-toggle" data-toggle="dropdown"><span class="judul">{Kelompok/Indikator}</span> <span class="caret"></span></a>
									<input id="indikator_kelompok" hidden>
									<ul class="dropdown-menu">
										'.$menu_indikator.'
									</ul>
									'.$opsi_tahun.'
									'.$opsi_kabupaten.'
								</div>
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

							<div class="papua_map fullscreen" id="papua_map"></div>
							<div id="popup" class="ol-popup">
							  	<div id="popup-content"></div>
							</div>
						</div>
					<!-- PAGE CONTENT WRAPPER --> 
					</div>
				</div>
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
						 'pagetitle'	=> $this->pgTitle,
						 'pagecontent'	=> $content,
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
			case 'kabupaten_detail':
				return $this->_getDataKabupatenDetail($id);
			break;			
			case 'gerbangmas':
				return $this->_getDataGerbangmas($id);
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
			$legend[] = array('urai'=> "{$lower_value} - {$upper_value} {$data_klp['satuan']}", 'hexcolor' => '#'.dechex(220-$xa).dechex(130-$xa).dechex(220-$xa)); 	
		}
		$legend[] = array('urai'=> "data belum tersedia", 'hexcolor' => '#'.dechex(225).dechex(225).dechex(225)); 	

		// load kabupaten geometry
		$data = Array();
		if ($id > 0) {
			$sql = "SELECT kel.*,kd.*,k.*,
			        AsText(k.geom) as wkt_1,
					AsText(centroid(k.geom)) as wkt_2
					FROM kabupaten_gis k
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$scr_idkelompok}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$scr_idkelompok}'
					WHERE k.kodepemda = '".$id."' AND k.gerbangmas = '1'
					GROUP BY kd.tahun";
			$chart['type'] = 'line';
		} else if ($id == 0) {
			$sql = "SELECT kel.*,kd.*,k.*,
			        ASTEXT(k.geom) AS wkt_1,
					ASTEXT(CENTROID(k.geom)) AS wkt_2
					FROM kabupaten_gis k					
					LEFT JOIN kelompok_kabupaten kel ON kel.idkelompok='{$scr_idkelompok}'
					LEFT JOIN kelompok_detail_kabupaten kd ON kd.idkabupaten=k.kodepemda AND kd.idkelompok = '{$scr_idkelompok}' AND kd.tahun={$scr_tahun}
					WHERE k.gerbangmas = '1'
					";			
			// $sql = "SELECT k_gis.*,
			// 			GROUP_CONCAT(ASTEXT(geom)) AS wkt_1,
			// 			GROUP_CONCAT(ASTEXT(CENTROID(geom))) AS wkt_2
			// 		FROM kabupaten_gis k_gis
			// 		WHERE wilayah IN (SELECT DISTINCT wilayah FROM kabupaten_gis)
			// 		GROUP BY wilayah
			// 		";
			$chart['type'] = 'column';
		}
		// die($sql);
		$res = $this->db->query($sql);

		$chart['judul'] = $data_klp['urai'];
		$chart['sumber'] = (!empty($data_klp['sumber'])) ? $data_klp['sumber'] : "Pusdalisbang (*)" ;
		$chart['satuan'] = $data_klp['satuan'];
		$chart['series'][0]['name'] = $data_klp['urai'];
			
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
				// $row['data_2'] = $tmpdata['test2']; 

				$row['data_1_satuan'] = $tmpdata['satuan']; 
				
				$row['kelompok_1'] = $legend[0]['urai'];
				// $row['kelompok_2'] = $legend[1]['urai'];
				$row['pct_1'] = $row['data_1']; 
				// $row['pct_2'] = $row['data_2'];
				
				if ($row['pct_1'] == null) {
					$row['color_1'] = array(225,225,225,0.8);	
				} else {
					$a = $row['pct_1']/$data_range['max_nilai']*100; 
					$row['color_1'] = array(220-$a,130-$a,220-$a,0.8);	
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
		print json_encode($result,JSON_NUMERIC_CHECK);
	}		
}

?>
