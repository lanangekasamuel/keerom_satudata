<?php
class AuthenticationClass
{	
	
	var $db;	
	var $group;
	var $sql;
	var $ssusrvar;
	var $sspassvar;
	var $timeout;

	private static $getDetail_ed;
	
	function __CONSTRUCT()
	{
		$this->db 	 = new MysqliClass();
		$this->encr  = new EncryptClass;
		$this->ssusrvar  = md5('ssusr');
		$this->sspassvar = md5('sspsw');
		$this->timeout = 60*60*60;
	}	
	
	
	function validLogin($usr,$pass)
	{
		//
		$this->sql = "SELECT * FROM users 
					WHERE MD5(MD5(username))='".$this->db->escape_string($usr)."' 
					AND pass=MD5(CONCAT('".$usr."','".$this->db->escape_string($pass)."'));";
		$res 	= $this->db->query($this->sql);
	
		if($this->db->numRows($res) > 0 ){
			return true;
		}else{
			return false;
		}
	}
	
	function setCookie($name,$val)
  	{		
		setcookie($name, $val, time() + $this->timeout );
  	}
	
	

	function unsetCookie( $name ) {
		setcookie ($name, "", time() - 3600);
	} 
	
	
	function getExpire()
	{
		return $this->timeout;
	}
	
	function getDetail()
  	{
  		if (isset(self::$getDetail_ed)) self::$getDetail_ed;
		
		$usr 	= $this->encr->decrypt($_SESSION[$this->ssusrvar]);
		$sql	= "SELECT * FROM users WHERE md5(md5(username))= '$usr' ";
		$res 	= $this->db->query($sql);
		$res = self::$getDetail_ed = $this->db->fetchArray($res);						
		return $res;
	}
	
	
	function login($usr,$pass){
		if($this->validLogin($usr,$pass)){
			$_SESSION[$this->ssusrvar] = $this->encr->encrypt($usr); 
			$_SESSION[$this->sspassvar] = $this->encr->encrypt($pass); 
			$this->setCookie(md5('coousr'),$this->encr->encrypt($usr));
			$this->setCookie(md5('coopsw'),$this->encr->encrypt($pass));
			return true;
		}else{
			return false;
		}
	}
	
	
	function isAuth(){		
		$usr 	= $this->encr->decrypt($_SESSION[$this->ssusrvar]);
		$pass 	= $this->encr->decrypt($_SESSION[$this->sspassvar]);
		$this->sql = "SELECT * FROM users 
					WHERE MD5(MD5(username))='".$usr."' 
					AND pass=MD5(CONCAT('".$usr."','".$pass."'));";
		//die('gagal auth : '.$this->sql);
		$res 	= $this->db->query($this->sql);
		if($this->db->numRows($res) > 0 && $_SESSION[$this->ssusrvar] == $_COOKIE[md5('coousr')] ){
			return true;
		}else{
			return false;
		}
	}
	
	function logout(){
		unset($_SESSION[$this->ssusrvar]);
		unset($_SESSION[$this->sspassvar]);		
		$this->unsetCookie(md5('coousr'));
		$this->unsetCookie(md5('coopsw'));
	}	
	
}

?>