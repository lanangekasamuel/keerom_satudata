<?php
Class SliderClass extends ModulClass{

	function buildForm(){
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM slider WHERE idslider='".$_GET['id']."'";
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';					
		}				
		else{
			$status ='tambah';
		}

		#build form
		$this->title = 'Slider';

		$define = array (
						'image'	=> $data['image'], 
						'title'	=> $data['title'], 
						'content'	=> $data['content'], 
						
						'id' 		=> $data['idslider'],
						'status' 	=> $status,
						'rootdir' 	=> ROOT_URL,
						'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/slider.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	
		return $form; 
	}
	function Insert(){
		# query insert
		$datausr = $this->auth->getDetail();

		if($_FILES["image"]["name"] <> ''){

			$imgname = strtolower($_FILES["image"]["name"]);
			
			$uploaddir = ROOT_PATH.'files/images/slider/';
			$file_parts = pathinfo($imgname);

			if(getimagesize($_FILES["image"]["tmp_name"])){
				if(!move_uploaded_file($_FILES["image"]["tmp_name"], $uploaddir. $imgname)){
					echo "<script>alert('upload gagal');</script>";
					die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>");	
				}else{
					$qadd ="image = '".$this->scr->filter($imgname)."',";
					// create thumbnail
					$img = new ImageClass;
					$img->load($uploaddir. $imgname);
					$img->resize(272,150);
					$arrimg = explode('.', $imgname);
					$img->save($uploaddir.$arrimg[0].'_thumb.'.$arrimg[1]);
				}
			}else{
				echo "<script>alert('format file tidak valid, \n gunakan file berformat pdf,doc,docs,xls, atau xlsx');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>");
			} # end get size
		}

		$sql = "INSERT INTO slider 
				SET 
					$qadd
					title = '".$this->scr->filter($_POST['title'])."', 
					content = '".$this->scr->filter($_POST['content'])."'
				";													
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		// echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>";
	}
	function Update(){
		# query insert
		$datausr = $this->auth->getDetail();

		if($_FILES["image"]["name"] <> ''){
			
			$imgname = strtolower($_FILES["image"]["name"]);			
			$uploaddir = ROOT_PATH.'files/images/slider/';
			$file_parts = pathinfo($imgname);
			
			if(getimagesize($_FILES["image"]["tmp_name"])){
				
				$sql = "SELECT * FROM slider WHERE idslider='".$this->scr->filter($_POST['id'])."'";
				$res = $this->db->query($sql);
				$data = $this->db->fetchAssoc($res);

				$uploaddir = ROOT_PATH.'files/images/slider/';
				if(file_exists($uploaddir.$data['image'])){
					@unlink($uploaddir.$data['image']);
					$arroldimg = explode('.', $data['image']);
					@unlink($uploaddir.$arroldimg[0].'_thumb.'.$arroldimg[1]);					
				}	
				
				if(!move_uploaded_file($_FILES["image"]["tmp_name"], $uploaddir. $imgname)){
					echo "<script>alert('upload gagal');</script>";
					die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>");	
				}else{
					$qadd ="image = '".$this->scr->filter($imgname)."',";
					// create thumbnail
					$img = new ImageClass;
					$img->load($uploaddir. $imgname);
					$img->resize(272,150);
					$arrimg = explode('.', $imgname);
					$img->save($uploaddir.$arrimg[0].'_thumb.'.$arrimg[1]);
				}
			}else{
				echo "<script>alert('format file tidak valid, \n gunakan file berformat pdf,doc,docs,xls, atau xlsx');</script>";
				die("<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>");
			} # end get size
		}

		$sql = "UPDATE slider 
				SET 
					$qadd
					title = '".$this->scr->filter($_POST['title'])."', 
					content = '".$this->scr->filter($_POST['content'])."'
				WHERE
					idslider = '".$this->scr->filter($_POST['id'])."'";			
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		// echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>";	
	}
	function Delete(){
		# query delete 
		$sql = "SELECT * FROM slider WHERE idslider='".$this->scr->filter($_GET['id'])."'";
		$res = $this->db->query($sql);
		$data = $this->db->fetchAssoc($res);

		$uploaddir = ROOT_PATH.'files/images/slider/';
		if(file_exists($uploaddir.$data['image'])){
			@unlink($uploaddir.$data['image']);
			$arroldimg = explode('.', $data['image']);
			@unlink($uploaddir.$arroldimg[0].'_thumb.'.$arroldimg[1]);		
		}	

		$sql = "DELETE FROM slider WHERE idslider='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/slider'>";
	}
	function Manage(){
		# grid & manajemen data
		$imgurl = ROOT_URL.'files/images/slider/';
		$tagopen = "<img width=\"200px\" height=\"100px\" src=\"".$imgurl;
		$tagclose = "\">";
		$sql = "SELECT *,concat(concat('$tagopen',image),'$tagclose') as preview FROM slider ORDER BY idslider DESC";
		$res = $this->db->query($sql);
		$field = array('preview','title','content');
		$this->title = 'Slider';		
		$this->content = $this->grid->init($res,'idslider',$field,array('editing'=>'1','adding'=>'1','deleting'=>'1','class'=>'grid', 'previous'=>'0', 'updown' => '0'));
	}
	function FrontDisplay(){
		# tampilan depan		
		$sql = "SELECT * FROM slider ORDER BY idslider DESC";
		$res = $this->db->query($sql);
		
		$indicator_content = $inner_content = "";
		// dibuthkan
		$item_class = array(1=>'active left','next left','','','','','');
		$indikator_class = array(1=>'active','','','','','','');

		$x=1;
		while($data = $this->db->fetchAssoc($res)){		
			$arrimagename = explode('.', $data['image']);
			$imagename = $arrimagename[0];

			$img = ROOT_URL."files/images/slider/".$data['image'];
			$imgthumb = ROOT_URL."files/images/slider/".$imagename."_thumb.jpg";

			// $slideStyle = array(
			// 					"text bottom left",
   //                              "text bottom right",
   //                              "text bottom left no-side-padding",
   //                              "text bottom-full-width"
   //                              );
			$indikator_content .= '<li data-target="#carousel-example-generic" data-slide-to="'.$x.'" class="'.$indikator_class[$x].'"></li>';
			$inner_content .= '<div class="item '.$item_class[$x].'">
                    <img src="'.$img.'" alt="'.strip_tags($data['title']).'">
                    <div class="carousel-caption">
                       <h3 class=" f-sansa">'.strip_tags($data['title']).'</h3>
                    </div>
                  </div>';

                  $x++;
		}

		$content ='<ol class="carousel-indicators">
                  '.$indikator_content.'
                </ol>
                <div class="carousel-inner">
                  '.$inner_content.'
                </div>';		

		return 	$content;				
	}
	
	function FrontList(){
		# tampilan daftar artikel		
	}	
	function GetDetail($id){
		# detail artikel
	}

}