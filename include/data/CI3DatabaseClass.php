<?php
/**
* 
* CI3DatabaseClass
* Intergrasi QueryBuilder milik CodeIgniter3 untuk memudahkan dalam melakukan SQL.
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20180817-ppr, 20180922
* @link https://devdocs.io/codeigniter~3/database/index
* @link https://www.codeigniter.com/user_guide/database/index.html
* 
*/

require __DIR__ . '/ci3boot.php';

if (BASEPATH) {
	require BASEPATH . 'database/DB_driver.php';
	require BASEPATH . 'database/DB_query_builder.php';
	require BASEPATH . 'database/DB_result.php';
	function ci3db_define_runtime() {
		if (!class_exists('CI_DB',false)) {
			class CI_DB extends CI_DB_query_builder {};
		}
	}
}

class CI3DatabaseClass extends DatabaseClass
{
	public static $__PARAMETERS_DEFAULT__ = array(
		'dsn'   => '',
		'hostname' => 'localhost',
		'username' => '',
		'password' => '',
		'database' => '',
		'dbdriver' => '',
		'dbprefix' => '',
		'pconnect' => TRUE, // persistent by default (direkomendasikan)
		'db_debug' => FALSE,
		'cache_on' => FALSE,
		'cachedir' => '',
		'char_set' => 'utf8',
		'dbcollat' => 'utf8_general_ci',
		'swap_pre' => '',
		'encrypt' => FALSE,
		'compress' => FALSE,
		'stricton' => FALSE,
		'failover' => array()
	);

	public static $__CONFIG__;

	public function __construct()
	{
		if (!isset(static::$__CONFIG__)) static::$__CONFIG__ = new ConfigClass;
		$this->config =& static::$__CONFIG__;

		if (BASEPATH) {
			ci3db_define_runtime();
			require_once BASEPATH . 'database/drivers/'.$this->__DRIVER__.'/'.$this->__DRIVER__.'_driver.php';
			require_once BASEPATH . 'database/drivers/'.$this->__DRIVER__.'/'.$this->__DRIVER__.'_result.php';

			if (!isset(static::$__CI3INSTANCE__)) {
				static::$__PARAMETERS__ = array_merge(static::$__PARAMETERS_DEFAULT__, array_filter(array(
					'dbdriver' => $this->__DRIVER__,
					'hostname' => (defined('DB_HOST') ? DB_HOST : ''),
					'username' => (defined('DB_USER') ? DB_USER : ''),
					'password' => (defined('DB_PASS') ? DB_PASS : ''),
					'database' => (defined('DB_NAME') ? DB_NAME : ''),
					'port' => (defined('DB_PORT') ? DB_PORT : ''),
				)));

				$ci3db = 'CI_DB_' . $this->__DRIVER__ . '_driver';
				$ci3db = new $ci3db(static::$__PARAMETERS__);
				$ci3db->initialize();
				static::$__CI3INSTANCE__ = $ci3db;
			}
			$this->ci3db =& static::$__CI3INSTANCE__;

			/*
				memungkinkan untuk melakukan query secara biasa,
				maupun melakukan query menggunakan codeigniter3
			*/
			$this->con =& $this->ci3db->conn_id;

		} else {
			/*
				jadi begini, kalau tidak pakai codeigniter3,
				otomatis fallback dengan melakukan koneksi secara manual
			*/
			$this->LegacyConnect();
		}
	} // __construct

	/*
		tidak semua bisa melakukan fallback,
		untuk menjaga compatibilitas dalam framework,
		hanya classes lama saja yg bisa, yaitu PostgreClass dan MysqliClass
	*/
	protected function LegacyConnect()
	{
		throw new \Exception($this->__DRIVER__ . ' tidak mendukung LegacyConnect()', 1);
	}

	public function ci3query()
	{
		return call_user_func_array(array($this->ci3db, 'query'), func_get_args());
	}

	public function __call($fn,$args)
	{
		return call_user_func_array(array($this->ci3db, $fn), $args);
	}
}
