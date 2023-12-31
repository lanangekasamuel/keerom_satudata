<?php
class SecurityClass{
	
	//var $filter; 	
	
		function __construct(){
			$this->db = new MysqliClass;
		}

		function filter($str){
			if(get_magic_quotes_gpc()){
				   $str = stripcslashes($str);
			}

			$str = utf8_decode($str);   
			$forbidstr = array(";","#","'","\\\\","\*");   
			foreach( $forbidstr as $fbd => $prs){
				$str = str_replace($prs,'',$str);
			}  
			return $this->db->escape_string($str);
		}		
				
		function cleanAllRequest(){
				# REQUEST
				foreach( $_REQUEST as $key => $val)
				{
				  $_REQUEST[$key] = $this->filter($val);		  
				} 		
				# POST
				foreach( $_POST as $key => $val)
				{
				  $_POST[$key] = $this->filter($val);	 		  
				} 	
				# GET
				if (isset($_GET)){
				foreach( $_GET as $key => $val)
				{
				  $_GET[$key] = $this->filter($val);			
				} 
				}
				# SESSION	
				if (isset($_SESSION)){
					foreach( $_SESSION as $key => $val)
					{
					  $_SESSION[$key] = $this->filter($val);	  		  
					} 
				}
	
		}

		function utf8_encode($string) {
			$string = mb_convert_encoding($string,'UTF-8','UTF-8');
			return $string;
		}
		
}
?>