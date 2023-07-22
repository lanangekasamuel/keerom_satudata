<?php
Class AksesClass extends ModulClass{

	function buildForm(){
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM `group` WHERE idgroup='".$_GET['id']."'";
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';
			$info =', kosongkan jika tidak ingin merubah';	

			// da
			$result = $this->db->query("SELECT GROUP_CONCAT(idadminmenu) AS menu_id FROM hakakses AS ha WHERE idgroup=".$_GET['id']);
			$rdata = $this->db->fetchAssoc($result);
			$menu = $rdata['menu_id'];				
		}				
		else{
			$status ='tambah';
			$menu = '';				
		}

		// pilihan admin menu akses
		$currentMenu = explode(',',$menu);
		$currentMenu = array_flip($currentMenu);
		$cbAdminMenu = "";
		$amQuery = $this->db->query("SELECT idadminmenu,menu FROM adminmenu ORDER BY parent,ord ASC");
		while ($amMenu = $this->db->fetchArray($amQuery)) {
			$checked = (array_key_exists($amMenu['idadminmenu'], $currentMenu)) ? 'checked=true' : '' ;
			$cbAdminMenu .= "<div class='checkbox col col-md-4 col-sm-4'><label class=''><input type='checkbox' name='adminmenu[]' value='{$amMenu['idadminmenu']}' {$checked}>{$amMenu['menu']}</label></div>";
		}

		// <div class="checkbox col col-sm-4 col-md-3"><label><input name="penggunaan[]" id="penggunaan[]" type="checkbox" value="6">IK</label></div>

		#build form
		$this->title = 'Users';
		// group selection
		$optGroup = '';
		$gsql = "SELECT * FROM `group`";
		$gres = $this->db->query($gsql);
		while($gdata = $this->db->fetchArray($gres)){
			$slc = ($gdata['idgroup']==$data['idgroup'])?'selected':'';
			$optGroup .= "<option value='".$gdata['idgroup']."' $slc>".$gdata['group']."</option>";
		}

		$optSkpd = '';
		$gsql = "SELECT * FROM `skpd` ORDER BY singkatan_skpd ASC";
		$gres = $this->db->query($gsql);
		while($gdata = $this->db->fetchArray($gres)){
			$slc = ($gdata['id_skpd']==$data['id_skpd'])?'selected':'';
			$optSkpd .= "<option value='".$gdata['id_skpd']."' $slc>".$gdata['singkatan_skpd']."</option>";
		}

		$define = array (
						'group'		=> $data['group'], 
						'info'		=>$info,
						'cb_akses_menu'		=> $cbAdminMenu, 
						'idgroup'	=> $data['idgroup'], 
						'username'  => $data['username'],
						'optGroup' 	=> $optGroup,
						'optSkpd'	=> $optSkpd,
						'id' 		=> $data['idgroup'],
						'status' 	=> $status,
						'rootdir' 	=> ROOT_URL,
						'action' 	=> $action
						 );

		if (isset($_GET['ajaxOn'])) {
			$define['ajax_var'] = 'id="ajaxOn" name="ajaxOn"'; // untuk handling asal update
		}

		// print_r($_GET);

		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/akses.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	

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
		// group : idgroup, group
		// hakakses : idakses, idadminmenu, idgroup

		// dapatkan id terbaru
		$result 	= $this->db->query("select max(idgroup) as mxid from `group`");
		$dmax 		= $this->db->fetchArray($result);
		$newid	 	= $dmax[mxid] + 1; //id baru

		$datausr = $this->auth->getDetail();
		$sql = "INSERT INTO `group`
				SET 
				`group` = '".$this->scr->filter($_POST['group'])."', 
				idgroup = '".$newid."'
				";	

		$insertQuery = $this->db->query($sql);

		// sisipkan ke tabel hak akses
		$adminmenu = $_POST['adminmenu'];
		foreach ($adminmenu as $idakses) {
			# code...
			$sql = "INSERT INTO hakakses 
					SET 
					idgroup = '".$newid."',
					idadminmenu = '".$idakses."' 
					";	
			$insertQuery = $this->db->query($sql);
		}

		if (isset($_POST['ajaxOn'])) {
			// dipanggil melalui ajax
			$message = ($insertQuery) 
				? '<h2 class="text-success">menambahkan data sukses</h2>' 
				: '<h2 class="text-warning">menambahkan data gagal</h2>';

			$json_data = array(
				'message' => $message
				);

			$jason = json_encode($json_data);
			die ($jason);
		} else {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/akses'>";				
		}
	}
	function Update(){
		# query insert
		$datausr = $this->auth->getDetail();
			
		// update tabel group
		$sql = "UPDATE `group` 
				SET 
				`group` = '".$this->scr->filter($_POST['group'])."'
				WHERE 
				idgroup = '".$this->scr->filter($_POST['id'])."'
				";			
		$updateQuery = $this->db->query($sql);

		// remove-add record di tabel hakakses
		// hapus dengan group
		// tambahkan akses record baru
		$adminmenu = $_POST['adminmenu']; // list menu terbaru
		
		$qry = "DELETE FROM hakakses 
				WHERE 
				idgroup=".$this->scr->filter($_POST['id']);
		$this->db->query($qry);

		foreach($adminmenu as $menu_id) {
			// tambahkan akses
			$qry = "INSERT INTO `hakakses` SET 
				idadminmenu='".$menu_id."',
				idgroup='".$this->scr->filter($_POST['id'])."'";
			$this->db->query($qry);
		}


		if (isset($_POST['ajaxOn'])) {
			// dipanggil melalui ajax
			$message = ($updateQuery) 
				? '<h2 class="text-success">update sukses</h2>' 
				: '<h2 class="text-warning">update gagal</h2>';

			$json_data = array(
				'message' => $message
				);

			$jason = json_encode($json_data);
			die ($jason);
		} else {
			echo "<script>alert('data tersimpan');</script>";
			die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/akses'>");				
		}
	}
	function Delete(){
		# query delete 
		// die(print_r($_GET));
		// hapus groud dan akses dari group dengan idtertentu
		$sql = "DELETE FROM `group` WHERE idgroup='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);		
		$sql = "DELETE FROM hakakses WHERE idgroup='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/akses'>";
	}
	function Manage(){
		# grid & manajemen data
		//JOIN group g JOINu.idgroup = g.idgroup AND 
		$sql = "SELECT * FROM users u LEFT JOIN skpd s ON (u.id_skpd = s.id_skpd) ORDER BY username DESC";
		$res = $this->db->query($sql);

		// $field = array('Nama Pengguna /<br>User'=>'username', 'Alamat Email'=>'email','Asal SKPD'=>'singkatan_skpd','Grup Admin'=>'group');	
		//$field = array('username','email','group');
		//$field = array('username','email','group');
		//'RP' => 'real_pass' , ,'NAMA SKPD'=>'nama_skpd'
		$this->title = 'Group Akses';		
		$this->pgScript = '<script src="{themepath}js/akses.js"></script>';
		$this->in_content .= $this->_tableGroup();
 		// $this->in_content .= $this->grid->init($res,'username',$field,array('editing'=>'1','adding'=>'1','deleting'=>'1','class'=>'grid', 'previous'=>'0', 'updown' => '0'));

		// $tplbody =  new TemplateClass;
		// $tplbody->init(THEME.'/akses.html');

		// $define = array (								 
		// 	'content'		=> $this->in_content,
		// );       					
		// $tplbody-> defineTag($define);

		$this->content = $this->in_content;//$tplbody->parse();
	}

	private function _tableGroup()
	{		
		$arField = array('menu');
		$class = 'grid';

		$pagedisplay = '';		  
		$rows = $this->_groupRow($pagedisplay);
		$pgmode = $this->scr->filter($_GET['content']);

		$table ="				

		<div class='admin_page_note text-error'><i class='fa fa-warning'></i> &nbsp;Menghapus data akses ini bisa menyebabkan akses ke halaman administrasi beberapa user terganggu</div>
		<div class='pull-right' >$pagedisplay</div>
		<br style='clear:both'/>
			
		<div style='font-size: 12px; padding-top:15px; padding-bottom:5px; padding-right: 15px;'>
							
				<a href='".ROOT_URL."giadmin/".$pgmode."/form.htm' class='fa fa-plus btn btn-success' data-toggle=\"tooltip\" 
				data-placement=\"top\" title=\"Add\"> </a>
		</div>							
		
		<div>

				<table class ='table $class display table-condensed table-striped table-bordered' border='0' cellpadding='0' cellspacing='0' width='100%'>
				  <thead>
				  <tr style='padding: 3px;' bgcolor='#cccccc'>
					<th align='center'><b>No</b></th>
					<th align='center'>Group</th>
					<th align='center'>Akses Ke</th>
					<th align='center'><b>Action</b></th>
				  </tr>
				  </thead>
				  <tbody> ";
					// <th align='center'>Users</th>

		$table .= $rows;
				 
		// closetable		
		$table .="</tbody>
				</table>
				$pagedisplay
			  </div> 
			  <br><br>					  
			  ";		

		return $table;					
	}

	private function _groupRow(&$pagedisplay){

			$themepath = THMDIR ;
			$rootdir = ROOT_URL;
		
			/* page display */
			$numperpage = 15;	
			$numsetpg = 5;
			/* page counting */
			$pg 		=(empty($_GET[pg]))?"1":$_GET[pg];
			$pgstart	= ($pg-1) * $numperpage;
			$pgend		= ($pg) * $numperpage;
			/* data */
		// $field = array('Nama Pengguna /<br>User'=>'username', 'Alamat Email'=>'email','Asal SKPD'=>'singkatan_skpd','Grup Admin'=>'group');	
			
			//$wh = (isset($_GET['id'])) ? "WHERE idpemolaan = ".$_GET['id'] : "" ; // jika hanya 1 id yg diminta
			// $sql = "SELECT * FROM `group` ORDER BY 'group' ASC";//g left join `user` u on g.idgroup =  u.idgroup

			// $sql = "SELECT g.idgroup, g.group, 
			// 		GROUP_CONCAT(DISTINCT h.idakses ORDER BY h.idakses), 
			// 		GROUP_CONCAT(DISTINCT am.idadminmenu) AS menu, 
			// 		GROUP_CONCAT(DISTINCT u.username SEPARATOR ', ') AS username 
			// 		FROM `group` AS g 
			// 		LEFT JOIN `hakakses` AS h ON h.idgroup = g.idgroup
			// 		LEFT JOIN `adminmenu` AS am ON am.idadminmenu = h.idadminmenu
			// 		LEFT JOIN `users` AS u ON u.idgroup = g.idgroup
			// 		GROUP BY g.idgroup";	

			// $sql = "SELECT g.idgroup, g.group, 
			// 		GROUP_CONCAT(DISTINCT h.idakses ORDER BY h.idakses), 
			// 		GROUP_CONCAT(DISTINCT am.idadminmenu) AS menu 
			// 		FROM `group` AS g 
			// 		JOIN `hakakses` AS h ON h.idgroup = g.idgroup
			// 		JOIN `adminmenu` AS am ON am.idadminmenu = h.idadminmenu
			// 		GROUP BY g.idgroup";	

			$sql = "SELECT g.idgroup, g.group, 
					GROUP_CONCAT(DISTINCT h.idakses ORDER BY h.idakses) AS idmenu, 
					GROUP_CONCAT(DISTINCT am.menu) AS menu 
					FROM `group` AS g 
					LEFT JOIN `hakakses` AS h ON h.idgroup = g.idgroup
					LEFT JOIN `adminmenu` AS am ON am.idadminmenu = h.idadminmenu
					GROUP BY g.idgroup";	

			$no = 1;		
			$dataSource = $this->db->query($sql);

			$nrw = $this->db->numRows($dataSource);
			$numpg = ceil($nrw/$numperpage);				
			$lssetpg = ceil($numpg/$numsetpg); 	
					
			$pg = (empty($_GET[pg]))?1:$_GET[pg];
			$pgs = (empty($_GET[pgs]))?1:$_GET[pgs];
			$prev = $_GET[pgs]-1;

			// pattern giadmin/content/pg/pgs/list.htm$ awal.gi?mode=admin&content=$1&pg=$2&pgs=$3&cntmode=list
			$pages = "<ul id=\"pagination-flickr\" class='pagination'>"; 
			$x  = ($pgs-2)*$numsetpg+1;
			//print $x;
			if($pgs>'1'){
				$pages = $pages."<li><a href=\"".$rootdir."giadmin/".$_GET['content']."/".$x."/".$prev."/list.htm \"> &laquo; Previous</a></li>"; 
				} 
			$awal  = ($pgs-1)*$numsetpg+1;
			$akhir = $pgs*$numsetpg;
			for($x=$awal;$x<=$akhir;$x++){
				if($x<=$numpg){
					$pages = $pages."<li><a href=\"".$rootdir."giadmin/".$_GET['content']."/".$x."/".$pgs."/list.htm \">".$x."</a></li>"; 
				}
			}
			//print $x;
			$next = $pgs+1;
			if($pgs<$lssetpg){
				$pages = $pages."<li><a href=\"".$rootdir."giadmin/".$_GET['content']."/".$x."/".$next."/list.htm \">Next &raquo;</a></li>"; 
			}
			$pages = $pages."</ul>";
			
			
			if($numpg > 1 ){
				$pagedisplay = $pages;
			}else{
				$pagedisplay = '';
			}
			
			if($adding == '1'){
				$add = "<a href='".ROOT_URL."giadmin/".$_GET[content]."/form.htm'
						class='fa fa-plus btn btn-success' data-toggle=\"tooltip\" 
						data-placement=\"top\" title=\"Add\"
						> </a>";
			}else{
				$add = "";
			}

			$i = 0;
					while($tmpdata = $this->db->fetchArray($dataSource)){
						$data[$i] = $tmpdata;
						$i++
;					}
					
					for($i = $pgstart; $i< $pgend; $i++){

						// checkbox element & def check
						// adminmenu : idadminmenu,menu,link
						
						$no = $i +1;
						// $color = ($no%2 == 0)?'#F7F7F7':'';
													// <td>". $data[$i]['username'] ."</td>
						if($data[$i][0] <> ''){
							$sresult .="<tr> 
													<td> $no </td>
													<td><a href=\"".$rootdir."giadmin/".$_GET['content']."/".$data[$i]['group']."/list.htm \">". $data[$i]['group'] ."</a></td>
													<td>".$data[$i]['menu'] ."</td>
													<td nowrap>
															
										 					<a href='javascript:editGroup(".$data[$i]['idgroup'].");' class='fa fa-edit btn btn-primary' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Edit\"></a>
										 					<!--
										 					<a href='#' class='fa fa-times-circle btn btn-danger' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\" onClick=\"codel('".ROOT_URL."giadmin/akses/".$data[$i]['idgroup']."/del.htm');\"></a>
										 					-->
															
													</td>
										</tr>		
										";	
						}							
						$no++;
					}				
				
			

			 return $sresult;


	}

	// function Json(){
	// 	# grid & manajemen data
	// 	//JOIN group g JOINu.idgroup = g.idgroup AND 
	// 	//print_r($_GET);
	// 	$sql = "SELECT * FROM users u JOIN skpd s ON (u.id_skpd = s.id_skpd) WHERE username LIKE '%".$_GET['username']."%' ORDER BY username DESC LIMIT 0,25";
	// 		$res = $this->db->query($sql);

	// 	$json_data = array();
	// 	while ($data = $this->db->fetchArray($res)){
	// 		//$json_data[][$data['id_login']] = $data['username'];
	// 		$json_data[$data['id_login']]['id'] = $data['id_login'];	//id sebagai index pengurutan
	// 		$json_data[$data['id_login']]['value'] = $data['username'];	

	// 		$new_label = $data['usename'];//preg_replace("/{$_GET['username']}/si", "<span style=\"color:red;\">{$_GET['username']}</span>", $data['username']);

	// 		$json_data[$data['id_login']]['label'] = $new_label;	
	// 	}

	// 	sort($json_data);
		
	// 	$jason = json_encode($json_data);
	// 	die ($jason);
	// }	

	// function JsEdit() {

	// 	# menampilkan form
	// 	$action = 'ins';
	// 	# get data	
	// 	if($_GET['id'] <> ''){
	// 		$sql = "SELECT * FROM users WHERE id_login='".$_GET['id']."'";
	// 		$result = $this->db->query($sql);
	// 		$data = $this->db->fetchArray($result);
	// 		$action ='upd';
	// 		$status ='edit';
	// 		$info =', kosongkan jika tidak ingin merubah';					
	// 	}				
	// 	else{
	// 		$status ='tambah';
	// 	}

	// 	#build form
	// 	$this->title = 'Users';
	// 	$optGroup = '';
	// 	$gsql = "SELECT * FROM `group`";
	// 	$gres = $this->db->query($gsql);
	// 	while($gdata = $this->db->fetchArray($gres)){
	// 		$slc = ($gdata['idgroup']==$data['idgroup'])?'selected':'';
	// 		$optGroup .= "<option value='".$gdata['idgroup']."' $slc>".$gdata['group']."</option>";
	// 	}
	// 	$define = array (
	// 					'pass'		=> '', 
	// 					'info'		=>$info,
	// 					'email'		=> $data['email'], 
	// 					'idgroup'	=> $data['idgroup'], 
	// 					'username'  => $data['username'],
	// 					'optGroup' 	=> $optGroup,
	// 					'id' 		=> $data['username'],
	// 					'status' 	=> $status,
	// 					'rootdir' 	=> ROOT_URL,
	// 					'action' 	=> $action, 
	// 					'ajax_var' 	=> 'id="ajaxOn" name="ajaxOn"', // untuk handling asal update
	// 					 );		
	// 	$tplform = new TemplateClass;
	// 	$tplform->init(THEME.'/forms/users.html');
	// 	$tplform->defineTag($define);	
	// 	$form = $tplform->parse();	
	// 	// return $form; 

	// 	$json_data = array(
	// 		'content' => 'ini isi'.$form,
	// 		'message' => 'load berhasil'
	// 		);

	// 	//sort($json_data);
		
	// 	$jason = json_encode($json_data);
	// 	die ($jason);
	// }
}
?>
