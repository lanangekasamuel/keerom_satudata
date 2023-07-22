<?php
Class SkpdClass extends ModulClass
{
	// tbl instansi : idinstansi, nama_instansi, singkatan, kode_urusan, kode_suburusan, kode_organisasi, idtipe_instansi, idkategori_instansi
	// tbl instansi_kategori : idkategori_instansi, kategori_instansi
	// tbl instansi_tipe : idtipe_instansi, tipe_instansi 

	function buildForm(){
		# menampilkan form
		$datausr = $this->auth->getDetail();

		// pilih theme
		if ($datausr['idgroup'] == 1) {
			$theme_file = "instansi.html";
		} else if ($datausr['idgroup'] == 2) {
			$theme_file = "instansi_instansi.html";
		}

		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM instansi WHERE idinstansi='".$this->scr->filter($_GET['id'])."'";
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';
			// $info ='batalkan jika tidak ingin merubah';

		} else{
			$action ='ins';
			$status ='tambah';
			if ($datausr['idgroup'] == 2) {
				// admin instansi
				$data['idinstansi'] = $datausr['idinstansi'];				
			} else {
				$js_onload = "
					disableElement('idinstansi');
					disableElement('idbidang_instansi');";	
			}
		}

		#build form
		$this->title = 'Instansi';
		// tipe instansi selection
		$opsi_tipe = '<option value=0>--pilih Tipe--</option>';
		$itsql = "SELECT * FROM `instansi_tipe`";
		$itres = $this->db->query($itsql);
		while($itdata = $this->db->fetchArray($itres)){
			$slc = ($itdata['idtipe_instansi']==$data['idtipe_instansi'])?'selected':'';
			$opsi_tipe .= "<option value='".$itdata['idtipe_instansi']."' $slc>".$itdata['tipe_instansi']."</option>";
		}

		// opsi instansi
		$opsi_kategori = '<option value=0>--pilih Kategori--</option>';
		$kisql = "SELECT * FROM `instansi_kategori` ORDER BY kategori_instansi ASC";
		$kires = $this->db->query($kisql);
		while($kidata = $this->db->fetchArray($kires)){
			$slc = ($kidata['idkategori_instansi']==$data['idkategori_instansi'])?'selected':'';
			$opsi_kategori .= "<option value='".$kidata['idkategori_instansi']."' $slc>".$kidata['kategori_instansi']."</option>";
		}

		// opsi bidang (opsi berubah jika instansi berubah)
		$opsi_urusan = '<option value=0>--pilih Bidang Urusan--</option>';
		$bidsql = "SELECT * FROM `urusan` WHERE kode_suburusan <> '' ORDER BY kode_urusan,kode_suburusan ASC";
		$bidres = $this->db->query($bidsql);
		while($biddata = $this->db->fetchArray($bidres)){
			switch ($biddata['kode_urusan']) {
				case '1.1' : $prefix = 'Wajib Pelayanan Dasar - '; break;
				case '1.2' : $prefix = 'Wajib Bukan Pelayanan Dasar - '; break;
				case '2' : $prefix = 'Pilihan - '; break;
				case '3' : $prefix = 'Umum - '; break;
			}

			$slc = ($biddata['kode_urusan']==$data['kode_urusan'] && $biddata['kode_suburusan']==$data['kode_suburusan'])?'selected':'';
			$opsi_urusan .= "<option value='".$biddata['kode_urusan'].".".$biddata['kode_suburusan']."' $slc>".$prefix.$biddata['urai']."</option>";
		}

		$define = array (
						'info'		=>$info,
						'nama_instansi'		=> $data['nama_instansi'], 
						'singkatan'	=> $data['singkatan'], 
						'opsi_tipe' 	=> $opsi_tipe,
						'opsi_kategori'	=> $opsi_kategori,
						'opsi_urusan'	=> $opsi_urusan,
						'id' 		=> $data['idinstansi'],
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
		<script src='{themepath}js/instansi.js'></script>
		<script src='{themepath}js/jqBootstrapValidation.js'></script>
		<script>
			$(document).ready(function(){
				$('input,select,textarea,button').not('[type=submit]').jqBootstrapValidation({preventSubmit: true});
				{$js_onload}
			});
		</script>
		";

		if (isset($_GET['ajaxOn'])) {
			echo json_encode([
				'content' => $form,
				'message' => 'load berhasil'
			]);
			die();

		} else return $form; 
	}
	function Insert(){
		# query insert 
		// tbl instansi : idinstansi, nama_instansi, singkatan, kode_urusan, kode_suburusan, kode_organisasi, idtipe_instansi, idkategori_instansi

		// print_r($_POST);
		// extract kode urusan
		$kode_urusan = explode('.',$_POST['kode_urusan']);
		// kode bidang
		$kode_bidang = $kode_urusan[(count($kode_urusan)-1)];
		array_pop($kode_urusan); // hapus kode bidang
		$kode_urusan = implode('.',$kode_urusan);

		$sql = "INSERT INTO instansi 
				SET 
					nama_instansi = '".$this->scr->filter($_POST['nama_instansi'])."', 
					singkatan = '".$this->scr->filter($_POST['singkatan'])."', 
					kode_urusan = '".$this->scr->filter($kode_urusan)."', 
					kode_suburusan = '".$this->scr->filter($kode_bidang)."',
					idkategori_instansi = '".$this->scr->filter($_POST['idkategori'])."', 
					idtipe_instansi = '".$this->scr->filter($_POST['idtipe'])."' 
					";		

		$insertQuery = $this->db->query($sql);
					// die($sql);

		if ($insertQuery) {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/instansi'>";				
		} else {
			echo "<script>alert('data gagal disimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/instansi/form.htm'>";
		}
	}
	function Update(){
		# query update 
		// tbl instansi : idinstansi, nama_instansi, singkatan, kode_urusan, kode_suburusan, kode_organisasi, idtipe_instansi, idkategori_instansi

		// extract kode urusan
		$kode_urusan = explode('.',$_POST['kode_urusan']);
		// kode bidang
		$kode_bidang = $kode_urusan[(count($kode_urusan)-1)];
		array_pop($kode_urusan); // hapus kode bidang
		$kode_urusan = implode('.',$kode_urusan);

		$sql = "UPDATE instansi
				SET 
					nama_instansi = '".$this->scr->filter($_POST['nama_instansi'])."', 
					singkatan = '".$this->scr->filter($_POST['singkatan'])."', 
					kode_urusan = '".$this->scr->filter($kode_urusan)."', 
					kode_suburusan = '".$this->scr->filter($kode_bidang)."',
					idkategori_instansi = '".$this->scr->filter($_POST['idkategori'])."', 
					idtipe_instansi = '".$this->scr->filter($_POST['idtipe'])."' 
				WHERE 
					idinstansi = '".$this->scr->filter($_POST['id'])."';
					";		

		// die($sql);

		$updateQuery = $this->db->query($sql);
					// die($sql);

		if ($updateQuery) {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/instansi'>";				
		} else {
			echo "<script>alert('data gagal disimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/instansi/form.htm'>";
		}

	}
	function Delete(){
		# query delete 
		if ($this->hasAkses($_GET['id'])) {
			$sql = "DELETE FROM instansi WHERE idinstansi='".$this->scr->filter($_GET['id'])."'";
			$this->db->query($sql);
			echo "<script>alert('data terhapus');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/instansi'>";
		} else {
			echo "<script>alert('tidak ada akses');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/instansi'>";
		}
	}
	function hasAkses($idinstansi) {
		// load use session
		$datausr = $this->auth->getDetail();
		if ($datausr['idgroup'] == 1) {
			// akses untuk admin
			return true;
		} else if ($datausr['idgroup'] == 2) {
			// akses untuk admin skpd
			return false;
		} else {
			return false;
		}
	}

	function Manage()
	{
		/* [anovedit][rewrite] */
		$qskpdq = $this->db->ci3db->query("SELECT
			ik.*,ub.*,i.*,
			uc.urai as urai_urusan,
			ub.urai AS urai_suburusan,
			-- semua users per/skpd digabung jadi 'a,b,c,d,e'
			(select GROUP_CONCAT(u.username SEPARATOR ',') from users u where u.idinstansi = i.idinstansi) as userslist
			FROM instansi i
			LEFT JOIN instansi_kategori ik ON (ik.idkategori_instansi = i.idkategori_instansi)
			LEFT JOIN urusan ub ON (ub.kode_urusan = i.kode_urusan AND ub.kode_suburusan = i.kode_suburusan)
			LEFT JOIN urusan uc on (uc.kode_urusan = i.kode_urusan AND uc.kode_suburusan = '')
			WHERE i.nama_instansi NOT LIKE 'admin%' -- [?]
			ORDER BY i.kode_urusan,i.kode_suburusan,i.kode_organisasi ASC
		");

		$this->title = 'Satuan Kerja Perangkat Daerah';

		$TemplatWaras1 = TemplatWaras1::init();
		$TemplatWaras1->set_root($this);

 		$TemplatWaras1->load(ROOT_PATH.'/themes/'.THEME.'/_/skpd/list.tpl', [
 			'qskpdq' => &$qskpdq,
 		]);
	}

	function FrontList()
	{
		$sql = "SELECT * FROM instansi i ORDER BY i.kode_urusan,i.kode_suburusan,i.kode_organisasi";
		$res = $this->db->query($sql);
		$nrw = $this->db->numRows($res);

		$pgn = new PaginateClass($nrw,12,5,ROOT_URL.$_GET['mode']."/{pg}/{pgs}/pages.htm");
		$pgstart = $pgn->indexstart;
		$pgend = $pgn->indexend;

		for ($i=0; $i < $nrw; $i++) {
			$tmpdata = $this->db->fetchArray($res);
			if ($tmpdata) $data[$i] = $tmpdata;
			else break;
		}

		$TemplatWaras2 = TemplatWaras2::init();
		$TemplatWaras2->set_root($this->template);
		$TemplatWaras2->data([
			'pagination' => &$pgn,
			'xskpdz' => &$data,
		]);

		$menu = new MenuClass;
		$user = new UserClass;
		$TemplatWaras2->block([
			'pagetitle' => 'Wali Data Perangkat Daerah',
			'account_menu' => $user->AccountMenu(),
			'menu'         => $menu->FrontDisplay('T'),
			'menufooter'   => $menu->FrontDisplay('B'),
			'sitetitle' => SITE_TITLE,
			'sitekey' => SITE_KEY,
			'sitedesc' => SITE_DESC,
			'home' => ROOT_URL,
			'tweetacc' => TWEET_ACC,
			'fbacc' => FB_ACC,
			'googleacc' => GOOGLE_ACC,
			'contactaddr' => CONTACT_ADDR,
			'contacttelp' => CONTACT_TELP,
			'contactweb' => CONTACT_WEB,
			'contactfb' => FB_ACC,
			'contactfax' => CONTACT_FAX,
			'contactemail' => CONTACT_EMAIL,
			'hotline' => HOTLINE,
			'themepath' => THEME_URL,
		]);
		$TemplatWaras2->load(THEME_PATH.'/_/skpd/frontlist.tpl');
		$TemplatWaras2->get_root()->init(THEME.'/detail.html');
		$TemplatWaras2->get_root()->printTpl();
		die();
	}

	function getJSON($id)
	{
		# tampilan depan
		$jmode = $_GET['ajaxmode'];
		switch ($jmode) {
			case 'kelompokskpd':
				return $this->_frontlist_kelompokSkpd($id);
			break;
			default:
			break;
		}
	}

	private function _frontlist_kelompokSkpd($idinstansi)
	{
		$sqlInstansi = "SELECT * FROM instansi WHERE idinstansi='{$this->scr->filter($idinstansi)}'";
	 	$QInstansi = $this->db->query($sqlInstansi);
	 	$dataInstansi = $this->db->fetchAssoc($QInstansi);

		$tbody = "<p><b>{$dataInstansi['nama_instansi']}</b></p>";

	 	// menampilkan kelompok teratas di instansi terkait
		$sqlKelompok = "SELECT *
			FROM kelompok_matrix k
			WHERE k.idparent = 0
			and k.pk_instansi = {$this->db->ci3db->escape($idinstansi)}
			and k.publish=1";

		$QKelompok = $this->db->query($sqlKelompok);
	 	$this->idinstansi = $idinstansi;

	 	$this->detailKelompok = "";
	 	while($rKelp = $this->db->fetchAssoc($QKelompok)){
	 		$topParent =  $rKelp['idkelompok'];
	 		$this->_frontlist_lisdetailtelement($topParent);
	 	}
	 	
	 	$tbody .= $this->detailKelompok;

 		$content = '
 			<div class="row">
 				<div class="panel-group col-lg-8 col-md-6">
 					<div class="box box-success" id="sub_element">
 						<div class="box-header with-border">
 							<h3 class="box-title">Chart </h3>
						</div><!-- /.box-header -->
						<div class="box-body" id="chart_content" style="height:450px;"></div><!-- /.box-body -->
					</div>
				</div>
				<div class="panel-group col-lg-4 col-md-6">
					<div class="box box-success" id="list_indikator">
						<div class="box-header with-border">
							<h3 class="box-title">Kelompok & Sub Kelompok Data</h3>
						</div><!-- /.box-header -->
						<div class="box-body list-kelompok-skpd" style="height:450px;overflow-y:auto;overflow-x:hidden;">'.$tbody.'</div><!-- /.box-body -->
					</div>
				</div>
			</div>';

		return json_encode([
			'nama_skpd' => $dataInstansi['nama_instansi'],
			'body' => $content,
		]);
	}

	private function _frontlist_lisdetailtelement($idkelompok,$tab = 0)
	{
		/*
		* dipakai pada form edit detail kelompok
		* lisitng element & sub (parent & 1 child)
		* tambahkan detail kelompok
		* - load kelompok - kelompok detail
		*/
		// idkelompok, id_parent, urai, formula, satuan
		// kelompok_detail, idkelompok_detail, idkelompok, tahun, nilai, kode_urusan, kode_suburusan, kode_organisasi, idinstansi, postdate

		// $sqlInstansi = "AND u.idinstansi='".$this->idinstansi."'";
		$sqlInstansi = "";

		// unset($dataKelompok,$sql,$dataDetail);
		// mengambil data dirinya
		$sqlKelompok = "SELECT * FROM kelompok_matrix a
			WHERE a.idkelompok='{$this->scr->filter($idkelompok)}'
			and a.publish=1";
		$QKelompok = $this->db->query($sqlKelompok);
		$dataKelompok = $this->db->fetchAssoc($QKelompok);

		$sqlchild = "SELECT * FROM kelompok_matrix k
			WHERE k.idparent='{$this->scr->filter($dataKelompok['idkelompok'])}'
			and k.publish=1";
		$rchild = $this->db->query($sqlchild);
		$n_child = $this->db->numRows($rchild);

		$action = '
			<div>
				<button type="button" class="btn-flat btn-chart-skpd" onClick="openSkpdChart(\''.$idkelompok.'\')">
					<i class="fa fa-bar-chart-o"></i>
				</button>
			</div>';

		$parent_urai = htmlentities($dataKelompok['urai']);
		if ($n_child > 0) {
			// parent kelompok
			// idkelompok ini masih memiliki child didalamannya
			$this->detailKelompok .= "
					<li>{$action}
						<span>{$parent_urai}</span>
						<br class:'clear'>
					</li><ul>";

			while ($dataDetail = $this->db->fetchAssoc($rchild)) {
				$this->_frontlist_lisdetailtelement($dataDetail['idkelompok'],$tab+1);
			}

			$this->detailKelompok .= "</ul>";

		} else {
			// untuk item yang tdk mempunyai anak lg
			// <button type="button" class="btn-flat"><i class="fa fa-save"></i></button>
			// $curent_klp = "";
			$this->detailKelompok .= "<li>{$action} <span>".$parent_urai."</span> <br class='clear'></li>";
		} // ifelse
	}

}

