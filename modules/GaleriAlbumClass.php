<?php
Class GaleriAlbumClass extends ModulClass{

	function buildForm(){
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM galeri_album WHERE idalbum='".$_GET['id']."'";
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
		$this->title = 'album';

		$define = array (
						 'album'	=> $data['album'],				
						 'keterangan'	=> $data['keterangan'],
						 // 'image'	=> $data['image'],
						 // 'video'	=> $data['video'],
						 'postdate'	=> $data['postdate'],
						 'id' 		=> $data['idalbum'],
						 'status' 	=> $status,
						 'rootdir' 	=> ROOT_URL,
						 'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/galeri_album.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	
		return $form; 
	}
	function Insert(){
		# query insert
		$datausr = $this->auth->getDetail();			

		$sql = "INSERT INTO galeri_album 
				SET 
					album = '".$this->scr->filter($_POST['album'])."',
					keterangan = '".$this->scr->filter($_POST['keterangan'])."',
					author = '".$datausr['username']."',
					postdate = now()";			
		// die($sql);
		$this->db->query($sql);
		
		echo "<script>
					alert('data tersimpan');
					window.location.href='".ROOT_URL."giadmin/galerialbum';
			 </script>";
	}

	function Update(){
		# query insert
		$datausr = $this->auth->getDetail();

		$sql = "SELECT * FROM galeri_album WHERE idalbum='".$this->scr->filter($_POST['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);
		
		$sql = "UPDATE galeri_album 
				SET 
					album = '".$this->scr->filter($_POST['album'])."',
					keterangan = '".$this->scr->filter($_POST['keterangan'])."',
					author = '".$datausr['username']."',
					postdate = now()
				WHERE
					idalbum = '".$this->scr->filter($_POST['id'])."'";			
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/galerialbum'>";	
	}
	function Delete(){
		# query delete
		$sql = "SELECT * FROM galeri_album WHERE idalbum='".$this->scr->filter($_GET['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$sql = "DELETE FROM galeri_album WHERE idalbum='".$this->scr->filter($_GET['id'])."'";

		$uploaddir = ROOT_PATH.'files/images/galeri/';

		if ($this->db->query($sql)) {
			// delete file-table
			$sql_file = "SELECT image FROM galeri WHERE idalbum='".$data['idalbum']."'"; //load files
			$res_file = $this->db->query($sql_file);
			while ($data_file = $this->db->fetchAssoc($res_file)) {
				@unlink($uploaddir.$data_file['image']);
				@unlink($uploaddir.'thumb/'.$data_file['image']);
			}

			$sql_delfile = "DELETE FROM galeri WHERE idalbum='".$data['idalbum']."'";
			$res_delfile = $this->db->query($sql_delfile);

			echo "<script>alert('album berhasil dihapus');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/galerialbum'>";
		} else {
			echo "<script>alert('album gagal dihapus');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/galerialbum'>";
		}
	}
	function Manage(){
		# grid & manajemen data

		$link1 = '<a href="'.ROOT_URL.'giadmin/galeri?album=';
		$link2 = '"><button class="btn btn-info"><i class="fa fa-list"></i> Lihat Foto</button></a>';
		$link3 = '<a href="'.ROOT_URL.'giadmin/galeri/form.htm?album=" style="display:none;"><button class="btn btn-success"><i class="fa fa-plus"></i> Tambah Foto</buttton></a>';

		$sql = "SELECT *,CONCAT('{$link1}',idalbum,'{$link2}') AS link FROM galeri_album ORDER BY postdate DESC";
		//$sql = "SELECT *,CONCAT('{$link1}',idalbum,'{$link2}',idalbum,'{$link3}') AS link FROM galeri_album ORDER BY postdate DESC";
		$res = $this->db->query($sql);

		$field = array('Album' => 'album','Galeri' => 'link', 'Keterangan' => 'keterangan','tanggal post' => 'postdate');
		$this->title = 'Galeri Album';		
		$this->content = $this->grid->init($res,'idalbum',$field);
	}
	function FrontDisplay(){
		// # tampilan depan		
		// $sql = "SELECT * FROM galeri ORDER BY idgaleri DESC limit 0,4";
		// $res = $this->db->query($sql);
		
		// $i = 1;
		// while($data = $this->db->fetchAssoc($res)){
		// 	if($data['title'] <> ''){
		// 		$content .= "<div class='col-md-3'>
		//                         <div class='w-box inverse'>
		//                             <div class='figure'>
		//                                 <img alt='' src='".ROOT_URL."files/images/galeri/".$data['image']."' class='img-responsive'>
		//                                 <div class='figcaption bg-2'></div>
		//                                 <div class='figcaption-btn'>
		//                                     <a href='files/images/galeri/".$data['image']."' class='btn btn-xs btn-one theater'><i class='fa fa-plus-circle'></i> Zoom</a>      
		//                                 </div>
		//                             </div>
		//                             <div class='row'>
		//                                 <div class='col-xs-9'>
		//                                     <h2>".$data['title']."</h2>
		//                                     <small>".$data['content']."</small>
		//                                 </div>
		//                                 <div class='col-xs-3'>
		                                    
		//                                 </div>
		//                             </div>
		//                         </div>
		//                     </div>";
		// 	}
		// }

		// return $content;			                    
						
	}

	function FrontList(){

  //       $this->pgScript = "
	 //  		<!-- use jssor.slider.debug.js instead for debug -->
		// 	<script type='text/javascript' src='{themepath}plugins/jssor.slider/jssor.slider.mini.js'></script>
		// 	<script type='text/javascript' src='{themepath}js/galeri.js'></script>
		// 	<link rel='stylesheet' href='{themepath}css/galeri.css'></style>
		// ";

		// $this->pgContent = '<style>
		// 	.jssora02l, .jssora02r {
		// 	    display: block;
		// 	    position: absolute;
		// 	    /* size of arrow element */
		// 	    width: 55px;
		// 	    height: 55px;
		// 	    cursor: pointer;
		// 	    background: url(\'{themepath}plugins/jssor.slider/img/a02.png\') no-repeat;
		// 	    overflow: hidden;
		// 	}
		// </style>
		// ';

  //       // read output folder
		// $galeri_folder = ROOT_PATH."files/images/galeri";
		// $galeri_folder_url = ROOT_URL."files/images/galeri";

		// $gallery_path 	= $galeri_folder;
		// $gallery_path_url 	= $galeri_folder_url.'/';

		// $idalbum = $_GET['kat'];
		// if ($idalbum == '') {
		// // jika tidak ada album dipilih, tampilkan pilihan album
		// 	$sql = "SELECT * FROM galeri_album ORDER BY updatedate DESC ";
		// 	$res = $this->db->query($sql);

		// 	$listalbum_content = "";
		// 	while ($albumdata = $this->db->fetchAssoc($res)) {
		// 		// get latest photo
		// 		$sql_cover = "SELECT * FROM galeri WHERE idalbum = {$albumdata['idalbum']} ORDER BY postdate DESC LIMIT 1";
		// 		$res_cover = $this->db->query($sql_cover);
		// 		$data_cover = $this->db->fetchAssoc($res_cover);

		// 		// $listalbum_content .= "{$albumdata['album']} {$data_cover['image']}";

		// 		$listalbum_content .= '<div class="clearfix col col-lg-3 col-md-4 col-sm-6 col-xs-12">
  //                 	<div class="attachment-text"><i class="fa fa-clock-o"></i> '.$data_cover['postdate'].'</div>
	 //                <img class="" width="200" src="'.$gallery_path_url.$data_cover['image'].'" alt="Attachment Image">

	 //                <div class="attachment-pushed">
	 //                  <h4 class="attachment-heading"><a href="'.ROOT_URL."galeri/{$albumdata['idalbum']}".'.htm">'.$albumdata['album'].'</a></h4>

	 //                  <div class="attachment-text">'.$albumdata['keterangan'].'</div>
	 //                  <!-- /.attachment-text -->
	 //                </div>
	 //                <!-- /.attachment-pushed -->
  //             	</div>
  //             	';
		// 	}

		// 	$this->pgContent .= "
		// 	<div class='panel-group col-sm-12 col-md-12'>
	 //  		<div class='box box-success'  id='output_content'>
	 //  			<div class='box-header with-border align-center'>
	 //  				<h4>GALERI ALBUM PUSDALISBANG PROVINSI PAPUA</h4>
	 //  			</div> 
	 //  			<div class='box-body text-center'>
	 //  				{$listalbum_content}
	 //  			</div>
		//     </div>
		//     </div>
		//     ";

		// } else {
		// // album dgn id tertentu dipilih
		// 	# tampilan daftar galeri	
		// 	$sql = "SELECT * FROM galeri WHERE idalbum='{$idalbum}' ORDER BY idgaleri DESC ";
		// 	$res = $this->db->query($sql);

		// 	$pictures = "";

	 //        while ($file_galeri = $this->db->fetchAssoc($res)) {
		// 		if (is_file($gallery_path."/".$file_galeri['image'])) {
		// 			$pictures .= '<div data-p="112.50" style="display: none;">
		// 								<div style="position: relative; z-index:2000; top: 220px; left: 2220px; width: 120px; height: 120px;">
		// 						            Your text here adsds dd dsd
		// 						        </div>
		// 				                <img data-u="image" src="'.$gallery_path_url.$file_galeri['image'].'" />
		// 				                <img data-u="thumb" src="'.$gallery_path_url.$file_galeri['image'].'" />
		// 						        <div u="caption" t="CLIP|LR"  style="position:absolute; left:20px; top: 10px; width:300px; height:30px; color:#fff; background-color=#000;"> 
		// 							        <h3>'.$file_galeri['title'].'</h3>
		// 							        '.$file_galeri['content'].'
		// 							    </div>
		// 				            </div>';
		// 		}
		// 	}

	 //    	$slider_content = '
		// 		<div id="jssor_1" class="jssor-container" style="position: relative; margin: 0 auto; top: 0px; left: 0px; width: 640px; height: 586px; overflow: hidden; visibility: hidden;">
		// 		        <!-- Loading Screen -->
		// 		        <div data-u="loading" style="position: absolute; top: 0px; left: 0px;">
		// 		            <div style="filter: alpha(opacity=70); opacity: 0.7; position: absolute; display: block; top: 0px; left: 0px; width: 100%; height: 100%;"></div>
		// 		            <div style="position:absolute;display:block;background:url(\'{themepath}plugins/jssor.slider/img/loading.gif\') no-repeat center center;top:0px;left:0px;width:100%;height:100%;"></div>
		// 		        </div>
		// 		        <div data-u="slides" class="jssor-pictures" style="cursor: default; position: relative; top: 0px; left: 0px; width: 640px; height: 480px; overflow: hidden;">
		// 		            '.$pictures.'
		// 		            <a data-u="ad" href="http://www.jssor.com" style="display:none">Bootstrap Slider</a>
				        
		// 		        </div>
		// 		        <!-- Thumbnail Navigator -->
		// 		        <div u="thumbnavigator" class="jssort03 jssor-navigator" style="position:absolute;left:0px;bottom:0px;width:640px;height:120px;" data-autocenter="1">
		// 		            <div style="position: absolute; top: -12px; left: 0; width: 100%; height:100%; background-color: #000; filter:alpha(opacity=30.0); opacity:0.3;"></div>
		// 		            <!-- Thumbnail Item Skin Begin -->
		// 		            <div u="slides" style="cursor: default;">
		// 		                <div u="prototype" class="p">
		// 		                    <div class="w">
		// 		                        <div u="thumbnailtemplate" class="t"></div>
		// 		                    </div>
		// 		                    <div class="c"></div>
		// 		                </div>
		// 		            </div>
		// 		            <!-- Thumbnail Item Skin End -->
		// 		        </div>
		// 		        <!-- Arrow Navigator -->
		// 		        <span data-u="arrowleft" class="jssora02l" style="top:0px;left:8px;width:55px;height:55px;" data-autocenter="2"></span>
		// 		        <span data-u="arrowright" class="jssora02r" style="top:0px;right:8px;width:55px;height:55px;" data-autocenter="2"></span>
		// 		    </div>
	 //    	';

	 //    	$sql = "SELECT * FROM galeri_album ORDER BY updatedate DESC "; //WHERE idalbum NOT IN ({$idalbum})
		// 	$res = $this->db->query($sql);

		// 	$listalbum_content = "";
		// 	while ($albumdata = $this->db->fetchAssoc($res)) {
		// 		// get latest photo

		// 		if ($albumdata['idalbum'] == $idalbum) {
		// 			$album_titile = $albumdata['album'];
		// 			$album_date = '<i class="fa fa-clock-o"></i> '.$data_cover['postdate'];
		// 			$album_note = $albumdata['keterangan'];
		// 		} else {
		// 			$sql_cover = "SELECT * FROM galeri WHERE idalbum = {$albumdata['idalbum']} ORDER BY postdate DESC LIMIT 1";
		// 			$res_cover = $this->db->query($sql_cover);
		// 			$data_cover = $this->db->fetchAssoc($res_cover);

		// 			$listalbum_content .= '<div class="attachment-block clearfix">
		//                 <img class="attachment-img" width="200" src="'.$gallery_path_url.$data_cover['image'].'" alt="Attachment Image">
		//                 <div class="attachment-pushed">
		//                   <h4 class="attachment-heading"><a href="'.ROOT_URL."galeri/{$albumdata['idalbum']}".'.htm">'.$albumdata['album'].'</a></h4>
		//                   <div class="attachment-text">'.$albumdata['keterangan'].'</div>
		//                   <!-- /.attachment-text -->
		//                 </div>
		//                 <!-- /.attachment-pushed -->
	 //              	</div>
	 //              	';
		// 		}

		// 	}

		// 	$this->pgContent .= "
		// 	<div class='panel-group col-sm-12 col-md-12'>
	 //  		<div class='box box-success'  id='output_content'>
	 //  			<div class='box-body'>
	 //  				<div class='col col-md-8'>
	 //  					<div class=''><div class='pull-right'>{$album_date}</div><h3><i class='fa fa-photo '></i> {$album_titile}</h3></div>
		//   				{$slider_content}
	 //  					<div class='text-center'>{$album_note}</div>
	 //  					<br>
	 //  				</div>
	 //  				<div class='col col-md-4' style='border-left:1px solid #ddd; height:80%;'>
	 //  					<div class='text-center'><h4>Album Lainnya</h4></div>
		//   				{$listalbum_content}
	 //  				</div>
	 //  			</div>
		//     </div>
		//     </div>
		//     ";
		// }
		
		// $this->menu = new MenuClass;
		// // $this->link = new LinkClass;
		// // $this->berita = new BeritaClass;
		// // $this->slider = new SliderClass;
		// // $this->agenda = new AgendaClass;
		// // $this->FrontDisplay();
		// $this->user = new UserClass;
		// // $this->agenda->FrontDisplay();
		
		// $define = array (
		// 				 'sitetitle' 	=> SITE_TITLE,	
		// 				 'sitekey' 		=> SITE_KEY,
		// 				 'sitedesc' 	=> SITE_DESC,
		// 				 'menu'			=> $this->menu->FrontDisplay(),
		// 				 'menufooter'	=> $this->menu->FrontDisplay('B'),
		// 				 // 'slider'		=> $this->slider->FrontDisplay(),
		// 				 'pagetitle'	=> 'GALERI PUSDALISBANG',
		// 				 'pagecontent'	=> $this->pgContent,
		// 				 'pagescript'	=> $this->pgScript,
		// 				 // 'sidenews'		=> $this->sidenews,		
		// 				 // 'link'			=> $this->link->FrontDisplay(),	
		// 				 // 'latestnews'	=> $this->berita->LatestNews(),	
		// 				 'account_menu'	=> $this->user->AccountMenu(),

		// 				 'home'			=> ROOT_URL,
		// 				 'tweetacc' 	=> TWEET_ACC,
		// 				 'fbacc' 		=> FB_ACC,
		// 				 'googleacc' 	=> GOOGLE_ACC,
		// 				 'contactaddr' 	=> CONTACT_ADDR,
		// 				 'contacttelp' 	=> CONTACT_TELP,
		// 				 'contactweb' 	=> CONTACT_WEB,
		// 				 'contactfb' 	=> FB_ACC,
		// 				 'contactfax' 	=> CONTACT_FAX,
		// 				 'contactemail' => CONTACT_EMAIL,
		// 				 'hotline' 		=> HOTLINE,					 						 
		// 		 		 'themepath'  	=> THEME_URL,
  //               );

		// $this->template->init(THEME.'/detail.html');
		// $this->template->defineTag($define);
		// $this->template->printTpl(); 	
	}	

	function GetDetail($id){
		# detail artikel
		
	}

}