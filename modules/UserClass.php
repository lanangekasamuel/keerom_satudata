<?php
Class UserClass extends ModulClass{

/*
 *		
 // users : iduser,idgroup,username,idinstnasi,idbidang_instansi
 // group : idgroup,group
 // instansi : idinstansi,nama_instansi,singkatan
 // isntansi_bidang : idbidang_instansi,bidang,idinstansi
 */

	function buildForm(){
		# menampilkan form
		# get data	
		
		$datausr = $this->auth->getDetail();
		// die(print_r($_GET,1));

		// pilih theme
		if ($datausr['idgroup'] == 1) {
			$theme_file = "users.html";
		} else if ($datausr['idgroup'] == 2) {
			$theme_file = "users_instansi.html";
		}

		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM users WHERE iduser='".$_GET['id']."'";
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';
			$uservalidation = "";
			// $info ='batalkan jika tidak ingin merubah';
			$this->subMode = "edit";
		}
		else if($_GET['idinstansi'] > 0){ //tambahkan instansi, link dari skpd
			$action ='ins';
			$status ='tambah';
			$uservalidation = "";
			$data['idgroup'] = 2;
			$data['idinstansi'] = $_GET['idinstansi'];
			// $js_onload = "disableElement('idbidang_instansi');";	
			$this->subMode = "edit";
		}				
		else{
			$action ='ins';
			$status ='tambah';
			$uservalidation = 'data-validation-ajax-ajax="'.ROOT_URL.'ajax/user/usercheck/1"';
			if ($datausr['idgroup'] == 2) {
				// admin instansi
				$data['idinstansi'] = $datausr['idinstansi'];				
			} else {
				$js_onload = "
					disableElement('idinstansi');
					disableElement('idbidang_instansi');";	
			}
			$this->subMode = "add";
		}

		#build form
		$this->title = 'Users';
		// group selection
		$opsi_group = '';
		$gsql = "SELECT * FROM `group`";
		$gres = $this->db->query($gsql);
		while($gdata = $this->db->fetchArray($gres)){
			$slc = ($gdata['idgroup']==$data['idgroup'])?'selected':'';
			$opsi_group .= "<option value='".$gdata['idgroup']."' $slc>".$gdata['group']."</option>";
		}

		// opsi instansi
		$opsi_instansi = '<option value=0>--pilih Instansi--</option>';
		$insql = "SELECT * FROM `instansi` ORDER BY nama_instansi ASC";
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

		$define = array (
						'pass'		=> '', 
						'info'		=>$info,
						'email'		=> $data['email'], 
						'uservalidation' => $uservalidation,
						'idgroup'	=> $data['idgroup'], 
						'username'  => $data['username'],
						'nama_walidata' => $data['nama_walidata'],
						'kontak'  		=> $data['kontak'],
						'opsi_group' 	=> $opsi_group,
						'opsi_instansi'	=> $opsi_instansi,
						'opsi_bidang'	=> $opsi_bidang,
						'id' 		=> $data['iduser'],
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
		<script src='{themepath}js/users.js'></script>
		<script src='{themepath}js/jqBootstrapValidation.js'></script>
		<script>
			$(document).ready(function(){
				$('input,select,textarea,button').not('[type=submit]').jqBootstrapValidation({preventSubmit: true});
				{$js_onload}
			});
		</script>
		";

		if (isset($_GET['ajaxOn'])) {
			$json_data = array(
				'content' => $form,
				'message' => 'load berhasil'
				);
			$jason = json_encode($json_data);
			die ($jason);
		} else {
			return $form; 
		}
	}
	function Insert(){
		# query insert
		// 1. admin
		// 2. admin instansi
		// validasi (belum) : 
		// jika group=2 tapi idinstansi = 0
		// jika group=3 tapi idinstansi = 0 atau idbidang_ins~ = 0

		$datausr = $this->auth->getDetail();

		if ($datausr['idgroup'] == 1) {
			$add_instansi = (!empty($_POST['idinstansi'])) 
				? "idinstansi = '".$this->scr->filter($_POST['idinstansi'])."'," : "" ;
			$add_bidang = (!empty($_POST['idbidang_instansi'])) 
				? "idbidang_instansi = '".$this->scr->filter($_POST['idbidang_instansi'])."'," : "" ;
			$add_group = "idgroup = '".$this->scr->filter($_POST['idgroup'])."'";
		} else if ($datausr['idgroup'] == 2) {
			$add_instansi = "idinstansi = '".$datausr['idinstansi']."',";
			$add_bidang = (!empty($_POST['idbidang_instansi'])) 
				? "idbidang_instansi = '".$this->scr->filter($_POST['idbidang_instansi'])."'," : "" ;
			$add_group = "idgroup = 3";
		}

		/*
		if ($datausr['idgroup'] == 1) {
			$add_instansi = (!empty(trim($_POST['idinstansi']))) 
				? "idinstansi = '".$this->scr->filter($_POST['idinstansi'])."'," : "" ;
			$add_bidang = (!empty(trim($_POST['idbidang_instansi']))) 
				? "idbidang_instansi = '".$this->scr->filter($_POST['idbidang_instansi'])."'," : "" ;
			$add_group = "idgroup = '".$this->scr->filter($_POST['idgroup'])."'";
		} else if ($datausr['idgroup'] == 2) {
			$add_instansi = "idinstansi = '".$datausr['idinstansi']."',";
			$add_bidang = (!empty(trim($_POST['idbidang_instansi']))) 
				? "idbidang_instansi = '".$this->scr->filter($_POST['idbidang_instansi'])."'," : "" ;
			$add_group = "idgroup = 3";
		}*/

		$sql = "INSERT INTO users 
				SET 
					username = '".$this->scr->filter($_POST['username'])."', 
					pass = MD5(CONCAT(MD5(MD5('".$this->scr->filter($_POST['username'])."')),MD5(MD5('".$this->scr->filter($_POST['pass'])."')))), 
					email = '".$this->scr->filter($_POST['email'])."', 
					nama_walidata = '".$this->scr->filter($_POST['nama_walidata'])."', 
					kontak = '".$this->scr->filter($_POST['kontak'])."', 
					{$add_instansi}
					{$add_bidang}
					{$add_group}
					";		

		// die($sql);
		$insertQuery = $this->db->query($sql);

		if ($insertQuery) {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/user'>";				
		} else {
			echo "<script>alert('data gagal disimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/user/form.htm'>";
		}
	}
	function Update(){
		# query update
		// 1. admin : semua item
		// 2. admin instansi : passw self, usernama bidang
		// P : jika user kosong ,maka pass tidak bisa di generate
		// P : username dan password keduanya harus diganti bersamaan

		if ($this->hasAkses($_POST['id'])) {
			
		}

		$datausr = $this->auth->getDetail();

		$ch_pass = (!empty($_POST['pass']) && !empty($_POST['username'])) 
			? "pass = MD5(CONCAT(MD5(MD5('".$this->scr->filter($_POST['username'])."')),MD5(MD5('".$this->scr->filter($_POST['pass'])."'))))," : "";
		$ch_username = (!empty($_POST['username'])) 
			? "username = '".$this->scr->filter($_POST['username'])."', " : "";

		if ($datausr['idgroup'] == 1) {
			$add_instansi = (!empty($_POST['idinstansi'])) 
				? "idinstansi = '".$this->scr->filter($_POST['idinstansi'])."'," : "" ;
			$add_bidang = (!empty($_POST['idbidang_instansi'])) 
				? "idbidang_instansi = '".$this->scr->filter($_POST['idbidang_instansi'])."'," : "" ;
			$add_group = "idgroup = '".$this->scr->filter($_POST['idgroup'])."'";
		} else if ($datausr['idgroup'] == 2) {
			$add_bidang = (!empty($_POST['idbidang_instansi'])) 
				? "idbidang_instansi = '".$this->scr->filter($_POST['idbidang_instansi'])."'," : "" ;
			$add_group = "idgroup = 3";
		}

		$sql = "UPDATE users 
				SET 
					{$ch_username}
					{$ch_pass}
					{$add_instansi}
					{$add_bidang}
					email = '".$this->scr->filter($_POST['email'])."', 
					nama_walidata = '".$this->scr->filter($_POST['nama_walidata'])."', 
					kontak = '".$this->scr->filter($_POST['kontak'])."', 
					{$add_group}
				WHERE
					iduser = '".$this->scr->filter($_POST['id'])."'";	

		// die($sql);
		$updateQuery = $this->db->query($sql);
		if ($updateQuery) {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/user'>";				
		} else {
			echo "<script>alert('data gagal disimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/user/".$_POST['id']."/form.htm'>";
		}
	}

	function Delete(){
		# query delete 
		// 
		if ($this->hasAkses($_GET['id'])) {
			$sql = "DELETE FROM users WHERE iduser='".$this->scr->filter($_GET['id'])."'";
			$this->db->query($sql);
			echo "<script>alert('data terhapus');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/user'>";
		} else {
			echo "<script>alert('tidak ada akses');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/user'>";
		}
	}
	function Manage(){
		# grid & manajemen data
		$this->title = 'Users';		
		$this->pgScript = '<script src="{themepath}js/users.js"></script>';
		$this->in_content .= $this->__tabelUsers();

		$this->content = $this->in_content;
	}

	function hasAkses($iduser) {
		// load use session
		$datausr = $this->auth->getDetail();

		// load checked user id
		$sql = "SELECT * FROM users WHERE iduser='".$this->scr->filter($iduser)."'";
		$qCurrentUser = $this->db->query($sql);
		$currentUser = $this->db->fetchAssoc($qCurrentUser);

		if ($datausr['idgroup'] == 1) {
			// akses untuk admin
			return true;
		} else if ($datausr['idgroup'] == 2 && $currentUser['idinstansi'] == $datausr['idinstansi'] 
			&& !empty($currentUser['idbidang_instansi'])) {
			// akses untuk admin skpd
			// jika user adalah anak dari skpd/instansi, maka proses ke selanjutnya
			return true;
		} else {
			return false;
		}
	}

	private function __tabelUsers()
	{		

		$pgmode = $this->scr->filter($_GET['content']);
		$datausr = $this->auth->getDetail();
		if (!(!empty($datausr['idinstansi']) && !empty($datausr['idbidang_instansi']))) {
			// user level/group bukan bidang, perlihatkan link tambah user
			$link_tambah = "<div class='mb-10'>
			<a href='".ROOT_URL."giadmin/".$pgmode."/form.htm' class='btn btn-flat btn-success' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Tambah User\"><i class='fa fa-plus'></i> &nbsp;Tambah User</a>
		</div>	";
		}

		$table = "			
								
		{$link_tambah}
		<div>

		<table id='tb_user' class ='table-striped table-condensed-side table-bordered' border='0' cellpadding='0' cellspacing='0' width='100%'>
			<thead>
				<tr>
					<th align='center'><b>No</b></th>
					<th align='center'>User</th>
					<th align='center'>Asal SKPD/Instansi</th>
					<th align='center'>Bidang</th>
					<th align='center'>Group</th>
					<th align='center'>Nama Wali</th>
					<th align='center'>Kontak</th>
					<th align='center'>Login Terakhir</th>
					<th align='center'><b>Action</b></th>
				</tr>
			</thead>
			<tbody> ";

		$table .= $this->__userRow($pagedisplay);
				 
		// closetable		
		$table .="</tbody>
			<tfoot>
			</tfoot>
		</table>

		</div> 
					  
		";		

		return $table;					
	}

	private function __userRow(&$pagedisplay){

		$themepath = THMDIR ;
		$rootdir = ROOT_URL;

		// select record by group akses, 1:sa, 2:si
		$datausr = $this->auth->getDetail();

		if ($datausr['idgroup'] == 1) {
			// bukan dari instansi&bidang, user group = 1 (admin) (empty($datausr['idinstansi']) && empty($datausr['idbidang_instansi'])|| 
			$sql = "SELECT * FROM users u 
		 			LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
		 			LEFT JOIN `instansi_bidang` b ON b.idbidang_instansi = u.idbidang_instansi
		 			LEFT JOIN `group` g ON g.idgroup = u.idgroup
		 			ORDER BY i.nama_instansi ASC";
		} else if ($datausr['idgroup'] == 2) {
			// instansi bukan dari bidang, user group = 2 (skpd/instansi), : tampilkan juga bidang dibawahnya (!empty($datausr['idinstansi']) && empty($datausr['idbidang_instansi'])) || 
			$sql = "SELECT * FROM users u 
		 			LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
		 			LEFT JOIN `instansi_bidang` b ON b.idbidang_instansi = u.idbidang_instansi
		 			LEFT JOIN `group` g ON g.idgroup = u.idgroup
		 			WHERE u.idinstansi = {$datausr['idinstansi']}
		 			ORDER BY i.nama_instansi ASC";
		} else if ($datausr['idgroup'] == 3) {
			// instansi -> bidang, user group = 3 (bidang) (!empty($datausr['idinstansi']) && !empty($datausr['idbidang_instansi'])|| 
			// akses dibatalkan
			$sql = "SELECT * FROM users u 
		 			LEFT JOIN instansi i ON i.idinstansi = u.idinstansi
		 			LEFT JOIN `instansi_bidang` b ON b.idbidang_instansi = u.idbidang_instansi
		 			LEFT JOIN `group` g ON g.idgroup = u.idgroup
		 			WHERE u.idinstansi = {$datausr['idinstansi']}
		 			AND u.idbidang_instansi = {$datausr['idbidang_instansi']}
		 			ORDER BY i.nama_instansi ASC";
		}
			
		$no = 1;		
		$dataSource = $this->db->query($sql);
		while($data = $this->db->fetchArray($dataSource)){
			$sresult .="<tr > 
					<td> $no </td>
					<td><a href=\"".$rootdir."giadmin/".$_GET['content']."/".$data['iduser']."/view.htm \">". $data['username'] ."</a></td>
					<td>". $data['nama_instansi'] ."</td>
					<td>". $data['bidang'] ."</td>
					<td>". $data['group'] ."</td>
					<td>". $data['nama_walidata'] ."</td>
					<td>". $data['kontak'] ."</td>
					<td>". $data['lastlogin'] ."</td>
					<td>
						<a href='".ROOT_URL."giadmin/user/".$data['iduser']."/form.htm' class='fa fa-edit btn btn-sm btn-primary btn-flat' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Edit\"></a>
						<a href='#' class='fa fa-times-circle btn btn-sm btn-danger btn-flat' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\" onClick=\"codel('".ROOT_URL."giadmin/user/".$data['iduser']."/del.htm');\"></a>
					</td>
				</tr>";							
			$no++;
		}				
			
		return $sresult;
	}

	function detail(){

	}

	function getJSON($id){
		// ajax
		$jmode = $_GET['ajaxmode'];

		switch ($jmode) {
			case 'usercheck':
				return $this->_isValidUser();
			break;
			case 'bidang_instansi' :
				return $this->_bidangInstansi($id);
			break;
		}
	}

	private function _isValidUser() {
		$datausr = $this->auth->getDetail();
		// cek existensi user 
		$qUser = $this->db->query("SELECT * FROM users WHERE username='".$_REQUEST['value']."'");
		$valid = ($this->db->numRows($qUser) == 0) ? true : false;

	    echo json_encode(
		    array(
		    "value" => $_GET["value"],
		    "valid" => $valid,
		    "message" => "username sudah dipakai"
		    )
	    );

	    die();
	}

	private function _bidangInstansi($idinstansi) {
	/*
	 * id = idinstansi
	 */
		if ($idinstansi > 0) {
			$sqlsub = $this->db->query("SELECT * FROM instansi_bidang WHERE idinstansi=".$idinstansi);
	  		while ($dataSUrusan = $this->db->fetchAssoc($sqlsub)) {
	  			$option .= "<option value='".$dataSUrusan['idbidang_instansi']."'>{$dataSUrusan['bidang']}</option>";
	  		}
		} else {
			$option = "";
		}
	  	return json_encode(array('options'=>$option)); 
	}

	function AccountMenu() {
		/*
		 * create user menu panel on top right of pages
		 * - cek login
		 * - load theme (kontent untuk pojok kanan atas /  user panel)
		 * - return processed theme
		 */
		$datausr = $this->auth->getDetail();

		if ($datausr) {
			// select : idinstansi,nama_instansi,singkatan 
			$sql = "SELECT i.nama_instansi,i.singkatan,ib.bidang FROM `users` AS u 
					LEFT JOIN `instansi` AS i  ON i.idinstansi = u.idinstansi 
					LEFT JOIN `instansi_bidang` AS ib  ON ib.idbidang_instansi = u.idbidang_instansi 
					WHERE u.iduser=".$datausr['iduser']."";

			$QuserData = $this->db->query($sql);
			$userData = $this->db->fetchAssoc($QuserData);

			// format nama user
			$user_name = strtoupper($datausr['username']) ;

			// format title
			$user_title = $userData['nama_instansi'];
			if ($datausr['idgroup'] == 1) $user_title = 'Administrator';

			// tambahkan keterangan nama bidang jida user = user bidang
			if (!empty($userData['bidang'])) $user_title .= "<br>Bidang : ".$userData['bidang'];

			$define = array (
				'user_name'	=> $user_name,
				'user_title'=> $user_title,
	        );
			$this->template->init(THEME.'/account_menu.html');
		} else {
			$this->template->init(THEME.'/account_login.html');
		}

		$this->template->defineTag($define);
		return $this->template->parse(); 
	}

}