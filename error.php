<?php
defined('im') or die('404');

$_SESSION['DEBUG']=false;

set_error_handler('errhandler',E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
function errhandler($number,$string,$file,$line,$context){
	
	$cnf = new ConfigClass;

	$themepath = ROOT_URL.'themes/error/';
	$themefile = $themepath.'index.html';

	$errorstring = "error : ($number) $string <br /> $file  online:  $line";

	if(file_exists($themefile)){		
		$define = array(	
			'rooturl' => 'dsdsd',
			'themeurl' => $themepath.'------',
			'pagetitle' => $_SESSION['DEBUG_TITLE'].'GIPANEL System Report',
			'error_message' => ($_SESSION['DEBUG']===true)?$errorstring:'Saat Ini Sedang Dilakukan Perbaikan.',
			'additional_message' => '',
			'sorry_text' => 'Mohon maaf atas ketidak nyaman ini, kami akan melakukan perbaikan secepatnya.<br/>Silahkan coba kembali beberapa saat lagi.',
		);

		$html = @file($themefile);
		$html = implode("", $html);
		foreach($define as $key => $value){
			$html = str_replace('{'.$key.'}', $value, $html);
		}
		print($html);
		die();
	}
	else{
		print('<br/> <h2 align=center> '.$errorstring.'</h2>');
	}
	
}



?>