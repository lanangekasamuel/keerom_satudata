<?php
Class OutputClass extends ModulClass{

	/**
	* modul disimpan dalam 1 file untuk kemudahan upload modul
	* fungsi insert, update, delete, manage, dan pembuatan form 
	* disertakan dalam tiap modul. Template dipisahkan, masuk dalam 
	* folder themes. class ini merupakan abstrak untuk di extend oleh
	* modul-modul yang akan dipakai
	* @author Bruri <bruri@gi.co.id>
	* @version 1.0
	* @package Modul
	**/

	/* moc = module-output-class */

	public $moc_path = '/files/output/';
	/*
		html, pdf, swf
		video,audio,picture (common,web*,og*)
	*/
	public $moc_streamable = '/\.(x?htm|pdf|swf|web|mkv|avi|mp4|mpe?g|ogv|oga|mp3|png|jpe?g|bmp|gif|svg|3gp|3g2)/';

	function buildForm()
	{
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM output WHERE idoutput={$this->db->ci3db->escape($_GET['id'])}";
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';	
			$this->subMode = "edit";				

		} else {
			$status ='tambah';
			$data['publish'] = 1 ;
			$this->subMode = "add";				
		}

		#build form
		$this->title = 'Dokumen Output';

		// publikasi data
		$publish = array(1=>'Ya', '0'=>'Tidak');
		$cek_publish = "";
		foreach ($publish as $kode => $urai) {
			$slc = ($kode == $data['publish']) ?'checked':'';
			$cek_publish .= "<div class='col col-sm-2 col-md-2'><label><input name='publish' id='publish' type='radio' value='".$kode."' $slc>&nbsp;".$urai."</label></div>";
		}

		$define = array (
						'filesumber'	=> $data['filesumber'], 
						'title'			=> $data['title'], 
						'content'		=> $data['content'], 
						'cek_publish'	=> $cek_publish,
						
						'id' 		=> $data['idoutput'],
						'status' 	=> $status,
						'rootdir' 	=> ROOT_URL,
						'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/dokumen.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	
		return $form; 
	}
	function Insert()
	{
		# query insert
		$datausr = $this->auth->getDetail();

		if($_FILES["filesumber"]["name"] <> '') {

			if (is_file($uploaddir.$data['filesumber'])) @unlink($uploaddir.$data['filesumber']);

			$uploaddir = ROOT_PATH.'files/output/';
			if (!file_exists($uploaddir) || !is_dir($uploaddir)) mkdir($uploaddir); // [anovedit][workaround] buat dir kalo belum ada.

			$file_parts = pathinfo($_FILES["filesumber"]["name"]);
			$filesumber_name = md5(strtolower($_FILES["filesumber"]["name"])).'_'.date('U').'.'.$file_parts['extension'];
			$upload = new UploadClass();
			$upload->SetFileName($filesumber_name);
			$upload->SetTempName($_FILES['filesumber']['tmp_name']);
			$upload->SetUploadDirectory($uploaddir); //Upload directory, this should be writable
			$upload->SetValidExtensions(array('pdf','odp','jpg','png','mp4','webm')); 
			$upload->SetMaximumFileSize(20000000); //Maximum file size in bytes
			$upload->ReplaceFile(true);

			// $this;dump($_FILES,get_defined_vars());die();

			if ($upload->UploadFile()) {
				$qadd ="filesumber = '{$this->scr->filter($filesumber_name)}',";
			} else {
				echo "<script>alert('upload gagal');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/output'>");
			}
		}

		$sql = "INSERT INTO output SET 
			$qadd
			title = '{$this->scr->filter($_POST['title'])}',
			content = '{$this->scr->filter($_POST['content'])}',
			publish = '{$this->scr->filter($_POST['publish'])}',
			author = '{$this->scr->filter($datausr['username'])}',
			postdate = now()";
		$this->db->query($sql);

		echo "<script>alert('data tersimpan');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/output'>";
	}

	function Update(){
		# query update 
		$datausr = $this->auth->getDetail();

		$sql = "SELECT * FROM output WHERE idoutput='".$this->scr->filter($_POST['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		if($_FILES["filesumber"]["name"] <> ''){// && file_exists($uploaddir.$data['image'])

			if (is_file($uploaddir.$data['filesumber'])) {
				@unlink($uploaddir.$data['filesumber']);
			}

			$uploaddir = ROOT_PATH.'files/output/';
			$file_parts = pathinfo($_FILES["filesumber"]["name"]);
			$filesumber_name = md5(strtolower($_FILES["filesumber"]["name"])).'_'.date('U').'.'.$file_parts['extension'];
			//$uploaddir = ROOT_PATH.'files/images/output/';
			$upload = new UploadClass();
			$upload->SetFileName($filesumber_name);
			$upload->SetTempName($_FILES['filesumber']['tmp_name']);
			$upload->SetUploadDirectory($uploaddir); //Upload directory, this should be writable
			$upload->SetValidExtensions(array('pdf','odp','jpg','png','mp4','webm')); 
			$upload->SetMaximumFileSize(20000000); //Maximum file size in bytes
			$upload->ReplaceFile(true);
			if ($upload->UploadFile()){
				$qadd ="filesumber = '".$this->scr->filter($filesumber_name)."',";
			}else{
				echo "<script>alert('upload gagal');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/output'>");	
			}
		}
		
		$sql = "UPDATE output 
				SET 
					$qadd
					title = '".$this->scr->filter($_POST['title'])."',
					content = '".$this->scr->filter($_POST['content'])."',
					publish = '".$this->scr->filter($_POST['publish'])."',
					postdate = now(),
					author = '".$this->scr->filter($datausr['username'])."'
				WHERE
					idoutput = '".$this->scr->filter($_POST['id'])."'";		
		// die($sql);	
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/output'>";
	}

	function Delete()
	{
		# query delete
		$sql = "SELECT * FROM output WHERE idoutput='{$this->scr->filter($_GET['id'])}'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$uploaddir = ROOT_PATH.'files/output/';
		if(!empty($data['filesumber']) && file_exists($uploaddir.$data['filesumber'])) {
			@unlink($uploaddir.$data['filesumber']);
		}

		$sql = "DELETE FROM output WHERE idoutput='{$this->scr->filter($_GET['id'])}'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/output'>"; 
	}

	function Manage(){
		# grid & manajemen data
		# grid & manajemen data
		$imgurl = ROOT_URL.'files/output/';
		$tagopen = "<img width=\"200px\" height=\"100px\" src=\"".$imgurl;
		$tagclose = "\">";
		$sql = "SELECT *, IF(publish=1, 'Ya', 'Tidak') AS publish FROM output";
		$res = $this->db->query($sql);
		$field = array('title','filesumber','content','publish');
		$this->title = 'Dokumen Output';		
		$this->content = $this->grid->init($res,'idoutput',$field,array('editing'=>'1','adding'=>'1','deleting'=>'1','class'=>'grid', 'previous'=>'0', 'updown' => '0'));
	}

	function FrontList()
	{
		// read output folder
		$ouput_folder = ROOT_PATH . $this->moc_path;
		$ouput_folder_url = ROOT_URL . $this->moc_path;

		$file_item = null;
		$file_list = null;

		$TemplatWaras2 = TemplatWaras2::init();
		$TemplatWaras2->set_root($this->template);
		$TemplatWaras2->block([
			'pagetitle' => ('BERKAS PUBLIKASI ' . SITE_TITLE),
			'sitetitle' => SITE_TITLE,
			'sitekey' => SITE_KEY,
			'sitedesc' => SITE_DESC,
		]);

		$file_list =  $this->db->ci3db->query('SELECT * FROM output WHERE publish=1 AND filesumber IS NOT NULL ORDER BY postdate desc');
		if (isset($_GET['kat']) && !empty($_GET['kat'])) {
			$q = $this->db->ci3db
			->from('output')
			->where(['title' => $_GET['kat'], 'publish' => 1])
			->limit(1)
			->get();
			if ($q) $file_item = $q->row();
		}

		$file_item_info = null;
		if ($file_item) {
			$file_item_info = pathinfo($ouput_folder . $this->moc_path . $file_item->filesumber);
		}

		$menu = new MenuClass;
		$user = new UserClass;

		$TemplatWaras2->load(THEME_PATH . '/_/output/frontlist.tpl', [
			'ouput_folder' => $ouput_folder,
			'ouput_folder_url' => $ouput_folder_url,
			'judul' => $judul,
			'keterangan' => $keterangan,
			'file_list' => &$file_list,
			'file_item' => &$file_item,
			'file_item_info' => &$file_item_info,
			'module_output_class' => &$this,
		]);
		$TemplatWaras2->get_root()->init(THEME.'/detail.html', [
			'menu'         => $menu->FrontDisplay('T'),
			'menufooter'   => $menu->FrontDisplay('B'),
			'account_menu' => $user->AccountMenu(),
			'home'     => ROOT_URL,
			'tweetacc' => TWEET_ACC,
			'fbacc'    => FB_ACC,
			'googleacc'    => GOOGLE_ACC,
			'contactaddr'  => CONTACT_ADDR,
			'contacttelp'  => CONTACT_TELP,
			'contactweb'   => CONTACT_WEB,
			'contactfb'    => FB_ACC,
			'contactfax'   => CONTACT_FAX,
			'contactemail' => CONTACT_EMAIL,
			'hotline'      => HOTLINE,
			'themepath'    => THEME_URL,
		]);
		$TemplatWaras2->get_root()->printTpl();
	}
}
