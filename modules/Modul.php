<?php
Class ModulClass{

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

	public function __construct() {}

	protected $moduleclass_defined_modules = array(
		'module_menu_class' => 'MenuClass',
		'cnf' => 'ConfigClass',
		'db' => 'MysqliClass',
		'str' => 'StringClass',
		'scr' => 'SecurityClass',
		'date' => 'DateClass',
		'url' => 'URLClass',
		'grid' => 'DbGridClass',
		'template' => 'TemplateClass',
		'auth' => 'AuthenticationClass',
		'numen' => 'NumberingClass',
	);

	public function __get($pk)
	{
		if (isset($this->moduleclass_defined_modules[$pk])) {
			if (!isset($this->{$pk})) {
				$cls = $this->moduleclass_defined_modules[$pk];
				$this->{$pk} = new $cls($this);
			}
			return $this->{$pk};
		}
		return null;
	}

	function Init(){
			$mode = ($_GET['cntmode'] <> '')?$_GET['cntmode']:$_POST['cntmode'];
			switch($mode){
				case 'form':
					$this->content = $this->buildForm();
				break;
				case 'ins':
					$this->insert();
				break;
				case 'upd':
					$this->update();
				break;
				case 'del':
					$this->delete();
				break;			
				case 'up':
					$this->up();
				break;			
				case 'down':
					$this->down();
				break;	
				case 'view':
					$this->detail();
				break;	
				case 'ajax':
					$this->getJSON();
				break;
				case 'import':
					$this->import();
				break;			
				default :
					$this->Manage();	
				break;
			}
	}

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
	}
	function GetDetail($id){
		# detail artikel
	}
	function cekAkses() {
		/* AKSES KELOMPOK 
		 * uraikan berdasarkan akses, 1:admin, 2:operator 3:skpd, 4:instansi_vertikal\
		 */
		$datausr = $this->auth->getDetail();
		$Qgroup = $this->db->query('SELECT * FROM `group` WHERE idgroup='.$datausr['idgroup']);
		$dataGroup = $this->db->fetchAssoc($Qgroup);
		// print_r($datausr);
		if ($datausr['idgroup'] == 1) {
			$this->userAkses = 'admin';
		} else if ($datausr['idgroup'] == 2) {
			$this->userAkses = 'instansi'; // [anovedit] atau skpd
			$sqlInstansi = 'SELECT * FROM instansi AS i 
					LEFT JOIN users AS u ON u.`idinstansi` = i.`idinstansi` 
					WHERE u.`iduser`='.$datausr['iduser'];
			$qInstansi = $this->db->query($sqlInstansi);
			$this->activeInstansi = $this->db->fetchAssoc($qInstansi);
		} else if ($datausr['idgroup'] == 3) {
			$this->userAkses = 'bidang';
			// $this->activeBidang = $this->db->fetchAssoc($qInstansi);
		}
	}

}