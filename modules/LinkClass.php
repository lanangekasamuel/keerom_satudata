<?php
Class LinkClass extends ModulClass{

	function buildForm(){
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM link WHERE idlink='".$_GET['id']."'";
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';					
		}				
		else{
			$status ='tambah';
			$data['postdate'] = date('Y-m-d h:i:s');
		}

		#build form
		$this->title = 'link';

		$define = array (
						 'title'	=> $data['title'],				
						 'link'	=> $data['link'],
						 'image'	=> $data['image'],
						 'id' 		=> $data['idlink'],
						 'status' 	=> $status,
						 'rootdir' 	=> ROOT_URL,
						 'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/link.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	
		return $form; 
	}
	function Insert(){
		# query insert
		$datausr = $this->auth->getDetail();
		
		if($_FILES["image"]["name"] <> ''){
			//die('here');
			$uploaddir = ROOT_PATH.'files/images/link/';			

					$image = new ImageClass();
				   	$image->load($_FILES["image"]["tmp_name"]);
				   	$image->resize(165,110);
					$image->save($uploaddir.$_FILES["image"]["name"]);

					if(!file_exists($uploaddir.$_FILES["image"]["name"])){
						echo "<script>alert('upload gagal');</script>";
						die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/link'>");	
					}else{
						$qadd ="image = '".$this->scr->filter($_FILES["image"]["name"])."',";
					}
		}		
			

		$sql = "INSERT INTO link 
				SET 
					title = '".$this->scr->filter($_POST['title'])."',
					link = '".$this->scr->filter($_POST['link'])."',
					$qadd
					postdate = now()";	

		$this->db->query($sql);
		
		echo "<script>
					alert('data tersimpan');
					window.location.href='".ROOT_URL."giadmin/link';
			 </script>";
	}
	function Update(){
		# query insert
		$datausr = $this->auth->getDetail();

		$sql = "SELECT * FROM link WHERE idlink='".$this->scr->filter($_POST['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$uploaddir = ROOT_PATH.'files/images/link/';		

		if($_FILES["image"]["name"] <> ''){
			//die('here');
			$uploaddir = ROOT_PATH.'files/images/link/';
			@unlink($uploaddir.$data['image']);			

					$image = new ImageClass();
				   	$image->load($_FILES["image"]["tmp_name"]);
				   	$image->resize(165,110);
				
					if(!$image->save($uploaddir.$_FILES["image"]["name"])){
						echo "<script>alert('upload gagal');</script>";
						die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/link'>");	
					}else{
						$qadd ="image = '".$this->scr->filter($_FILES["image"]["name"])."',";
					}
		}		
		
		$sql = "UPDATE link 
				SET 
					title = '".$this->scr->filter($_POST['title'])."',
					link = '".$this->scr->filter($_POST['link'])."',
					$qadd
					postdate = now()
				WHERE
					idlink = '".$this->scr->filter($_POST['id'])."'";			
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/link'>";	
	}


	function Delete(){
		# query delete
		$sql = "SELECT * FROM link WHERE idlink='".$this->scr->filter($_GET['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$uploaddir = ROOT_PATH.'files/images/link/';
		if($data['image'] <> '' && file_exists($uploaddir.$data['image'])){
			@unlink($uploaddir.$data['image']);
		}

		$sql = "DELETE FROM link WHERE idlink='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/link'>";
	}

	function Manage(){
		# grid & manajemen data
		$sql = "SELECT * FROM link ORDER BY idlink";
		$res = $this->db->query($sql);
		$field = array('title','link');
		$this->title = 'Link Banner';		
		$option = array('editing'=>'1','adding'=>'1','deleting'=>'1','class'=>'grid');
		$this->content = $this->grid->init($res,'idlink',$field,$option);
	}
	function FrontDisplay(){
		# tampilan depan		
		$sql = "SELECT * FROM link ORDER BY idlink limit 0,10";
		$res = $this->db->query($sql);
		
		$i = 1;
		while($data = $this->db->fetchAssoc($res)){
			if($data['title'] <> ''){
					$content .= "
								<!-- icon -->
                                        <li class=\"icon clearfix\">
                                            <a href=\"".$data['link']."\"  target='_blank'>
                                            <img src=\"".ROOT_URL."files/images/link/".$data['image']."\" alt=\"".$data['link']."\">
                                            </a>
                                        </li>
                                <!-- icon -->
								";
			}
		}

		return $content;			                    
						
	}
	function FrontList(){
		# tampilan daftar link	
		$sql = "SELECT * FROM link ORDER BY idlink DESC ";
		$res = $this->db->query($sql);

		$i = 0;
		while($tmpdata = mysql_fetch_array($res)){
				$data[$i] = $tmpdata;
				$i++;
		}

		$this->pgn = new PaginateClass($i,12,5, $linkformat = ROOT_URL."galeri/{pg}/{pgs}/pages.htm");
				
		$pgstart = $this->pgn->indexstart;
		$pgend = $this->pgn->indexend;

		

        $content = "<div id='ulSorList'>";                                
		for($i = $pgstart; $i< $pgend; $i++){
				$no = $i +1;
				if($data[$i][0] <> ''){
					if($data[$i]['video'] <>''){
						$galeritarget = $data[$i]['video'];
						$galeritumb = THEME_URL."images/film-icon.png";
					}else{
						$galeritarget = ROOT_URL."files/images/galeri/".$data[$i]['image'];
						$galeritumb = ROOT_URL."files/images/galeri/".$data[$i]['image'];
					}
					
					$content .="
					<div style='display: inline-block;  opacity: 1;' class='mix category_$no col-lg-3 col-md-3 col-sm-6 mix_all' data-cat='$no'>
                            <div class='w-box inverse'>
                                <div class='figure'>
                                    <img alt='' src='$galeritumb' class='img-responsive'>
                                    <div class='figcaption bg-2'></div>
                                    <div class='figcaption-btn'>
                                        <a href='$galeritarget' class='btn btn-xs btn-one theater'><i class='fa fa-plus-circle'></i> Zoom</a>
                                        
                                    </div>
                                </div>
                                <div class='row'>
                                    <div class='col-xs-9'>
                                        <h2>".$data[$i]['title']."</h2>
                                        <small>".$data[$i]['content']."</small>
                                    </div>
                                    
                                </div>
                            </div>
                    </div>
					";
					
               
				}
		}

		$content .= "</div>";
		$content .= $this->pgn->pagedisplay;

		
		$this->menu = new MenuClass;
		$this->testimonial = new TestimonialClass;	
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'pagetitle'	=> 'Galeri',
						 'pagecontent'	=> $content,
						 'testimonial'	=> $this->testimonial->FrontDisplay(),
						 'ipadmenu'		=> $this->menu->ipadmenu,
						 'sidebarmenu'	=> $this->menu->sidebarmenu,
						 'footermenu'	=> $this->menu->footermenu,
						 'home'			=> ROOT_URL,
						 'tweetacc' 	=> TWEET_ACC,
						 'fbacc' 		=> FB_ACC,
						 'googleacc' 	=> GOOGLE_ACC,
						 'contactaddr' 	=> CONTACT_ADDR,
						 'contacttelp' 	=> CONTACT_TELP,
						 'contactfax' 	=> CONTACT_FAX,
						 'contactemail' => CONTACT_EMAIL,
						 'hotline' 		=> HOTLINE,					 						 
				 		 'themepath'  	=> THEME_URL,
                );
		$this->template->init(THEME.'/single.html');
		$this->template->defineTag($define);
		$this->template->printTpl(); 	
	}	

	function GetDetail($id){
		# detail artikel
		
	}

}