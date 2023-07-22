<?php
Class BeritaClass extends ModulClass{

	function buildForm(){
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM berita WHERE idberita='".$_GET['id']."'";
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
		$this->title = 'Berita';

		$define = array (
						 'title'	=> $data['title'],				
						 'content'	=> $data['content'],
						 'image'	=> $data['image'],
						 'postdate'	=> $data['postdate'],
						 'id' 		=> $data['idberita'],
						 'status' 	=> $status,
						 'rootdir' 	=> ROOT_URL,
						 'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/berita.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	
		return $form; 
	}
	function Insert(){
		# query insert
		$datausr = $this->auth->getDetail();

		if($_FILES["image"]["name"] <> ''){
			// die('here');
			
			$imgname = strtolower($_FILES["image"]["name"]);
			$uploaddir = ROOT_PATH.'files/images/berita/';
			$file_parts = pathinfo($imgname);
			if(getimagesize($_FILES["image"]["tmp_name"])){
				if(!move_uploaded_file($_FILES["image"]["tmp_name"], $uploaddir. $imgname)){
					echo "<script>alert('upload gagal');</script>";
					die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/berita'>");	
				}else{
					$qadd ="image = '".$this->scr->filter($imgname)."',";
					$img = new ImageClass;
					$img->load($uploaddir. $imgname);
					$img->resize(272,196);
	
					$img->save($uploaddir.$imgname);
				}
			}else{

				echo "<script>alert('gambar tidak valid');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/berita'>");
			} # end get size
		}	
			

		$sql = "INSERT INTO berita 
				SET 
					$qadd
					title = '".$this->scr->filter($_POST['title'])."',
					content = '".$this->scr->filter($_POST['content'])."',
					postdate = now(),
					author = '".$this->scr->filter($datausr['username'])."'";			
		$this->db->query($sql);
		
		echo "<script>
					alert('data tersimpan');
					window.location.href='".ROOT_URL."giadmin/berita';
			 </script>";
	}
	function Update(){
		# query insert
		$datausr = $this->auth->getDetail();

		$sql = "SELECT * FROM berita WHERE idberita='".$this->scr->filter($_POST['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$uploaddir = ROOT_PATH.'files/images/berita/';
		if($_FILES["image"]["name"] <> ''){// && file_exists($uploaddir.$data['image'])
			
			@unlink($uploaddir.$data['image']);

			// print_r($_FILES);
			// die();

			$imgname = strtolower($_FILES["image"]["name"]);
			//$uploaddir = ROOT_PATH.'files/images/berita/';
			$file_parts = pathinfo($imgname);
			if(getimagesize($_FILES["image"]["tmp_name"])){
				if(!move_uploaded_file($_FILES["image"]["tmp_name"], $uploaddir. $imgname)){
					echo "<script>alert('upload gagal');</script>";
					die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/berita'>");	
				}else{
					
					$qadd ="image = '".$this->scr->filter($imgname)."',";
					$img = new ImageClass;
					$img->load($uploaddir. $imgname);
					$img->resize(272,196);
	
					$img->save($uploaddir.$imgname);
				}
			}else{
				echo "<script>alert('gambar tidak valid');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/berita'>");
			} # end get size
		}
		
		
		$sql = "UPDATE berita 
				SET 
					$qadd
					title = '".$this->scr->filter($_POST['title'])."',
					content = '".$this->scr->filter($_POST['content'])."',
					postdate = now(),
					author = '".$this->scr->filter($datausr['username'])."'
				WHERE
					idberita = '".$this->scr->filter($_POST['id'])."'";		

		// die($sql);	
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/berita'>";	
	}
	function Delete(){
		# query delete
		$sql = "SELECT * FROM berita WHERE idberita='".$this->scr->filter($_GET['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$uploaddir = ROOT_PATH.'files/images/berita/';
		if($data['image'] <> '' && file_exists($uploaddir.$data['image'])){
			@unlink($uploaddir.$data['image']);
		}

		$sql = "DELETE FROM berita WHERE idberita='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/berita'>";
	}
	function Manage(){
		# grid & manajemen data
		$sql = "SELECT * FROM berita ORDER BY idberita DESC";
		$res = $this->db->query($sql);
		$field = array('title','postdate','author');
		$this->title = 'Berita';		
		$this->content = $this->grid->init($res,'idberita',$field);
	}
	function FrontDisplay(){
		# tampilan depan	
		$sql = "SELECT * FROM berita ORDER BY postdate DESC, idberita DESC LIMIT 0,4";
		$res2 = $this->db->query($sql);
		while($dt = $this->db->fetchAssoc($res2)){
			$img = ($dt['image']<>'')?ROOT_URL."files/images/berita/".$dt['image']:ROOT_URL."files/images/berita/default.jpg";
			$url = ROOT_URL."detail/berita/".$dt['idberita']."/".$this->url->friendlyURL($dt['title']).".htm";
			
			$arrdate = explode(' ', $dt['postdate']);
			$date = $arrdate[0];
			$explodedate = explode("-", $date);
			$day = $explodedate[2];
			$month = (int) $explodedate[1];
			$arMon = array(1=>"Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agus","Sep","Okt","Nov","Des");
			$monthname = $arMon[$month];

			$content  .= "

							<!-- news item -->
                                        <div class=\"item col col-sm-4 col-md-3\">
                                            <div class=\"item-content clearfix\">
                                                <div class=\"date\">
                                                    <div class=\"day\">$day</div>
                                                    <div class=\"month\">$monthname</div>
                                                </div>
                                                <div class=\"text\">
                                                    <h4 class=\"title\">
                                                    <a href=\"$url\">".$dt['title']."</a></h4>
                                                    <p>".$this->str->getSynopsis($dt['content'],20)."</p>
                                                </div>
                                                <a href=\"$url\" class=\"read-more-arrow\"><i class='fa fa-arrow-right'></i> lebih lanjut ...</a>
                                            </div><!-- .item-content -->
                                        </div>
                            <!-- .item -->";

	
		}	


			return $content;	
	}

	function FrontList(){
		# tampilan daftar artikel
		$sql = "SELECT * FROM berita ORDER BY idberita DESC ";
		$res = $this->db->query($sql);
		$nrw = $this->db->numRows($res); 

		$pgn = new PaginateClass($nrw,5,5,ROOT_URL."berita/{pg}/{pgs}/pages.htm");
		$pgstart = $pgn->indexstart;
		$pgend = $pgn->indexend;
		$dataSource = $this->db->query($sql); 

		$i = 0;
			while($tmpdata = $this->db->fetchArray($res)){
					$data[$i] = $tmpdata;
					$i++;
			}
					
					
			for($i = $pgstart; $i< $pgend; $i++){
					if($data[$i][0] <> ''){
						$url = ROOT_URL."detail/berita/".$data[$i]['idberita']."/".$this->url->friendlyURL($data[$i]['title']).".htm";
						
						$img = ($data[$i]['image']<>'')?ROOT_URL."files/images/berita/".$data[$i]['image']:ROOT_URL."files/images/berita/default.jpg";
						$content .= "\n<article class=\"list\"> \n
											<div class=\"short-content\"> \n
												
												<h1 class=\"entry-header\"> \n
													<a title=\"".$data[$i]['title']."\" 
													   href=\"$url\">
													   ".$data[$i]['title']."
													</a> \n
												</h1> \n

												<div class=\"short-description\"> \n
													<p>".$this->str->getSynopsis($data[$i]['content'],30)."</p>
												</div> \n

												<div class=\"entry-meta\"> \n
													<time datetime=\"".$data[$i]['postdate']."\"><a class=\"buttons time fleft\" href=\"#\"><i class=\"icon-calendar\"></i> ".$data[$i]['postdate']."</a></time> 
													<a class=\"buttons author fleft\" href=\"#\"><i class=\"icon-user\"></i> ".$data[$i]['author']."</a> 
													<a class=\"buttons fright\" href=\"$url\" title=\"read more\">read more</a> \n
												</div> \n
												<div class=\"clear\"></div> \n

											</div> \n
											<div class=\"clear\"></div> \n
									</article> \n ";
							 
					}
			}

			$content .= $pgn->pagedisplay;

		
		$this->menu = new MenuClass;
		$this->link = new LinkClass;
		$this->berita = new BeritaClass;
		$this->slider = new SliderClass;
		// $this->agenda = new AgendaClass;
		//$this->FrontDisplay();
		//$this->agenda->FrontDisplay();
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'menufooter'	=> $this->menu->FrontDisplay('B'),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> 'Berita',
						 'pagecontent'	=> $content,
						 'sidenews'		=> $this->sidenews,		
						 'link'			=> $this->link->FrontDisplay(),	
						 'latestnews'	=> $this->berita->LatestNews(),	
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

	function LatestNews(){
		# detail artikel
		$sql = "SELECT * FROM berita order by postdate DESC limit 0,5";
		$res = $this->db->query($sql);

		$content = "";
		while($data = $this->db->fetchAssoc($res)){

			$arrdate = explode(' ', $data['postdate']);
			$date = $arrdate[0];
			$explodedate = explode("-", $date);
			$day = $explodedate[2];
			$month = (int) $explodedate[1];
			$arMon = array(1=>"Jan","Feb","Mar","Apr","Mei","Jun","Jul","Agus","Sep","Okt","Nov","Des");
			$monthname = $arMon[$month];

			$url = ROOT_URL."detail/berita/".$data['idberita']."/".$this->url->friendlyURL($data['title']).".htm";

			$content .= "<!-- list item -->
                            <li class=\"item clearfix latest-news\">
                                <div class=\"item-content\">
                                    <div class=\"date\">
                                        <div class=\"day latest-news\">$day</div>
                                        <div class=\"month latest-news\">$monthname</div>
                                    </div>
                                    <h6 class=\"title latest-news\">
                                    	<a href=\"$url\">".$data['title']."</a></h6>
                                </div>
                            </li>";
		}

		return $content;

	}	

	function GetDetail($id){
		# detail artikel
		$sql = "SELECT * FROM berita WHERE idberita = '".(int) $this->scr->filter($id)."'";
		$res = $this->db->query($sql);

		$data = $this->db->fetchAssoc($res);
		$data['author'] = ($data['author'] == 'admin') ? "keerompudata" : $data['author'] ;
		
		$img = ($data['image']<>'')
			?ROOT_URL."files/images/berita/".$data['image']
			:ROOT_URL."files/images/berita/default.jpg";
		$this->pgTitle = 'Berita';

		$this->pgContent = "
			<div class='col col-sm-10 col-md-9 col-xs-12'>
				<div class='box box-info'>
					<div class='box-body'>
						\n<article class=\"single\">						
								<div class=\"entry-content\">		

									<div class=\"long-description\">
										<h3>".$data['title']." </h3>
										<br>

										<img src=\"".$img."\" class=\"wp-post-image\" alt=\"\" height=\"auto\" width=\"272\" align='left'> \n
										".$data['content'].$this->url->shareThis()."
										<br>
									</div>
									
									<div class=\"clear\"></div>									
									<div class=\"entry-meta-press\">
										<time class=\"entry-date fleft\" datetime=\"".$data['postdate']."\">
											<i class=\"icon-calendar\"></i> ".$this->date->IndonesianDate($data['postdate'])."
										</time>
										<div class=\"author-i\">
											oleh : <i class=\"fa fa-user\"></i> <a href=\"#\">".$data['author']."</a>
										</div>
										<div class=\"clear\"></div>
									</div>

								</div>

							<div class=\"clear\"></div>
					
						</article>
					</div>
				</div>
				<!-- /.box -->
			</div>
			<!-- /.col col-sm-10 col-md-9 col-xs-12 -->

			<div class='col col-sm-2 col-md-3 col-xs-12'>
				<div class='box box-info'>
					<div class='box-body'>
						{latestnews}
					</div>
				</div>
				<!-- /.box -->
			</div>

					";

					
				
	}

}

?>
