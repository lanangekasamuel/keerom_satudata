<?php
class DateClass{
		function IndonesianMonth($intMon){
			$intMon = (int) $intMon; 
			$arMon = array(1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
				return $arMon[$intMon];
		}
		
		function IndonesianDay($intMon){
			$hari = array('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu');
			return $hari[$intMon];
		}
		
				
		function IndonesianDate($sqldate){	
			$intBln=substr($sqldate,5,2);	
			$bln = $this->IndonesianMonth($intBln);	
			
			return substr($sqldate,8,2).' '.$bln.' '.substr($sqldate,0,4);
		}
		
		function IndonesianDatetime($sqldate){	
			$intBln=substr($sqldate,5,2);	
			$bln = $this->IndonesianMonth($intBln);	
			
			return substr($sqldate,8,2).' '.$bln.' '.substr($sqldate,0,4).'&nbsp;&nbsp;'.substr($sqldate,11);
		}
		
		function hariini(){		
		return $this->IndonesianDay(@date("w")-1).', '.$this->IndonesianDate(@date("Y-m-d"));
		}
}
?>