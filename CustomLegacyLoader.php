<?php
/**
* 
* custom legacy classes autoloader
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20180811-ppr, 20180920
* 
*/

class CustomLegacyLoader
{
	/*
	secara default,
	autoload menganggap kalo NAMA_CLASS dan NAMAFILE_CLASS.php adalah SAMA.
	kalo TIDAKSAMA, harus didefinisikan manual.
	maksudnya SAMA adalah CASE-SENSITIVE.
	"NamaClass" = "NamaClass.php" bukan "NamaClass" ≠ "namaclass.php" (kecuali windows)

	jika nama-class TIDAKSAMA dengan namafile-class.
	maka harus didefinisikan dibawah ini:
	NamaClass => Nama_Class.php,
	Nama_Class => namaclass.php,
	*/
	private $customs = array(
		'URLClass' => 'include/utility/UrlClass.php',
		'ShapeFile' => 'include/utility/shapefile.php',
		'ModulClass' => 'modules/Modul.php',
		'AdminClass' => 'modules/Admin.php',
	);

	// semua lokasi dimana classes berada
	private $locations = array(
		'include/common',
		'include/component',
		'include/utility',
		'include/data',
		'modules', // harus selalu terakhir
	);

	private $modules;

	public function __construct()
	{
		$this->modules = glob(__DIR__ . '/modules/*Class.php');
	}

	public function load_legacy_class($name)
	{
		// karena legacy tidak pakai namespace
		if (@preg_match('/\\/', $name)) return false;

		/* customs */
		if (isset($this->customs[$name])) {
			require __DIR__ . '/' . $this->customs[$name];
			return true;
		}

		/* modules */
		foreach ($this->modules as $i => $module) {
			$filename = basename($module,'.php');
			// di-lower-kan, karena meningkatkan persentase kesamaan
			similar_text(strtolower($name), strtolower($filename), $persentase);
			// jika seandainya class tidak ditemukan,
			// tingkatkan minimal persentase, tapi jangan 100%
			// jika persentase NamaClass dengan namaclass.php lebih dari 97.5%
			if ($persentase > 97.5) {
				unset($this->modules[$i]);
				require $module;
				return true;
			}
		}

		/* locations */
		foreach ($this->locations as $location) {
			$path = __DIR__.'/'.$location.'/'.$name.'.php';
			if (file_exists($path)) {
				require $path;
				return true;
			}
		}

		return false;
	}
}

spl_autoload_register(array(new CustomLegacyLoader, 'load_legacy_class'));

call_user_func(function() {
	$IS_LOCALHOST = false;
	if ((isset($_SERVER['IS_LOCALHOST']) && $_SERVER['IS_LOCALHOST'] == 1) || (isset($_ENV['IS_LOCALHOST']) && $_ENV['IS_LOCALHOST'] == 1)) {
		$IS_LOCALHOST = true;

		$php_packages__autoload = __DIR__ . '/php_packages/autoload.php';
		if (file_exists($php_packages__autoload)) {
			require $php_packages__autoload;

			$whoops = new \Whoops\Run;
			$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
			$whoops->register();
		}
	}
	define('IS_LOCALHOST', $IS_LOCALHOST);
});
