<?php
/**
* 
* file ini berfungsi sebagai perantara framework dengan QueryBuilder milik CodeIgniter3
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20180817-ppr, 20180922
* 
*/

/* semua classes ci3 membutuhkan "BASEPATH" */
if (!defined('BASEPATH')) {
	$path = realpath(__DIR__ . '/../../php_packages/codeigniter/framework/system/'); // false|string
	define('BASEPATH', ($path ? ($path . '/') : null)); // string|null
}

/* "APPPATH" hanya sekedar untuk memuaskan CI3 */
if (BASEPATH && !defined('APPPATH')) define('APPPATH', BASEPATH);

/* ==================================================
fake-function load_class() untuk fake-class
original
	~/system/core/Common.php
mentioned
	~/system/database/DB_driver.php >―――> display_error()
================================================== */
if (BASEPATH && !function_exists('load_class')) {
	function load_class($name,$type) { return (new CI3_Fake_Load_Class($name,$type)); }
	class CI3_Fake_Load_Class {
		public function __construct($name,$type) { $this->name = $name; $this->type = $type; }
		public function Exceptions()
		{
			return print_r(array_filter(func_get_args()), true);
		}
		public function __call($fn,$args) {
			array_unshift($args, $fn);
			if (method_exists($this, $this->name)) return call_user_func_array(array($this,$this->name), $args);
			return null;
		}
	}
}

/* fungsi ini hanya sekedar untuk memuaskan ci3 */
if (BASEPATH && !function_exists('log_message')) { function log_message() {} }

/* ==================================================
copas
	~/system/core/Common.php
mentioned
	~/system/database/drivers/postgre/postgre_driver.php >―――> escape()
	~/system/database/drivers/mysqli/mysqli_driver.php >―――> _trans_begin()
================================================== */
if (BASEPATH && !function_exists('is_php')) {
	function is_php($version) {
		static $_is_php;
		$version = (string) $version;
		if ( ! isset($_is_php[$version])) { $_is_php[$version] = version_compare(PHP_VERSION, $version, '>='); }
		return $_is_php[$version];
	}
}
