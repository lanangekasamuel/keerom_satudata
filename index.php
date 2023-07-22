<?php
require __DIR__ . '/CustomLegacyLoader.php';
require __DIR__ . '/inc.php';

class cms
{
	public $loadedclass = [
		// mode (lowercase)	=> class file name				
		'AnoovDebugClass' => 'AnoovDebugClass',
		'cnf'     => 'ConfigClass',
		'db'      => 'MysqliClass',
		'counter' => 'CounterClass',
		'template'			=> 'TemplateClass',	
		'scr'				=> 'SecurityClass',				
		'berita'			=> 'BeritaClass',
		'menu'				=> 'MenuClass',	
		'search'			=> 'SearchClass',
		'analisa'			=> 'AnalisaClass',
		'analisakabupaten'	=> 'AnalisakabupatenClass',

		'progis'			=> 'ProgisClass', // Data management
		'kabupaten'			=> 'KabupatenClass', // Data Kab Management

		'data'				=> 'DataClass',
		'gerbangmas'		=> 'GerbangmasClass',

		'gis' => 'GIS_Class',

		'kelompok'			=> 'KelompokClass',	
		'konversi'			=> 'KonversiClass',	 //keperluan konversi, tidak di publish
		'urusan'			=> 'UrusanClass',	

		'skpd'     => 'SkpdClass',
		'instansi' => 'SkpdClass',

		'distrik' => 'WilayahClass',

		'map'				=> 'MapClass',
		'help'				=> 'HelpClass',	
		'kontak'			=> 'KontakClass',

		'output'			=> 'OutputClass',

		'page'				=> 'PageClass',	
		'link'				=> 'LinkClass',			
		'slider'			=> 'SliderClass',		
		'galeri'			=> 'GaleriClass',		
		'galerialbum'		=> 'GaleriAlbumClass',		
		'setting'			=> 'SettingClass',	
		'profil'			=> 'ProfilClass',	
		'galeri'			=> 'GaleriClass',
		'user'				=> 'UserClass',			
		'akses'				=> 'AksesClass'			
	];

	// public function __construct() {}

	function init()
	{
		$this->__config();
	
		switch(strtolower($_GET['mode'])){
			case 'detail':
				$this->getDetail();
			break;
			case 'ajax': //ajax operation
				$this->getJSON();
			break;
			case 'admin':
				$this->admin = new AdminClass($this->loadedclass);
			break;			
			case 'beranda':
				$this->getIndex();
			break;
			default:
				if(array_key_exists($_GET['mode'], $this->loadedclass)){
					if(($_GET['cntmode'] == 'download')){
						// untuk men-download file di masing2 modul  >>> $this->berita->FrontList();
						$this->{$_GET['mode']}->Download($this->scr->filter($_GET['id']));
					}else{
						// untuk menampilkan daftar artikel  >>> $this->berita->FrontList();					
						$this->{$_GET['mode']}->FrontList();
					}	
				}else{	
					// lainnya	
					$this->getStartPage();
				}				
			break;
		}
	}	

	function getIndex()
	{
		$chartanalisa = $this->analisa->FrontDisplay();
		$pgScript = $this->analisa->pgScript;

		$l_app_arr = array(
			// judul	=> link gerbangmas, rpjmd, data : sipd-supd-kabupaten, analisa, peta wilayah, peta tematik, dll
			'Gerbangmas' => 'gerbangmas/',
			// 'RPJMD' => 'data/rpjmd.htm', //keperluan internal
			'SIPD' => 'progis/',
			// 'SUPD' => 'data/supd.htm',
			'Analisa' => 'analisa/',
			'Tematik' => 'map/tematik.htm',
			);
		$list_aplikasi = "";
		foreach ($l_app_arr as $judul => $link) {
			# code...
			$stc_file = ROOT_PATH.'files/images/shortcut/'.strtolower($judul).'.jpg';
			$src_file = (file_exists($stc_file)) ? ROOT_URL.'files/images/shortcut/'.strtolower($judul).'.jpg' : ROOT_URL.'files/skpd/basic.png' ;
			$list_aplikasi .= '
				<li>
					<a href="'.ROOT_URL.$link.'"><img src="'.$src_file.'" alt="'.$judul.'">
						<span class="users-list-name">'.$judul.'</span>
						<span class="users-list-date"></span>
					</a>
				</li>';
		}

		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay('T'),
						 'menufooter'   => $this->menu->FrontDisplay('B'),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'galeri'		=> "",//$this->galeri->FrontDisplay(),
						 'intro'		=> $this->page->StaticDisplay(), 
						 'informasilist'	=> $this->berita->LatestNews(),
						 'informasi'	=> $this->berita->FrontDisplay(),
						 'link'			=> $this->link->FrontDisplay(),
						 'statcontent'	=> $this->counter->display(),
						 'account_menu'	=> $this->user->AccountMenu(),

						 'random_indikator'	=> $this->data->randomData(),
						 
						 'home'			=> ROOT_URL,
						 'error_tag'	=> ERROR_TAG,
						 'chartanalisa' => $chartanalisa,
						 'listaplikasi' => $list_aplikasi,
	
						 'tweetacc' 	=> TWEET_ACC,
						 'fbacc' 		=> FB_ACC,
						 'googleacc' 	=> GOOGLE_ACC,
						 'contactaddr' 	=> CONTACT_ADDR,
						 'contacttelp' 	=> CONTACT_TELP,
						 'contactfax' 	=> CONTACT_FAX,
						 'contactweb' 	=> CONTACT_WEB,
						 'contactfb' 	=> FB_ACC,
						 'contactemail' => CONTACT_EMAIL,
						 'hotline' 		=> HOTLINE,					 						 
				 		 'themepath'  	=> THEME_URL,
				 		 'pagescript' => $pgScript
                );
		$this->template->init(THEME.'/index.html');			
		$this->template->defineTag($define);
		$this->template->printTpl(); 
	}

	function getStartPage() {

		// $chartanalisa = $this->analisa->FrontDisplay();
		// $pgScript = $this->analisa->pgScript;
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay('T'),
						 'menufooter'   => $this->menu->FrontDisplay('B'),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'intro'		=> $this->page->StaticDisplay(), 
						 'informasi'	=> $this->berita->FrontDisplay(),
						 'link'			=> $this->link->FrontDisplay(),
						 'statcontent'	=> $this->counter->display(),
						 'account_menu'	=> $this->user->AccountMenu(),
						 
						 'home'			=> ROOT_URL,
						 'error_tag'	=> ERROR_TAG,
						 // 'chartanalisa' => $chartanalisa,
	
						 'tweetacc' 	=> TWEET_ACC,
						 'fbacc' 		=> FB_ACC,
						 'googleacc' 	=> GOOGLE_ACC,
						 'contactaddr' 	=> CONTACT_ADDR,
						 'contacttelp' 	=> CONTACT_TELP,
						 'contactfax' 	=> CONTACT_FAX,
						 'contactweb' 	=> CONTACT_WEB,
						 'contactfb' 	=> FB_ACC,
						 'contactemail' => CONTACT_EMAIL,
						 'hotline' 		=> HOTLINE,					 						 
				 		 'themepath'  	=> THEME_URL,
				 		 'pagescript' => $pgScript
                );
		$this->template->init(THEME.'/startpage.html');			
		$this->template->defineTag($define);
		$this->template->printTpl(); 
	}

	function getJSON(){

		if(array_key_exists($_GET['content'], $this->loadedclass)){
			// detail :: berita : BeritaClass->getDetail(id);
			$json = new $this->loadedclass[$this->scr->filter($_GET['content'])];
			$ajson = $json->getJSON($this->scr->filter($_GET['id']));
			die ($ajson);
		}else{
			new ErrorClass('Error 404 : Halaman tidak ditemukan');
		}			
	}

	function getDetail(){
		
		
		if(array_key_exists($_GET['content'], $this->loadedclass)){
			// detail :: berita : BeritaClass->getDetail(id);
			$detail = new $this->loadedclass[$this->scr->filter($_GET['content'])];
			$detail->getDetail($this->scr->filter($_GET['id']));
		}else{
			new ErrorClass('Error 404 : Halaman tidak ditemukan');
		}
 
 		//$this->berita->FrontDisplay();

		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay('T'),
						 'menufooter'   => $this->menu->FrontDisplay('B'),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> $detail->pgTitle,
						 'pagecontent'	=> $detail->pgContent,	
						 'pagescript'	=> $detail->pgScript,					 
						 'link'			=> $this->link->FrontDisplay(),
						 'latestnews'	=> $this->berita->LatestNews(),
						 'account_menu'	=> $this->user->AccountMenu(),
						 
						 'home'			=> ROOT_URL,
						 'error_tag'	=> ERROR_TAG,

						 'user_name'	=> '',
						 'user_title'	=> '',

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

	/*
	[anovedit]
	jangan initialize semua class secara bersamaan.
	initialize class jika dibutuhkan saja.
	*/
	public function __get($pk)
	{
		if (isset($this->loadedclass[$pk])) {
			if (!isset($this->{$pk})) {
				$cls = $this->loadedclass[$pk];
				$this->{$pk} = new $cls($this);
			}
			return $this->{$pk};
		}
		return null;
	}

	function __config(){		
		$sql = "SELECT * FROM conf";
		$res = $this->db->query($sql)or die(new ErrorClass('Saat ini kami sedang melakukan perbaikan Database'));
		
		while($data = $this->db->fetchArray($res)){
			$k = strtoupper($data['conf']);
			$v = $data['val'];
			if (!defined($k)) define($k, $v);
		}		
	} 


}


$gi	= new cms();

print($gi->init());