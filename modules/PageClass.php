<?php
Class PageClass extends ModulClass{

	function init(){
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
			default :
				$this->content = $this->Manage();	
			break;
		}
		$this->title = 'Menu / Halaman';
	}

	function buildForm(){
		$action = 'ins';
		$data['status'] = 'Tambah';
		# get data
		// / idmenu,menu,url,published,parent,ordering
		if($_GET['id'] <> ''){
			$sql = "SELECT * FROM 
					menu m 
					 WHERE m.idmenu={$this->db->ci3db->escape($_GET['id'])}";
			//die($sql);					 
			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$data['status'] = 'Ubah';	
		}
		
		# parent option data
		$parentoption = "<option value='0'> - </option>";
		$this->RecursiveMenuRow($parentoption,"#".$data['parent']);
	
				
		// if external url
		if(preg_match('/http\:\/\//',$data['url'])){
			$url = $data['url'];
		}
					$furl = '
							<div class="form-group">  
			                      <label for="title" class="col-sm-2 control-label"><b>External url</b></label>
			                      <div class="col-sm-2 input-group">
			                          <input name="url" type="text" id="url" style="width: 630px;" value="'.$url.'"> <br> 
			                          ( ex : http://google.com )
			                      </div>
			                </div
							';
				
				
				#build form
				$define = array (
					'fpublished' => ($data['published'] === 'Y' ? 'checked' : ''),
					'ficon'      => $data['icon'],
						 'ftitle'		=> $data['menu'],
						 'flink'		=> $data['url'],
						 'ftitle_en'	=> $data['title_en'],
						 'fparent'  	=> $parentoption,
						 'fcontent' 	=> $data['content'],
						 'fcontent_en' 	=> $data['content_en'],
						 'furl'=> $furl,
						 'fid' => $data['idpage'],
						 'fidmenu' => $data['idmenu'],
						 'fstatus' => $data['status'],
						 'rootdir' => ROOT_URL,
						 'faction' => $action
						 );		
				$tplform = new TemplateClass;
				$tplform->init(THEME.'/forms/page.html');
				$tplform->defineTag($define);	
				$form = $tplform->parse();	
				return $form;
	}
	
	function insert(){
		#insert
			$result 	= $this->db->query("SELECT max(m.idmenu) as mxmenu from menu m");
			$dmax 		= $this->db->fetchArray($result);
			// $idpages 	= $dmax[mxpage] + 1;
			$idmenu 	= $dmax['mxmenu'] + 1;	
			$result		= $this->db->query("SELECT max(ordering) as mxord from menu m");
			$dord		= $this->db->fetchArray($result);
			$ordering   = ($dord['mxord'] <> '')?$dord['mxord'] +1 : 1;	

									
			// if($_POST['url'] == ''){
			// 	$url = "page/".$idpages."/".$this->url->friendlyURL($_POST['title']).".htm";
			// }else{
			// 	$url = $_POST['url'];
			// }
			
								
			// // save page
			// 	$sql = "INSERT INTO pages(idpage,title,content,idmenu) values (
			// 								'".$idpages."',
			// 								'".$_POST['title']."',
			// 								'".$_POST['content']."',
			// 								'".$idmenu."')";								
			// 	$this->db->query($sql);

			$sql = "INSERT INTO menu	
						SET 
						idmenu ={$this->db->ci3db->escape($idmenu)},
						menu = {$this->db->ci3db->escape($_POST['title'])},
						icon = {$this->db->ci3db->escape($_POST['icon'])},
						url ={$this->db->ci3db->escape($_POST['url'])},
						parent= '0',
						ordering ={$this->db->ci3db->escape($ordering)} ";											
			$this->db->query($sql);																		
			echo "<meta http-equiv='refresh' content='0;URL=".ROOT_URL."giadmin/page'>";
	}
	
	function update(){
		
		// if($_POST['url'] <> ''){
		// 		// if external url
		// 		if(eregi('http://',$_POST['url'])){
		// 			$url = " url = '".$_POST['url']."', ";
		// 		}else{
		// 			$url = " url = 'page/".$_POST['idpage']."/".$this->url->friendlyURL($_POST['title']).".htm', ";
		// 		}
		// }
		// #update pages
		// 	$sql = "UPDATE pages 
		// 			SET 
		// 				title='".$_POST['title']."',
		// 				content='".$_POST['content']."'
		// 			WHERE 
		// 				idpage='".$_POST['idpage']."'";								
		// 	$this->db->query($sql);			
		#update menu
			$published = (isset($_POST['published']) && $_POST['published'] === 'Y') ? 'Y' : 'N';
			$sql = "UPDATE menu 
					SET 
						menu={$this->db->ci3db->escape($_POST['title'])},
						icon = {$this->db->ci3db->escape($_POST['icon'])},
						published = '{$published}'
					WHERE 
						idmenu={$this->db->ci3db->escape($_POST['idmenu'])}";
			$this->db->query($sql);								

		echo "<meta http-equiv='refresh' content='0;URL=".ROOT_URL."giadmin/page'>";

	}

	function delete(){
		#delete
			// $result = $this->db->query("SELECT * FROM 
			// 							  menu m 
			// 							 WHERE m.idmenu='".$_GET['id']."'");
			// $data = $this->db->fetchArray($result);
			// #delete page
			// // $sql = "DELETE FROM pages WHERE	idpage='".$data['idpage']."' ";								
			// // $this->db->query($sql);
			// #delete menu
			// $sql = "DELETE FROM menu WHERE idmenu='".$data['idmenu']."' ";							
			// $this->db->query($sql);	
			// echo "<meta http-equiv='refresh' content='0;URL=".ROOT_URL."giadmin/page'>";
	}
	
	function up(){
		#ambil urutan
			$sql 		= "SELECT * FROM menu WHERE idmenu= '".$_GET['id']."'";					
			$res 		= $this->db->query($sql);
			$dtself	 	= $this->db->fetchArray($res);
			
			// q parent					
			$sql 		= "SELECT * FROM menu WHERE parent='".$dtself['parent']."' and ordering < ".$dtself['ordering']." order by ordering desc ";				
			$res 		= $this->db->query($sql);
			$dtchg	 	= $this->db->fetchArray($res);
						
			if($this->db->numRows($res) > 0){
				#tukar
				$sqla ="UPDATE menu SET ordering = '".$dtchg['ordering']."' WHERE idmenu='".$dtself['idmenu']."'";
				$this->db->query($sqla);
				$sqlb ="UPDATE menu SET ordering = '".$dtself['ordering']."' WHERE idmenu='".$dtchg['idmenu']."'";
				$this->db->query($sqlb);
			}
			echo "<meta http-equiv='refresh' content='0;URL=".ROOT_URL."giadmin/page'>";
	}
	
	function down(){
		#ambil urutan
			$sql 		= "SELECT * FROM menu WHERE idmenu= '".$_GET['id']."'";								
			$res 		= $this->db->query($sql);
			$dtself	 	= $this->db->fetchArray($res);
						
			$sql 		= "SELECT * FROM menu WHERE parent='".$dtself['parent']."' and ordering > ".$dtself['ordering']." order by ordering ";	
			// die($sql);
			$res 		= $this->db->query($sql);
			$dtchg	 	= $this->db->fetchArray($res);
							
			if($this->db->numRows($res) > 0){
			#tukar
				$sqla ="UPDATE menu SET ordering = '".$dtchg['ordering']."' WHERE idmenu='".$dtself['idmenu']."'";
				$this->db->query($sqla);
				$sqlb ="UPDATE menu SET ordering = '".$dtself['ordering']."' WHERE idmenu='".$dtchg['idmenu']."'";
				$this->db->query($sqlb);
			}
			echo "<meta http-equiv='refresh' content='0;URL=".ROOT_URL."giadmin/page'>";
	}


	function Manage(){
		# grid & manajemen data
		// $this->title = 'Menu / Halaman';		
		$this->content = $this->dbMenu();
		return $this->content;
	}

	function dbMenu()
	{		
		$arField = array('menu');

		if($class == ''){
				$class = 'grid';
			}

				$table ="				
					
				<strong><i class='fa fa-file-text-o margin-r-5'></i> Notes</strong>
				<ul>
				<li class='text-red'>menu tidak dapat dihapus</li>
		 		<li class='text-red'>url/link menu fixed, tidak bisa di ubah karena akan berpengaruh ke modul</li>
		 		</ul>

				<div style='font-size: 12px; padding-top:15px; padding-bottom:5px; padding-right: 15px;'>
									
						<a href='".ROOT_URL."giadmin/page/form.htm' class='fa fa-plus btn btn-success' data-toggle=\"tooltip\" 
						data-placement=\"top\" title=\"Add\"> </a>
				
				</div>							
				
				<div>
						<table class ='table table-condensed table-bordered table-striped $class' border='0' cellpadding='0' cellspacing='0' width='100%'>
						  <thead>
						  <tr>
							<th width='30px' height='25' align='center'><b>No</b></th>
							<th width='30px' height='25' align='center'><b>Icon</b></th>
							<th height='25' align=left  style='padding-left:5px;padding-right:5px'><b>Menu</b></th>
							<th width='30px' height='25' align='center'><b>Link</b></th>
							<th width='200px' height='25'><b>Action</b></th>
						  </tr>
						  </thead>
						  <tbody> ";
						  
				$this->RecursiveMenuRow($table);
						 
				// closetable		
				$table .="</tbody>
						</table>
					  </div> 
					  <br><br>					  
					  ";		
		return $table;					
	}


	function RecursiveMenuRow( &$sresult = "", $optid ="", $parent = 0, $level = 0,  &$no = 1){
			for($x=0;$x<$level;$x++){
				$pref .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				$optpref .= "&nbsp;&nbsp;";
			}

			$sql = "SELECT *, idmenu as id FROM														 
									menu 	
								 WHERE parent = '$parent' order by ordering ";

			$dataSource = $this->db->query($sql);
			while($data = $this->db->fetchArray($dataSource)){	
				// $color = ($no%2 == 0)?'#F7F7F7':'';
				if($optid == ''){
					$sresult .="<tr> 
										<td> $no </td>
										<td><i class=\"{$data['icon']}\"></i></td>
										<td> <b>". $pref.$data['menu'] ."</b></td>
										<td>{$data['url']}</td>
										<td>
																<a href='".ROOT_URL."giadmin/page/".$data['idmenu']."/up.htm' class='fa fa-arrow-up btn btn-success' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Up\"></a>
											 					<a href='".ROOT_URL."giadmin/page/".$data['idmenu']."/down.htm' class='fa fa-arrow-down btn btn-warning' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Down\"></a>
											 					<!--<a href='#' class='fa fa-times-circle btn btn-danger' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\" onClick=\"codel('".ROOT_URL."giadmin/page/".$data['idmenu']."/del.htm');\"></a>-->
																<a href='".ROOT_URL."giadmin/page/".$data['idmenu']."/form.htm' class='fa fa-edit btn btn-primary' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Edit\"></a>
										</td>
								</tr>		
								";
				}else{
					$ioptid = str_replace("#","",$optid); 
					$sel = ($ioptid == $data['idmenu'])?'selected':'';					
					$sresult .="<option value='".$data['idmenu']."' $sel > $optpref ".$data['menu']." </option>";
				}				
				$no++;			
				$this->RecursiveMenuRow($sresult,$optid,$data['idmenu'],$level+1, $no);
			}					
	}

	public function StaticDisplay() {}
}