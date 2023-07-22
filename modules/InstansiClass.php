<?php
Class InstansiClass extends ModulClass{

	/**
	* modul disimpan dalam 1 file untuk kemudahan upload modul
	* fungsi insert, update, delete, manage, dan pembuatan form 
	* disertakan dalam tiap modul. Template dipisahkan, masuk dalam 
	* folder themes. class ini merupakan abstrak untuk di extend oleh
	* modul-modul yang akan dipakai
	* @author Bruri <bruri@gi.co.id>
	* @version 1.0
	* @package Modul
	**/

	function buildForm(){
		# menampilkan form
	}
	function Insert(){
		# query insert 
	}
	function Update(){
		# query update 
	}
	function Delete(){
		# query delete 
	}
	function Manage(){
		# grid & manajemen data
		// tbl instansi : idinstansi, nama_instansi, singkatan, kode-urusan, kode_suburusan, kode_organisasi, idtipe_instansi, idkategori_instansi
		// tbl instansi_kategori : idkategori_instansi, kategori_instansi
		// tbl instansi_tipe : idtipe_instansi, tipe_instansi 
		$sql = "SELECT * FROM instansi i
				LEFT JOIN instansi_kategori ik ON ik.idkategori_instansi = i.idkategori_instansi
				LEFT JOIN instansi_tipe it ON it.idtipe_instansi = i.idtipe_instansi
				LEFT JOIN users u ON u.idinstansi = i.idinstansi
				WHERE 
				i.nama_instansi NOT LIKE 'admin%'
				ORDER BY 
				i.nama_instansi ASC";
		$res = $this->db->query($sql);

		$this->title = 'Instansi';		
		$this->pgScript = '<script src="{themepath}js/instansi.js"></script>';

		// $field = array('SKPD'=>'skpd_name', 'Urusan'=>'kode_urusan','Sub Urusan'=>'kode_suburusan','Organisasi'=>'kode_organisasi');	
		// $this->in_content .= $this->__tabelUsers();
 		// $this->in_content .= $this->grid->init($res,'skpd_name',$field,array('editing'=>'1','adding'=>'1','deleting'=>'1','class'=>'grid', 'previous'=>'0', 'updown' => '0'));
 		$table = "<table id='table_instansi' class ='table table-condensed table-bordered table-striped' border='0' cellpadding='0' cellspacing='0' width='100%'>
						  <thead>
						  <tr>
							<th>No</th>
							<th>Nama Instansi</th>
							<th>Singkatan</th>
							<th>Kategori</th>
							<th>Tipe</th>
							<th>action</th>
						  </tr>
						  </thead>
						  <tbody>
						  ";
		/*
<td>
            <!--
            <button type="button" class="btn btn-info btn-sm" onClick="displayElemen('.$rInstansi['kode_urusan'].');">'.$rInstansi['kode_urusan'].'.'.$rInstansi['kode_suburusan'].'.'.$rInstansi['kode_organisasi'].'</button>
            
            '.$rInstansi['kode_urusan'].'.'.$rInstansi['kode_suburusan'].'.'.$rInstansi['kode_organisasi'].'-->

            </td>
		*/
		$no=1;
 		while ($rInstansi = $this->db->fetchAssoc($res)) {
 			$table .= '<tr>
 			<td>'.$no.'</td>
 			<td>'.$rInstansi['nama_instansi'].'</td>
            <td>'.$rInstansi['singkatan'].'</td>
 			<td>'.$rInstansi['kategori_instansi'].'</td>
 			<td>'.$rInstansi['tipe_instansi'].'</td>
            <td nowrap>											
				<a href="javascript:editInstansi('.$data[$i]['idinstansi'].');" class="fa fa-edit btn btn-primary" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Edit\"></a>
				<a href="#" class="fa fa-times-circle btn btn-danger" data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\" onClick=\"codel("".ROOT_URL."giadmin/pemolaan/".$data[$i]["idpemolaan"]."/del.htm");\"></a>										
			</td>
              </tr>';
            $no++;
 		}
 		$table .= "</tbody></table>
 		";

		$this->content = $table;//$tplbody->parse();
	}
	function FrontDisplay(){
		# tampilan depan
	}
	function FrontList(){
		# tampilan daftar Instansi
	}
	function GetDetail($id){
		# detail artikel
	}

}