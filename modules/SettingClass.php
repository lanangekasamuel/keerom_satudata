<?php
Class SettingClass extends ModulClass{

	function buildForm(){
		# menampilkan form	
		$action ='upd';

		$sql = "SELECT * FROM conf";
		$result = $this->db->query($sql);
		while($data = $this->db->fetchArray($result)){
			${$data['conf']} = $data['val'];
		}
		
		#build form
		$this->title = 'Setting';
		
		$define = array (
						'sitetitle'  => $SITE_TITLE,
						'contactaddr'  => $CONTACT_ADDR,
						'tweetacc'  => $TWEET_ACC,
						'contacttelp'  => $CONTACT_TELP,
						'contactfax'  => $CONTACT_FAX,
						'contactemail'  => $CONTACT_EMAIL,
						'sitedesc'  => $SITE_DESC,
						'sitekey'  => $SITE_KEY,
						'googleacc'  => $GOOGLE_ACC,
						'hotline'  => $HOTLINE,
						'contactweb' => $CONTACT_WEB,
						'contactfb' => FB_ACC,
						'fbacc'  => $FB_ACC,
						'rootdir' 	=> ROOT_URL,
						'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/setting.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	
		$this->content = $form;
	}

	function Update(){
		# query insert
		//$datausr = $this->auth->getDetail();

		$sql = "SELECT * FROM conf";
		$result = $this->db->query($sql);
		while($data = $this->db->fetchArray($result)){
			
			$varname =str_replace('_','',strtolower($data['conf']));

			$sql = "UPDATE conf 
				SET 
					val = '".$this->scr->filter($_POST[$varname])."'					
				WHERE
					conf = '".$data['conf']."'";			
			$this->db->query($sql);
		}		
	
		echo "<script>
					alert('data tersimpan');
					window.location.href='".ROOT_URL."giadmin/setting';
			 </script>";
	}
	
	function Manage(){
		# grid & manajemen data
		$this->buildForm();
	}	
}