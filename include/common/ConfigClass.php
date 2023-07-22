<?php /* [20180920][anovedit] rewrite path|url menjadi dinamis; */

class ConfigClass extends Include_Common_Data
{
	private static $config_class_defined = false;

	public function __construct()
	{
			if (static::$config_class_defined) return;
			else static::$config_class_defined = true;
			parent::__construct();

			/* tidak usah dirubah, karena sudah otomatis */
			define('ROOT_PATH', $_SERVER['DOCUMENT_ROOT']."/");
			define('BASE_PATH', str_replace($_SERVER['DOCUMENT_ROOT'], '', ROOT_PATH));
			define('HOST', '//'.$_SERVER['HTTP_HOST']);
			define('ROOT_URL', HOST.BASE_PATH);

		  /* database */
		  define('DB_HOST', 'localhost');
		  define('DB_USER', 'homestead');
		  define('DB_PASS', 'secret');
		  define('DB_NAME', 'u1631272_keerom_satu_data_v3');

		  /* theme */
		  define('THEME', 'adminlte2');
		  define('THEME_PATH', ROOT_PATH.'themes/'.THEME);
		  define('THEME_URL', ROOT_URL.'themes/'.THEME.'/');

		  /* Security */
		  define('DISPLAY_ERRORS', IS_LOCALHOST ? true : false); // [legacy][?][todo:] belum tau mau saya apakan...
		  define('ERROR_TAG', '<!--some-error-found-here-->'); // cross js/php error [legacy][?]

		  /* https://secure.php.net/manual/en/errorfunc.configuration.php#ini.error-reporting */
		  ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
		  ini_set('display_errors', (bool) DISPLAY_ERRORS); // [legacy][deprecated][todo:]

		  $this->init();
	}

	/* [legacy][deprecated][todo:] */
	public function init() {}
}
