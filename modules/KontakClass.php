<?php
Class KontakClass extends ModulClass{

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

	
	function buildForm(){
		# menampilkan form
	}
	function Insert(){
		# query insert 
	}
	function Update(){
		# query update 
	}
	function Delete(){
		# query delete 
	}
	function Manage(){
		# grid & manajemen data
	}
	function FrontDisplay(){
		# tampilan depan
	}
	function FrontList(){
		# halaman kontak
		$this->pgContent = '';

		$this->pgScript = "

			<script src='https://maps.googleapis.com/maps/api/js?v=3.exp'></script>
			<script src='{themepath}js/kontak.js'></script>

			<style>
				html, body, #map-canvas {
					height: 100%;
					margin: 0px;
					padding: 0px
				}
				h6.title{
					font-weight:normal;
					display:inline-text;
				}
				div.info-box{min-height:0px; !important!;
					margin-bottom:2px;

				}
				div.info-box i.fa{
					/*min-width:50px; !important!;*/
				}
				div.info-box h5{
					line-height:18px;
				}
				div.info-box h5 span{
					color:#3c8dbc;
				}
			</style>

			";

		$contact_item = array(
			'map-marker' => CONTACT_ADDR,
			'phone' 	=> CONTACT_TELP,
			'fax' 		=> CONTACT_FAX,
			'envelope' 	=> CONTACT_EMAIL,
			'google' 	=> GOOGLE_ACC,
			'facebook' 	=> FB_ACC,
			'twitter' 	=> TWEET_ACC,
		);

		$contact_title = array(
			'map-marker' => 'Alamat',
			'phone' 	=> 'Nomor Telepon',
			'fax' 		=> 'Fax',
			'envelope' 	=> 'Email',
			'google' 	=> 'Google',
			'facebook' 	=> 'Facebook',
			'twitter' 	=> 'Twitter',
		);

		foreach ($contact_item as $icon => $value) {
			# code...
			if (!empty($value)) {
		  		$contact .= '
					<div class=" clearfix"><div class="item-content">
		                <div class="icon" >
		                    <h5 class="title"><i class="fa fa-'.$icon.'"></i>&nbsp; '.$contact_title[$icon].' :<br><span> '.$value.'</span></h5>
		                </div>
		            	</div>
		            </div>
		  		';
			}
		}

		$this->pgContent .= "

		    <div class='panel-group col-sm-8'>
	  		<div class='box box-success'  id='kontak_map'>
	  			<div class='box-body'><div id='map-canvas' style='height:250px;'></div></div>
		    </div>
		    </div>

		    <div class='panel-group col-sm-4'>

		  		<div class='col-sm-12 box box-success'  id='kontak_content'>
		  			<div class='box-body'>".$contact."</div>
			    </div>

		  		<div class='col-sm-12 box box-success'  id='div_kontak'>
		  			<div class='box-header with-border'><i class='fa fa-envelope'></i> Pesan/Kesan/Permintaan</div>
		  			<div class='box-body'>
		  			<form id='frm_kontak' name='frm_kontak' class='form-horizontal' enctype='multipart/form-data' role='form'  data-toggle='validator'>
		  				<div class='form-group'>
							<label for='nama' class='col-sm-2 control-label'>nama*</label>
							<div class='col-sm-10'>
								<input type='text' class='form-control' name='nama' id='nama' placeholder='isikan nama' value='namanamanama' required>
							</div>
						</div>
		  				<div class='form-group'>
							<label for='email' class='col-sm-2 control-label'>email*</label>
							<div class='col-sm-10'>
								<input type='email' class='form-control' name='email' id='email' placeholder='isikan email' value='namanamanama@email.com' required>
							</div>
						</div>
		  				<div class='form-group'>
							<label for='pesan' class='col-sm-2 control-label'>pesan*</label>
							<div class='col-sm-10'>
								<textarea class='form-control' name='pesan' id='pesan' placeholder='isikan pesan' rows=4 validate required>pesan saya</textarea>
							</div>
						</div>
						<div class='form-group'>
						    <div class='col-sm-offset-2 col-sm-10'>
						      <button type='submit' class='btn btn-info btn-flat'>Kirim</button>
						    </div>
						</div>	
		  			</form>
		  			</div>
			    </div>
		    
		    </div>
  		";	

		$this->menu = new MenuClass;
		// $this->link = new LinkClass;
		// $this->berita = new BeritaClass;
		// $this->slider = new SliderClass;
		// $this->agenda = new AgendaClass;
		// $this->FrontDisplay();
		$this->user = new UserClass;
		// $this->agenda->FrontDisplay();
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'menufooter'	=> $this->menu->FrontDisplay('B'),
						 // 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> 'KONTAK',
						 'pagecontent'	=> $this->pgContent,
						 'pagescript'	=> $this->pgScript,
						 // 'sidenews'		=> $this->sidenews,		
						 // 'link'			=> $this->link->FrontDisplay(),	
						 // 'latestnews'	=> $this->berita->LatestNews(),	
						 'account_menu'	=> $this->user->AccountMenu(),

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
	function GetDetail($id){
		# detail artikel
	}

	function getJSON($id){
		// ajax
		$jmode = $_GET['ajaxmode'];

		switch ($jmode) {
			case 'kirimpesan':
				return $this->_kirimPesan();
			break;
			default:
			break;
		}
	}
	private function _kirimPesan() {
		// $datausr = $this->auth->getDetail();			

		$sql = "INSERT INTO pesan 
				SET 
					nama = '".$this->scr->filter($_POST['nama'])."',
					email = '".$this->scr->filter($_POST['email'])."',
					pesan = '".$this->scr->filter($_POST['pesan'])."',
					tanggalpesan = now()";		
		// die($sql);	
		$insQuery = $this->db->query($sql);

		$message = ($insQuery) 
					? 'pesan sukses terkirim' 
					: 'pesan gagal dikirim, silakan coba lagi'.ERROR_TAG;

			$json_data = array(
				'message' 	=> $message
			);

		$jason = json_encode($json_data);
		die ($jason);
	}

}

?>
