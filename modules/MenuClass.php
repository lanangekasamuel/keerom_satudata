<?php
Class MenuClass extends ModulClass{

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

	function RecursiveMenu($position, &$sresult = "", $parent = 0, $level = 0, $paret_urai = ""){

			$sql = "SELECT *, idmenu as id FROM
									menu
								 WHERE
								 	parent = '{$parent}' AND
								 	position = '{$position}' AND
								 	published = 'Y'
								 order by ordering ";

			$dataSource = $this->db->query($sql);

			if($this->db->numRows($dataSource) > 0){
				if ($position == 'T') {
					$sresult .= ($level > 0)?"<ul class=\"dropdown-menu\" role=\"menu\">\n":"";
					while($data = $this->db->fetchArray($dataSource)){

						// cek child menu
						$sql_child = "SELECT count(idmenu) as nummenu FROM menu
										WHERE parent = '$data[idmenu]' AND published = 'Y'";
						$data_child = $this->db->query($sql_child);
						$rec_child = $this->db->fetchAssoc($data_child);

						// menu yg aktif
						// print $_GET['mode'].$data['url']."<br>";
						$c_active = ($_GET['mode']."/" == $data['url']) ? 'active' : '' ;

						// menu url
						$url = str_replace('{home}', ROOT_URL, $data['url']);
						$url = (substr($url,0,4)=='http')?$url:ROOT_URL.$url;
						$url = ( in_array(strtolower($data['menu']), array("home","beranda")))?ROOT_URL:$url;

						// menu custom icon
						if ($data['icon'] != '') { //'fa fa-arrow-circle-o-right';
							$menu_icon = '<h3 style="margin:0px;" class="text-center larger-menu-icon"><i class="'.$data['icon'].'"></i></h3><i class="'.$data['icon'].' menu-icon"></i>&nbsp;&nbsp;';
						} else {
							$menu_icon = '<i class="fa fa-folder-o"></i>&nbsp;';
						}

						// select menu. drop down or not
						if ($rec_child['nummenu'] > 0) {
							$sresult .="<li class=\"dropdown $c_active\">
							<a href=\"$url\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">{$menu_icon}".$data['menu']."\n
							<span class=\"caret\"></span></a>";
							$this->RecursiveMenu($position, $sresult,$data['idmenu'],$level+1,$paret_urai);
							$sresult .="</li>\n";
						} else {
							$sresult .="<li class=\"$c_active \">
							<a href=\"$url\">{$menu_icon}".$data['menu']."</a>\n
							</li>\n";
						}
					}
					$sresult .= ($level > 0)?"</ul>\n":"";
				} else {
					while($data = $this->db->fetchArray($dataSource)){
						// menu custom icon
						$menu_icon = '<i class="'.$data['icon'].'"></i>&nbsp;&nbsp;';

						// menu url
						$url = str_replace('{home}', ROOT_URL, $data['url']);
						$url = (substr($url,0,4)=='http')?$url:ROOT_URL.$url;
						$url = ( in_array(strtolower($data['menu']), array("home","beranda")))?ROOT_URL:$url;
						$sresult .= "<a href=\"{$url}\"><button class=\"btn btn-sm btn-info\">{$menu_icon} ".$data['menu']."</button></a>\n";
					}
				}
			}
	}

	function FrontDisplay($pos="T"){
		# tampilan depan
		//idmenu,meu,url
/*
		$sql = "SELECT * FROM menu WHERE published='Y' AND parent='0' AND position='T' order by ordering ";
		$res = $this->db->query($sql);*/
		// while($menuData = $this->db->fetchAssoc($res)){
		// 	$c_active = ($_GET['mode'].'/' == $menuData['url']) ? 'class="active"' : '' ;
		// 	$content .= '<li '.$c_active.'><a href="{home}'.$menuData['url'].'">'.$menuData['menu'].' <span class="sr-only">(current)</span></a></li>';
		// }

		$content = '';
		$this->RecursiveMenu($pos,$content);

		return 	$content;
	}

	function GetDetail($id){
		# detail artikel
	}

}
