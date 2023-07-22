<?php
Class HelpClass extends ModulClass{

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
		# daftar artikel
		// opt kelompok
		$faq_list = array(
			'Apa itu Keerom Pu Data ?' => 'Keerom Pu Data adalah Wadah yang berfungsi sebagai pengelola data dan informasi pembangunan daerah. Selain itu Keerom Pu Data berfungsi untuk :
			<ul>
				<li>Menjembatani jaringan-jaringan dengan pemangku kepentingan pembangunan</li>
				<li>Mendukung dipergunakannya data dan informasi tersebut oleh pemerintah daerah untuk memperbaiki kualitas pengelolaan keuangan daerah</li>
				<li>Mendorong pengembangan kapasitas dan secara proaktif menyebarkan data dan informasi (terutama dokumen-dokumen perencanaan dan penganggaran) kepada publik dan pemerintah. </li>
			</ul>',
			'Bagaimana Keerom Pu Data dapat membantu ?' => 'Keerom Pu Data Memberikan Pelayanan bagi Masyarakat, Pers, Perguruan Tinggi ,CSO, dll sebagai berikut : 
			<ul>
				<li>Menyediakan fasilitas ruang pertemuan gratis</li>
				<li>Penyediaan media atau tempat bertukar pengetahuan</li>
				<li>Layanan Internet Gratis untuk Akses data dan informasi pembangunan yang relevan</li>
				<li>memfasilitasi jaringan dan olaborasi antar organisasi dan individu</li>
			</ul>',
			'Bisakah Data di Keerom Pu Data di Download atau di Export dalam Bentuk Dokumen Lainnya ?' => '<b>Bisa</b>, tetapi untuk saat ini fitur tersebut belum tersedia',
			'Bagaimana cara mendapatkan data dari Pusdalisbang' => 'buka halaman <a href="'.ROOT_URL.'kontak/"><b>Kontak</b></a> lalu kirim pesan kepada Keerom Pu Data, atau dengan mendatangi langsung kantor Keerom Pu Data, dengan alamat yang terdapat pada halaman <a href="'.ROOT_URL.'kontak/"><b>kontak</b></a> tersebut',
		);	
		$content = '
		<div class="panel-group col-sm-12">
  		<div class="box box-success"  id="sub_element">
	            <!-- /.box-header -->   
	            <div class="box-body" style="min-height:450px;overflow-y:auto;overflow-x:hidden;">
					<div id="faq_content">
  		<ol style="list-style: none;">';

		foreach ($faq_list as $key => $value) {
			$content .= "<li>
			<UL class='faq_list'>
			<div class='question'> {$key}
			</div><div><i class='fa fa-commenting-o'></i> &nbsp;{$value}</div>
			</UL>
			</li>";
		}

		$this->pgScript = "<script>
  			$(document).ready(function(){
  				$('.faq_list').find('div.question').addClass('h3');
  			});
  		</script>";

		$content .= "</ol></div>
	            </div>
	            <!-- /.box-body -->         
	    </div>
  		</div>
  		";	

		$this->menu = new MenuClass;
		$this->link = new LinkClass;
		$this->berita = new BeritaClass;
		$this->slider = new SliderClass;
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
						 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> '<b>F.A.Q</b><br><small>FREQUENTLY ASKED QUESTION</small>',
						 'pagecontent'	=> $content,
						 'pagescript'	=> $this->pgScript,
						 'sidenews'		=> $this->sidenews,		
						 'link'			=> $this->link->FrontDisplay(),	
						 'latestnews'	=> $this->berita->LatestNews(),	
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

}
