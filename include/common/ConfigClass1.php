<?php
class ConfigClass
{
  function __CONSTRUCT()
  	{
	  /* Database */
	  define('DB_HOST', 'localhost');
	  define('DB_USER', 'root');
	  define('DB_PASS', 'root');
	  define('DB_NAME', 'pusdalisbang_devel_sumber'); //appslexi_dc_papua_new
	  /* Theme */
	  define('HOST', "//".$_SERVER['HTTP_HOST']);
	  define('ROOT_URL', HOST."/pusdalisbang_papua_sumber/");
	  define('THEME', 'adminlte2');//responsive
	  define('THEME_URL', ROOT_URL."themes/".THEME."/");
	  define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/pusdalisbang_papua_sumber/");
	  /* Security */
	  error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
	  define('DISPLAY_ERRORS', true);

	  define('ERROR_TAG', '<!--some-error-found-here-->'); // cross js/php error

	  $this->init();
	}

	function init(){
	  (DISPLAY_ERRORS)?ini_set('display_errors','1'):ini_set('display_errors','0');
	  // pritn $a;
	}

}
?>
