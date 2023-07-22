<?php
Class SearchClass extends ModulClass{

	function _findUrusan() {
		$kw 			= $_GET['search'];
		$kelompok 		= $_GET['kelompok_urusan'];
		$jenis_urusan 	= $_GET['jenis_urusan'];

		// filter berdasarkan kelompok dan jenis urusannya
		if ($kelompok == 0) { //semua kelompok
			$sql = "SELECT k.idkelompok, k.urai, k.satuan, a.urai AS urai_parent_a, 
				b.urai AS urai_parent_b, c.urai AS urai_parent_c, d.urai AS urai_parent_d
				FROM kelompok k
				JOIN kelompok a ON a.idkelompok = k.idparent 
	            JOIN kelompok b ON b.idkelompok = a.idparent  
	            JOIN kelompok c ON c.idkelompok = b.idparent  
	            JOIN kelompok d ON d.idkelompok = c.idparent  
	            WHERE k.urai like '%".$kw."%'
				ORDER BY k.urai ASC ";
		} else if ($kelompok > 0 && $jenis_urusan == 0) { 
		// semua jenis dalam suatu kelompok
			$sql = "SELECT k.idkelompok, k.urai, k.satuan, a.urai AS urai_parent_a, 
				b.urai AS urai_parent_b, c.urai AS urai_parent_c, d.urai AS urai_parent_d 
				FROM kelompok k
				JOIN kelompok a ON a.idkelompok = k.idparent 
	            JOIN kelompok b ON b.idkelompok = a.idparent  
	            JOIN kelompok c ON c.idkelompok = b.idparent  
	            JOIN kelompok d ON d.idkelompok = c.idparent  
	            WHERE k.urai like '%".$kw."%'  AND (a.idkelompok= ".$kelompok." 
	            	OR b.idkelompok= ".$kelompok." 
	            	OR c.idkelompok= ".$kelompok." 
	            	OR d.idkelompok= ".$kelompok.")
				ORDER BY k.urai ASC ";
		} else if ($kelompok > 0 && $jenis_urusan > 0) { 
		// berdasarkan jenis tertentu dalam kelompok tertentu
			$sql = "SELECT k.idkelompok, k.urai, k.satuan, a.urai AS urai_parent_a, 
				b.urai AS urai_parent_b, c.urai AS urai_parent_c, d.urai AS urai_parent_d 
				FROM kelompok k
				JOIN kelompok a ON a.idkelompok = k.idparent 
	            JOIN kelompok b ON b.idkelompok = a.idparent  
	            JOIN kelompok c ON c.idkelompok = b.idparent  
	            JOIN kelompok d ON d.idkelompok = c.idparent  
	            WHERE k.urai like '%".$kw."%'  AND (a.idkelompok= ".$jenis_urusan." 
	            	OR b.idkelompok= ".$jenis_urusan." 
	            	OR c.idkelompok= ".$jenis_urusan." 
	            	OR d.idkelompok= ".$jenis_urusan.")
				ORDER BY k.urai ASC ";
		}
		
		$res = $this->db->query($sql);
		$nrw = $this->db->numRows($res); 
		$nrw = ($nrw > 0) ? $nrw : $nrw .= ', &nbsp; tidak ada data ditemukan..' ;

		$qData = $this->db->query("SELECT MIN(tahun) as min, MAX(tahun) as max FROM kelompok_detail_matrix");
		$tahunData = $this->db->fetchAssoc($qData);
		$tahunHeader = '';
		for ($tahun = $tahunData['min'] ;$tahun <= $tahunData['max']; $tahun++) {
			$tahunHeader .= '<th>'.$tahun.'</th>';
		}

		$json_data['numfound'] = $nrw;
		$json_data['lastcoloum'] = $tahunData['max']-$tahunData['min']+3; //for disabling ordering of datatable
		$json_data['content'] = '
		<div class="col col-md-12 text-info bg-success h3">
		Hasil Pencarian : '.$nrw.'<br/>
		</div><br>';

		// ada data ditemukan
		if ($nrw > 0) {
			$json_data['content'] .= '<table id="tb_serach_result" class="table table-bordered table-condensed">
			<thead>
			<tr><th>No</th><th>Kelompok Data</th>'.$tahunHeader.'<th></th></tr>
			</thead>
			<tbody>';
			$x = 1;
			while ($dataK = $this->db->fetchAssoc($res)) {

				$json_data['content'] .= '<tr><td>'.$x."</td><td>".$dataK['urai_parent_a']." &nbsp;<i class='fa fa-chevron-circle-right' class='ms-5'></i>&nbsp; ".$dataK['urai'].'</td>
				';

				for ($tahun = $tahunData['min'] ;$tahun <= $tahunData['max']; $tahun++) {
					$sql_val = "SELECT * FROM kelompok_detail_matrix 
			      				WHERE tahun=".$tahun." and idkelompok IN (SELECT idkelompok_matrix FROM konversi_kelompok WHERE idkelompok_sipd='".$dataK['idkelompok']."')";
					$qVal = $this->db->query($sql_val);
					$rVal = $this->db->fetchAssoc($qVal);
					// die($sql_val);

					// validate nilai
					$nilai = $rVal['nilai'];
					if ($nilai == 0) {
						$nilai = '-';
					} else if (preg_match('/^\d+\.\d+$/',$nilai)) {
						$nilai = number_format($rVal['nilai'],2,'.','').' '.$dataK['satuan'];
					} else {
						$nilai .= ' '.$dataK['satuan'];
					}

					$json_data['content'] .= '<td nowrap>'.$nilai.'</td>';
				}

				$json_data['content'] .= '
				<td><button class="btn tbn-flat btn-xs btn-info" onclick="openChart('.$dataK['idkelompok'].')" title="lihat chart"><i class="fa fa-fw fa-bar-chart"></i></button></div>
				</td></tr>';
				$x++;
			}	
		} else {

		}

		$json_data['content'] .= "</tbody></table>";
		die (json_encode($json_data));
	}

	function getJSON($id){
		// print_r($_GET);
		# tampilan depan
		//echo 'VOID';
		$jmode = $_GET['ajaxmode'];
		switch ($jmode) {
			case 'urusan':
				# code...
				return $this->_findUrusan();
			break;
	
			default:
				# code...
			break;
		}
	}

	function FrontList(){

		//pgscript
		$this->pgScript='
		<script src="{themepath}plugins/Highcharts-4.2.3/js/highcharts.js"></script>
		<script src="{themepath}plugins/Highcharts-4.2.3/js/modules/exporting.js"></script>
		<script type="text/javascript" src="{themepath}js/progis.js"></script>
		<script type="text/javascript" src="{themepath}js/search.js"></script>';

		// opt kelompok
		$opt_kelompok = "";
		$sql = $this->db->query("SELECT * FROM kelompok WHERE idparent=4"); //analisa
		while ($dataUrusan = $this->db->fetchAssoc($sql)) {
			$opt_kelompok .= "<option value='".$dataUrusan['idkelompok']."'>".$dataUrusan['urai']."</option>";
  		}

		// load search form
		$define = array(
			'keyword' => '',
			'opt_kelompok'	=> $opt_kelompok,
		);
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/search.html');
		$tplform->defineTag($define);	
		$content = $tplform->parse();	

		$this->menu = new MenuClass;
		$this->link = new LinkClass;
		$this->berita = new BeritaClass;
		$this->slider = new SliderClass;
		// $this->agenda = new AgendaClass;
		// $this->FrontDisplay();
		$this->user = new UserClass;
		// $this->agenda->FrontDisplay();
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'menufooter'	=> $this->menu->FrontDisplay('B'),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> 'Pencarian Data',
						 'pagescript'	=> $this->pgScript,
						 'pagecontent'	=> $content,
						 'sidenews'		=> $this->sidenews,		
						 'link'			=> $this->link->FrontDisplay(),	
						 'latestnews'	=> $this->berita->LatestNews(),	
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
}