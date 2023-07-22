<?php
class MysqliClass Extends CI3DatabaseClass {
	protected $__DRIVER__ = 'mysqli';
	public static $__CI3INSTANCE__;
	public static $__OVERRIDE__ = false;
	public static $__PARAMETERS__ = array();

	var $conected;
	var $con;
	var $sql;
	var $error;
	var $host;
	var $user;
	var $password;
	var $database;
	var $config;
	var $secur;

	// copas ^__construct()
	public function LegacyConnect()
	{
		$this->connected = false;
		if ($this->con = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME)) {
			$this->connected = true;
		}
	}

	function query($qry){ 
		if (static::$__OVERRIDE__) return $this->ci3db->query($qry);

			$this->sql = $qry;
			if($this->config->writeLog){
				// write log
			}
			return @mysqli_query($this->con,$qry);
	}	
	
	function lastError(){
			return addslashes(str_replace("\n",' ',mysqli_error($this->con)));
	}	

	function escape_string($str){
		return mysqli_real_escape_string($this->con,$str);
	}
	
	function first(&$res) {
		$res->data_seek(0);
	}	
	
	function fetchArray($qry){
			return @mysqli_fetch_array($qry);
	} 

	function fetchAssoc($qry){
			return @mysqli_fetch_assoc($qry);
	} 

	function fetchAssoc_Query($qry){
			return $this->fetchAssoc($this->query($qry));
	} 

	function fetchObject($qry){
			return @mysqli_fetch_object($qry);
	}

	function numRows($qry){
			return @mysqli_num_rows($qry);
	}

	function numField($qry){
			return @mysqli_num_fields($qry);
	}	  
	
	function getData($table){
			return $this->query("SELECT * FROM ".$table);
	}
	
	function getDataWhere($table,$wfield,$wvalue){
			return $this->query("SELECT * FROM ".$table." WHERE  ".$wfield." = '".$wvalue."'");
	}

}
