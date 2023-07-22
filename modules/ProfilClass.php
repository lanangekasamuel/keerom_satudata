<?php
Class ProfilClass extends ModulClass{

	function Init(){
		$mode = ($_GET['cntmode'] <> '')?$_GET['cntmode']:$_POST['cntmode'];
			switch($mode){
				case 'form':
					$this->content = $this->buildForm();
				break;
				case 'upd':
					$this->Update();
				break;		
				default :
					$this->Manage();	
				break;
			}
	}

	function buildForm(){
		# menampilkan form
		# get data	
		$data = $this->auth->getDetail();
		$action ='upd';
		$status ='edit';		

		#build form
		$this->title = 'Users';
		
		$define = array (
						'pass'		=> '', 
						'info'		=> $info,
						'email'		=> $data['email'], 
						'idgroup'	=> $data['idgroup'], 
						'username'  => $data['username'],
						'id' 		=> $data['iduser'],
						'status' 	=> $status,
						'rootdir' 	=> ROOT_URL,
						'action' 	=> $action
						 );		
		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/profil.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();
		$this->pgScript = "<script src='{themepath}js/jqBootstrapValidation.js'></script>
		<script>
			$(document).ready(function(){
				$('input,select,textarea').not('[type=submit]').jqBootstrapValidation({preventSubmit: true});
			});
		</script>
		";			
		$this->content = $form;
	}
	function Update(){
		# query insert
		// users : iduser,username,pass
		$datausr = $this->auth->getDetail();

		$qaddpass = ($_POST['pass'] <> '')
			? "pass = md5(CONCAT(md5(md5('".$datausr['username']."')),md5(md5('".$this->scr->filter($_POST['pass'])."')))), "
			: '';

		$sql = "UPDATE users 
				SET 
					$qaddpass	
					email = '".$this->scr->filter($_POST['email'])."' 
				WHERE
					username = '".$datausr['username']."'";			
		$this->db->query($sql);
		echo "<script>alert('data tersimpan');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/beranda'>";	
	}
	
	function Manage(){
		# grid & manajemen data
		$this->buildForm();
	}	

	function getJSON($id){
		// ajax
		$jmode = $_GET['ajaxmode'];

		switch ($jmode) {
			case 'jsoncheck':
				return $this->jsonValidUser($id);
			break;
		}
	}

	function jsonValidUser(){
		$datausr = $this->auth->getDetail();
		$valid = ($datausr['pass'] == md5( md5(md5($datausr['username'])).md5(md5($_REQUEST["value"])) ))
			? true : false;

	    echo json_encode(
		    array(
		    "value" => $_GET["value"],
		    "valid" => $valid,
		    "message" => "Password tidak sesuai"
		    )
	    );

	    die();
	}

}