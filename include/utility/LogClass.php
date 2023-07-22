<?php
Class LogClass
{
		public static function log($str,$path = 'sapilog.txt'){
			$file = fopen($path,"a+");
			fwrite($file,"$str \n");
			fclose($file);
		}

		public static function logClear($str,$path = 'sapilog.txt'){
			$file = fopen($path,"w");
			fwrite($file,"");
			fclose($file);
		}
}
?>