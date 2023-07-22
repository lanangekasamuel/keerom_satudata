<?php
Class AdminClass extends ModulClass
{
	function __construct($loaded)
	{
		$this->loadedclass = $loaded; 
		parent::__construct();
		$this->init();
	}

	function init(){
		if($this->auth->isAuth() <> 1) {
			if(!$_POST['submit']){
				// build login form
				$this->template->init(THEME.'/login.html');
				$this->template-> defineTag(['themepath' => THEME_URL]);
				$this->template-> printTpl();
			} else {
				// cek login
				$this->auth->login($_POST['user'],$_POST['pass']);
				header("location:".ROOT_URL."giadmin/beranda");
			} //$post submit

		} else {
			//logedin					
			// hak akses ke halaman di tambahkan ke kondisi
			$datausr = $this->auth->getDetail();
			$sql = "SELECT *
				FROM adminmenu a
				join hakakses h on (a.idadminmenu = h.idadminmenu)
				where idgroup = {$this->db->ci3db->escape($datausr['idgroup'])}
				ORDER BY `ord`";
			$res = $this->db->query($sql);
			while($data = $this->db->fetchArray($res)){
				$aksesMenu[] = $data['link'];
			}
			$aksesMenu = array_flip($aksesMenu);
			$pre_load = array();

			if(array_key_exists($_GET['content'], $this->loadedclass)){
				// cek content dari adminmenu apakah ada di table
				$sql = "SELECT link FROM adminmenu WHERE link={$this->db->ci3db->escape($_GET['content'])}";
				$res = $this->db->query($sql);
				$dataAdminMenu = $this->db->fetchAssoc($res);
				
				// admincontent = berita : BeritaClass();
				// batasi akses sesuai dengan hakakses
				if (array_key_exists($dataAdminMenu['link'],$aksesMenu) || $dataAdminMenu['link'] == NULL ) {
					// user memiliki akses ke menu yang bersangkutan || jika menu tidak ada di tabel
					$admContent = new $this->loadedclass[$this->scr->filter($_GET['content'])];
					$admContent->Init();
					$admContent->theme = THEME.'/adminbody.html'; 
				} else {
					//dashboard management
					$admContent->theme = THEME.'/dashboard.html'; 	
				}
			} else {
				switch($_GET['content']) {
					case 'logout':						
						$this->logout();
					break;
					default :
						$this->dashboardContent();

						if ($this->userAkses == 'admin' || $this->userAkses == 'operator' || $this->userAkses == 'gubernur') {
							$theme_file = "dashboard.html";
							$pre_load['opsi_filter_instansi'] = $this->opsi_filter_instansi;
						} else if ($this->userAkses == 'instansi') {
							$theme_file = "dashboard_instansi.html";
						}

						$pre_load['entry_progress_instansi'] = $this->entry_progress_instansi;
						$pre_load['entry_progress'] = $this->entry_progress;
						$pre_load['user'] = ucfirst($datausr['username']);

						$admContent->pgScript = "
						<script src='{themepath}js/dashboard.js'></script>
						<script src='{themepath}plugins/knob/jquery.knob.js'></script>
						<link rel='stylesheet' href='{themepath}plugins/select2/select2.min.css'>
						<script src='{themepath}plugins/select2/select2.full.min.js'></script>
						<script>
							$(function() {
							    $('.dial').knob();
							});
						</script>
						";

						$admContent->theme = THEME.'/'.$theme_file; 	
					break;
				}	
			}

			$tplbody =  new TemplateClass;
			$tplbody->init($admContent->theme);		
			$tplbody->defineTag(array_merge($pre_load, [
				'pgtitle'	=> $admContent->title,
				'content' => $admContent->content,
			]));

			$datausr = $this->auth->getDetail();
			$this->user = new UserClass;

			$modulMode = strtolower($this->scr->filter($_GET['content'])."_".$admContent->subMode);
			
			$define = array (			
							 'sitetitle'	=> SITE_TITLE, 						 
							 'pgtitle'		=> $admContent->title,
							 'account_menu'	=> $this->user->AccountMenu(),
							 'menufooter'   => $this->module_menu_class->FrontDisplay('B'),
							 'rootdir'		=> ROOT_URL,
							 'home'			=> ROOT_URL,
							 'error_tag'	=> ERROR_TAG,
							 'modulmode'	=> $modulMode,
							 
							 'user'			=> $datausr['username'],

							 'menu'			=> $this->getMenu(),					 
							 'body'			=> $tplbody->parse(),						
							 'metaexpire'	=> "<meta http-equiv='refresh' content='".$this->auth->getExpire().";URL='".$this->cnf->ROOTDIR."giadmin/logout'>",
							 'pagescript'	=> $admContent->pgScript,
							 'themepath'   	=> THEME_URL
							);       					
			$this->template->init(THEME.'/adminpanel.html');
			$this->template-> defineTag($define);
			$this->template-> printTpl();
		} //isauth 

		// $this;dump(get_defined_vars());die();
	}


	function getMenu()
	{
		$content;
    $this->_recrusiveAdminMenu($content);
		return $content;
	}

	private function _recrusiveAdminMenu( &$sresult = "", $parent = 0, $level = 0){
		//  output html menu untuk masing2 template akan berbeda

		$datausr = $this->auth->getDetail();
		$sql = "SELECT *
			FROM adminmenu a
			join hakakses h on (a.idadminmenu = h.idadminmenu)
			where h.idgroup = {$this->db->ci3db->escape($datausr['idgroup'])}
			AND parent = {$this->db->ci3db->escape($parent)}
			ORDER BY a.ord,a.parent";

		$dataSource = $this->db->query($sql);

			if($this->db->numRows($dataSource) > 0){
				$sresult .= ($level > 0) ? "<ul class='treeview-menu'>" : "";
				while($data = $this->db->fetchArray($dataSource)){

					// cek child menu
					$sql_child 	= "SELECT count(idadminmenu) as nummenu FROM adminmenu WHERE parent = {$this->db->ci3db->escape($data['idadminmenu'])}";

					// die($sql_child);
					$data_child = $this->db->query($sql_child);
					$rec_child 	= $this->db->fetchAssoc($data_child);

					// select menu. drop down or not
					if ($rec_child['nummenu'] > 0) {
						$sresult .="<li class='treeview'>
				          <a href='".ROOT_URL.'giadmin/'.$data['link']."'>
				            <i class='fa {$data['icon']}'></i>
				            <span>{$data['menu']}</span>
				            <i class='fa fa-angle-left pull-right'></i>
				          </a>
				          ";
						$this->_recrusiveAdminMenu($sresult,$data['idadminmenu'],$level+1);
						$sresult .="</li>";
					} else {
						$sresult .= "<li>
						<a href='".ROOT_URL.'giadmin/'.$data['link']."'>
             	 		<i class='fa ".$data['icon']." fa-fw'></i>&nbsp;<span>".$data['menu']."</span></a>
            	 		</li>";						
					}
				}
				$sresult .= ($level > 0)?"</ul>":"";
			}
	}

	function logout(){
		$this->auth->logout();
		header("location:".ROOT_URL."giadmin/beranda");
	}

	function cekAkses(){
		/* AKSES GROUP
		 * uraikan berdasarkan akses, 1:admin, 2:operator 3:skpd, 4:instansi_vertikal\
		 */
		$datausr = $this->auth->getDetail();
		$Qgroup = $this->db->query("SELECT * FROM `group` WHERE idgroup={$this->db->ci3db->escape($datausr['idgroup'])}");
		$dataGroup = $this->db->fetchAssoc($Qgroup);
		// print_r($datausr);
		if ($datausr['idgroup'] == 1) {
			$this->userAkses = 'admin';
		} else if ($datausr['idgroup'] == 9) {
			$this->userAkses = 'operator';
		} else if ($datausr['idgroup'] == 2) {
			$this->userAkses = 'instansi';
			$sqlInstansi = "SELECT *
				FROM instansi AS i
				LEFT JOIN users AS u on (u.idinstansi = i.idinstansi)
				WHERE u.iduser={$this->db->ci3db->escape($datausr['iduser'])}";
			$qInstansi = $this->db->query($sqlInstansi);
			$this->activeInstansi = $this->db->fetchAssoc($qInstansi);
		} else if ($datausr['idgroup'] == 3) {
			$this->userAkses = 'bidang';
			// $this->activeBidang = $this->db->fetchAssoc($qInstansi);
		} else if ($datausr['idgroup'] == 10) {
			$this->userAkses = 'gubernur';
			// $this->activeBidang = $this->db->fetchAssoc($qInstansi);
		}
	}

	private function dashboardContent(){
		// load dashboard content	
		$this->cekAkses();

		if ($this->userAkses == 'admin' || $this->userAkses == 'operator' || $this->userAkses == 'gubernur') {

			$sql_detail = "SELECT
				COUNT(idkelompok) as nmx
				FROM kelompok_matrix
				WHERE (formula = '' OR formula IS NULL)
				AND idkelompok NOT IN (SELECT idparent FROM kelompok_matrix)";

			$res_detail = $this->db->query($sql_detail);
			$jumlahData = $this->db->fetchAssoc($res_detail);

			// batasi pilihan tahun sesui keberadaan data
			$sql_tahun = "SELECT MIN(tahun) as min, MAX(tahun) as max FROM kelompok_detail_matrix";
			$res_tahun = $this->db->query($sql_tahun);
			$tahunData = array_map('intval', $this->db->fetchAssoc($res_tahun));

			/* [anovedit] kalau tahun kosong, min|max pakai tahun sekarang */
			$y = (int) date('Y');
			if (empty($tahunData['min'])) $tahunData['min'] = $y;
			if (empty($tahunData['max'])) $tahunData['max'] = $y;

			$this->entry_progress;
			// total progress oengisian data untuk seluruhnya
			for ($year = $tahunData['min'] ; $year <= $tahunData['max'];$year++) {
				$sql_kelompok = "SELECT
					COUNT(idkelompok) as nmx
					FROM kelompok_detail_matrix
					WHERE tahun={$year}";
				$res_kelompok = $this->db->query($sql_kelompok);
				$jumlahDataY = $this->db->fetchAssoc($res_kelompok);

				$width = $jumlahDataY['nmx']/$jumlahData['nmx']*100; // progress bar width

				$this->entry_progress .= '
				<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
				    <div class="info-box bg-aqua">
				    <span class="info-box-icon">

				    <!--<i class="fa fa-bookmark-o"></i>-->

				    <input type="text" class="dial" data-min="0" data-max="100" class="knob" value="'.number_format($width,2,'.','').'" data-skin="tron" data-thickness="0.4" data-width="80" data-height="80" data-fgcolor="#00c0ef" data-bgcolor="#ff8080" data-readonly="true" readonly="readonly" >

				    </span>
				    <div class="info-box-content">
				        <span class="info-box-text">Entry Progress</span>
				        <span class="info-box-number">'.$year.'</span>
					    <div class="progress">
							<div class="progress-bar" style="width: '.$width.'%"></div>
						</div>
						<span class="progress-description">
							  '.$jumlahDataY['nmx'].' /'.$jumlahData['nmx'].' indikator</span>
					</div>
					<!-- /.info-box-content -->
					</div>
					<!-- /.info-box -->
				</div>

				';
			}

			$list_instansi = array();
			$this->entry_progress_instansi;
			// total pengisian data untuk instansi
			$sql_instansi = "SELECT * FROM instansi ORDER BY nama_instansi LIMIT 0,100";
			$res_instansi = $this->db->query($sql_instansi);
			while ($recInstansi = $this->db->fetchAssoc($res_instansi)) {
				$list_instansi[$recInstansi['idinstansi']] = $recInstansi['nama_instansi'];
				$jData_sql = "SELECT
					COUNT(idkelompok) as nmx,
					GROUP_CONCAT(idkelompok) AS kel_indikator
					FROM kelompok_matrix
					WHERE (formula = '' OR formula IS NULL)
					AND pk_instansi = {$recInstansi['idinstansi']}
					AND idkelompok NOT IN (SELECT idparent FROM kelompok_matrix)";
				$QjumlahData = $this->db->query($jData_sql);
				$jumlahData = $this->db->fetchAssoc($QjumlahData);

				// instansi yg tidak memiliki indikator
				if ($jumlahData['nmx'] > 0 ) { 

					$inner_content = "";
					$progress_intern = array();
					for ($year = $tahunData['min'] ; $year <= $tahunData['max'];$year++) {
						$yData_sql = "SELECT
							COUNT(idkelompok) as nmx
							FROM kelompok_detail_matrix
							WHERE tahun={$year}
							AND nilai IS NOT NULL
							AND idkelompok IN ({$jumlahData['kel_indikator']})";

						$QjumlahY = $this->db->query($yData_sql);
						$jumlahDataY = $this->db->fetchAssoc($QjumlahY);
						$progress_intern[] = $width = $jumlahDataY['nmx']/$jumlahData['nmx']*100;
						$updatedata_link = ($this->userAkses == 'gubernur') ? "" : "href='".ROOT_URL."giadmin/progis?instansi=".$recInstansi['idinstansi']."&tahun=".$year."'" ;
						$title = ($this->userAkses == 'gubernur') 
							? "progress data {$recInstansi['nama_instansi']} tahun {$year}" 
							: "klik untuk mengupdate data {$recInstansi['nama_instansi']} tahun {$year}" ;
						$inner_content .= "
							<div class='col col-md-2 col-sm-4 col-xs-6 progress-instansi'>
								<a {$updatedata_link} data-toggle='tooltip' title='{$title}'>
								<div class='row'>
									<div class='col col-xs-6'>{$year}</div>
									<div class='col col-xs-6'> {$jumlahDataY['nmx']}/{$jumlahData['nmx']}</div>
								</div>
								<div class='progress instansi'>
								    <div class='progress-bar progress-bar-green' role='progressbar' aria-valuenow='{$width}' aria-valuemin='0' aria-valuemax='100' style='width: {$width}%'>
								        <span class='sr-only'>{$width}% Complete (success)</span>
								    </div>
								</div>
								</a>
							</div>";
					}

					$total_progress = array_sum($progress_intern)/count($progress_intern);
					$updateinstansi_link = ($this->userAkses == 'gubernur') 
						? "" 
						: "href='".ROOT_URL."giadmin/progis?instansi=".$recInstansi['idinstansi']."' data-toggle='tooltip' title='klik untuk mengupdate data {$recInstansi['nama_instansi']}'" ;
					$this->entry_progress_instansi .= "
					<div class='box box-default instansi-progress' data-progress-entry=\"{$total_progress}\" data-jumlah-indikator=\"{$jumlahData['nmx']}\" data-id=\"{$recInstansi['idinstansi']}\" id=\"instansi-{$recInstansi['idinstansi']}\">
					<div class='box-body'>
					<h4><a {$updateinstansi_link}>
					<i class='fa fa-user'></i> &nbsp;{$recInstansi['nama_instansi']}
					</a></h4>
					<div class='row'>
					".$inner_content;

				} else {
					$this->entry_progress_instansi .= "
						<div class='box box-warning instansi-progress' data-progress-entry=\"-1\" data-jumlah-indikator=\"-1\" data-id=\"{$recInstansi['idinstansi']}\"  id=\"instansi-{$recInstansi['idinstansi']}\">
						<div class='box-body'>
						<h4>
						<i class='fa fa-user'></i> &nbsp;{$recInstansi['nama_instansi']}
						</h4>
						<div class='row'>
						";

					$this->entry_progress_instansi .= ($this->userAkses == 'gubernur') 
						? "<div class='col col-md-12 text-gray'>
						<h5>skpd/instansi ini belum memiliki indikator untuk di update nilainya. 
						</h5>
						</div>"
						: "<div class='col col-md-12 text-gray'>
						<span class='pull-right'>
						<a href='".ROOT_URL."giadmin/kelompok/matrik.htm?instansi=".$recInstansi['idinstansi']."'><button class='btn btn-flat btn-info'><i class='fa fa-plus'></i> tambahkan indikator</button></a>
						</span>
						<h5>skpd/instansi ini belum memiliki indikator untuk di update nilainya,<br>
						untuk menambahkan indikator klik tombol 'tambahkan indikator'. 
						</h5>
						</div>";
				}

				$this->entry_progress_instansi .= "</div></div></div>";
			}

			//filter instansi
			$this->opsi_filter_instansi = "";
			foreach ($list_instansi as $id => $nama) {
				$this->opsi_filter_instansi .= "<option value=\"{$id}\">{$nama}</option>";
			}

		} else if ($this->userAkses = 'instansi') {
			// instansi dashboard
			
			$scr_idinstansi = $this->scr->filter($this->activeInstansi['idinstansi']);

			/* [anovedit][workaround][users_table_ignored] */
			$sql_detail = "SELECT
				COUNT(idkelompok) AS nmx,
				GROUP_CONCAT(idkelompok) AS kel_indikator 
				FROM kelompok_matrix
				WHERE pk_instansi = {$scr_idinstansi}
				AND idkelompok NOT IN (SELECT idparent FROM kelompok_matrix)";

			$res_detail = $this->db->query($sql_detail);
			$jumlahData = $this->db->fetchAssoc($res_detail);

			// instansi yg tidak memiliki indikator
			if ($jumlahData['nmx'] > 0 ) {

				// batasi pilihan tahun sesui keberadaan data
				$sql_tahun = "SELECT
					MIN(tahun) AS `min`,
					MAX(tahun) AS `max`
					FROM kelompok_detail_matrix
					WHERE idkelompok IN (
						SELECT idkelompok
						FROM kelompok_matrix
						WHERE pk_instansi = {$scr_idinstansi}
					)";

				$res_tahun = $this->db->query($sql_tahun);
				$tahunData = array_map('intval', $this->db->fetchAssoc($res_tahun));

				/* [anovedit] kalau tahun kosong, min|max pakai tahun sekarang */
				$y = (int) date('Y');
				if (empty($tahunData['min'])) $tahunData['min'] = $y;
				if (empty($tahunData['max'])) $tahunData['max'] = $y;

				$this->entry_progress;
				// total progress oengisian data untuk seluruhnya
				for ($year = $tahunData['min'] ; $year <= $tahunData['max'];$year++) {
					$sql_kelompok = "SELECT
						COUNT(idkelompok) as nmx
						FROM kelompok_detail_matrix
						WHERE tahun={$year}
						AND nilai IS NOT NULL
						AND idkelompok IN ({$jumlahData['kel_indikator']})";

					$res_kelompok = $this->db->query($sql_kelompok);
					$jumlahDataY = $this->db->fetchAssoc($res_kelompok);

					$width = $jumlahDataY['nmx']/$jumlahData['nmx']*100; // progress bar width

					$this->entry_progress .= '
					<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
						<a href="'.ROOT_URL.'giadmin/progis?tahun='.$year.'" data-toggle="tooltip" title="klik untuk mengupdate data tahun '.$year.'">
					    <div class="info-box bg-purple">
					    <span class="info-box-icon">

					    <!--<i class="fa fa-bookmark-o"></i>-->

					    <input type="text" class="dial" data-min="0" data-max="100" class="knob" value="'.number_format($width,2,'.','').'" data-skin="tron" data-thickness="0.4" data-width="80" data-height="80" data-fgcolor="#f3ac12" data-bgcolor="#ffffff" data-readonly="true" readonly="readonly" >

					    </span>
					    <div class="info-box-content">
					        <span class="info-box-text">Entry Progress</span>
					        <span class="info-box-number">'.$year.'
					        <!--<button class="btn btn-xs btn-info pull-right"><i class="fa fa-edit"></i> Data</button>-->
					        </span>
						    <div class="progress">
								<div class="progress-bar" style="width: '.$width.'%"></div>
							</div>
							<span class="progress-description">
								  '.$jumlahDataY['nmx'].' /'.$jumlahData['nmx'].' indikator</span>
						</div>
						<!-- /.info-box-content -->
						</div>
						<!-- /.info-box -->
						</a>
					</div>

					';
				}
			} else {
				// belum punya indikator
				$this->entry_progress .= '<div class="col col-xs-12 "><h4><i class="fa fa-info"></i>. Belum Ada indikator untuk Instansi Anda, Silakan Hubungi Pusdalisbang untuk mendiskusikan Indikator dan Data</h4></div>';
			}
		}
	}
}
