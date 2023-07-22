<?php

Class DataClass extends ModulClass
{
	/* [anovedit] handle penomoran, dan tingkatan */
	private $table_kelompok_levl = [];
	private $table_kelompok_numb = [];

	// [anovedit] saya pindah sini, supaya tidak mengganggu getter.
	private $elemennumber = 1;

	function Manage()
	{
		# grid & manajemen data

		$kategori_data = strtoupper(trim($this->scr->filter($_GET['cntmode'])));
		if ($kategori_data == 'PENGGUNAAN') {
			// $this->pgTitle = "PENGGUNAAN INDIKATOR";
			$this->pgScript = '<script>
				//$(document).ready(function(){autoCompleteSearch_indikator();});
				$(document).data(\'print_title\',\'DATA PENGGUNAAN INDIKATOR\');
			</script>
			<script src="{themepath}plugins/iCheck/icheck.min.js"></script>
			<script src="{themepath}js/data.js"></script>
			<link rel="stylesheet" href="{themepath}css/data.css">
			';

			$tab_menu = $tab_content = '';
			$tab_default = 'RKPD';
			$sql = $this->db->query("SELECT * FROM penggunaan_indikator WHERE penggunaan <> 'SIPD' ORDER BY penggunaan ASC"); 
  		while ($dataPenggunaan = $this->db->fetchAssoc($sql)) {
				$number++;
				if (strtoupper($dataPenggunaan['penggunaan']) == $tab_default) {
					$this->pgScript .= "
  					<script>
							$(document).ready(function(){
								loadContent_penggunaan({$dataPenggunaan['idpenggunaan']},'penggunaan');
							});
						</script>";
					$a_class = 'active';
					$aria_ex = 'true';
				} else {
					$a_class = '';
					$aria_ex = 'false';
				}

				$tab_id = $dataPenggunaan['idpenggunaan'];
       	$tab_menu .= "
       		<li class=\"{$a_class}\" onclick=\"loadContent_penggunaan({$dataPenggunaan['idpenggunaan']},'penggunaan');\">
       			<a href=\"#tab_{$tab_id}\" data-toggle=\"tab\" aria-expanded=\"{$aria_ex}\">{$dataPenggunaan['penggunaan']}</a>
       		</li>";
       	$tab_content .= "
         	<div class=\"tab-pane {$a_class}\" id=\"tab_{$tab_id}\">
            <h4>{$dataPenggunaan['penggunaan']}</h4>
            <p><b>{$dataPenggunaan['urai']}</b></p>
						<div id=\"data_content_{$dataPenggunaan['idpenggunaan']}\"></div>
          </div>";
  			
  		}

			$content = '
        <!-- Custom Tabs -->
        <div class="nav-tabs-custom">
          <ul class="nav nav-tabs">'.$tab_menu.'</ul>
          <div class="tab-content">'.$tab_content.'<!-- /.tab-pane --></div>
          <!-- /.tab-content -->
        </div>
        <!-- nav-tabs-custom -->
				<!-- Modal -->
				<div id="chartModal" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Chart</h4>
				      </div>
				      <div class="modal-body" id="modal_content"></div>
			      	<div class="modal-footer">
			        	<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			      	</div>
				    </div>
				  </div>
				</div>';
			// return $content;
		} 

		$this->title = 'Penggunaan Data';
		$this->content = $content;
	}

	function getJSON($id)
	{
		// ajax
		$jmode = $_GET['ajaxmode'];

		switch ($jmode) {
			case 'loadcontent':
				$type = $_GET['type'];
				return $this->_loadContent($id,$type);
			break;					
			case 'list_kelompok_profil':
				return $this->_listElement_Autocomplete('profil');
			break;		
			case 'list_kelompok_supd':
				return $this->_listElement_Autocomplete('supd');
			break;	
			case 'list_kelompok_indikator':
				return $this->_listElement_Autocomplete('indikator');
			break;
			default: break;
		}
	}

	function randomData()
	{
		# random indikator
		$sql = "SELECT
			k2.urai AS parent,
			k.urai,
			k.idkelompok,
			k.satuan
			FROM kelompok_matrix k
			JOIN kelompok_matrix k2 ON (k2.idkelompok = k.idparent)
			WHERE k.idkelompok IN (SELECT DISTINCT(idkelompok) FROM kelompok_detail_matrix)
			ORDER BY rand() ASC LIMIT 0,10";
		// die($sql);
		$res = $this->db->query($sql);
		$random_idk = "";
		while ($data = $this->db->fetchAssoc($res)) {
			$sqld = "SELECT * FROM kelompok_detail_matrix kd WHERE idkelompok = '{$data['idkelompok']}' AND (nilai <> '' OR nilai <> 0) ORDER BY tahun DESC;"; 
			$resd = $this->db->query($sqld);
			$datad = $this->db->fetchAssoc($resd);
			$random_idk .= "<i class='fa fa-send'></i>&nbsp;&nbsp;{$data['parent']} <i class='fa fa-angle-right'></i> {$data['urai']} ({$datad['nilai']} {$data['satuan']} / {$datad['tahun']})&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		}
		return $random_idk;
	}

	function FrontList()
	{
		// kategori : 1. PROFIL : urusan, 2. ASPEK, 3. SUPD
		$kategori_data = strtoupper(trim($this->scr->filter($_GET['kat'])));

		// load tampilan berdasakan kategory data
		if ($kategori_data == 'PROFIL') {
			$this->pgTitle = "PROFIL (URUSAN PEMERINTAHAN)";
			$this->pgScript = '
				<script>
					$(document).ready(function(){autoCompleteSearch_Profil();});
					$(document).data(\'print_title\',\'DATA URUSAN PEMERINTAHAN\');
				</script>';

			$urusan .= '
				<!-- /.accordion -->
				<div class="box-group col-sm-3 list_kategori">
					<div class=" data-search">
						<input placeholder="cari elemen data" id="search_elemen" name="search_elemen" class="form-control">
					</div>
					<div class=" data-navigation" id="accordion" role="tablist" aria-multiselectable="true" style="">';

			$is_collapse = 1;
			$sql = $this->db->query("SELECT *
				FROM urusan
				WHERE kode_suburusan <> ''
				ORDER BY kode_urusan,kode_suburusan");

			while ($dataUrusan = $this->db->fetchAssoc($sql)) {
  			$collapse = ($is_collapse) ? 'collapse' : 'collapse';
  			$kode = $dataUrusan['kode_urusan'].".".$dataUrusan['kode_suburusan'].".";
  			$id_object = str_replace('.','_',$kode);
  			$urusan .= '
	  			<div class="panel box box-warning" style=" margin:0px;">
			    	<div class="box-header with-border" role="tab" id="heading'.$id_object.'">
				    	<h3 class="box-title" style="font-size:14px;">
					    	<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$id_object.'" aria-expanded="false" class="collapsed">
						    	<span>'.$kode." ".$dataUrusan['urai'].'</span>
					    	</a>
				      </h3>
			    	</div>
			     	<div id="collapse'.$id_object.'" class="panel-collapse '.$collapse.'" aria-expanded="false" style="height: 0px;">
			      	<div class="box-body"><ul class="nav-list">';

  			$is_collapse = 0;
  			
  			$sql_klp = "SELECT * FROM kelompok_supd
  				WHERE idparent < 100
  				AND idkelompok IN (
  					SELECT idkelompok
  					FROM urusan_kelompok_supd
  					WHERE kode_urusan={$this->db->ci3db->escape($dataUrusan['kode_urusan'])}
  					AND kode_suburusan={$this->db->ci3db->escape($dataUrusan['kode_suburusan'])}
  					ORDER BY ordering ASC
  				)";

  			$res_klp = $this->db->query($sql_klp);
  			$number = 1;
  			while ($dataKlp = $this->db->fetchAssoc($res_klp)) {
  				$urusan .= "
  					<li class='nav'>
	  					<a href=\"javascript:loadContent_profil({$dataKlp['idkelompok']},'{$dataUrusan['kode_urusan']}','{$dataUrusan['kode_suburusan']}','profil');\">
	  						<span class='number'>{$number}.
	  						</span><span class='text'>{$dataKlp['urai']}</span>
	  					</a>
  					</li>";
  				$number++;
  			}
  			$urusan .= "</ul></div></div></div>";
	  	}

  		$urusan .= '</div></div>
  			<div class="panel-group col-sm-9 data-content">
  				<div class="box box-danger"  id="data_container">
  					<div class="box-header with-border" id="data_header">
  						<h3 class="box-title">DATA PROFIL</h3>
  						<p>untuk menampilkan data, pilih elemen data di samping</p>
						</div>
            <!-- /.box-header -->   
            <div class="box-body"><div id="data_content"></div></div>
            <!-- /.box-body -->         
	    		</div>
  			</div>';

			$this->pgContent = $urusan;

		/*
		} else if ($kategori_data == 'SUPD') {
			$this->pgTitle = "SUPD (Sinkronisasi Urusan Pemerintahan Daerah)";
			$this->pgScript = '<script>
				$(document).ready(function(){autoCompleteSearch_SUPD();});
				$(document).data(\'print_title\',\'DATA SUPD\');
			</script>';

			$urusan .= '
			<!-- /.accordion -->
			<div class="box-group col-sm-3 list_kategori">
			<div class=" data-search"><input placeholder="cari elemen data" id="search_elemen" name="search_elemen" class="form-control"></div>
			<div class="box-group data-navigation" id="accordion" role="tablist" aria-multiselectable="true">';

			$res_parent = $this->db->query("SELECT * FROM supd WHERE idparent=0 ORDER BY idsupd");
			while ($dataParent = $this->db->fetchAssoc($res_parent)) {
				$urusan .= '
					<div class="panel box box-info" style=" margin:0px; margin-top:0px;">
			    		<div class="box-header with-border supd-parent">
				    		<h3 class="box-title">'.$dataParent['urai'].'</h3>
			    		</div>
			    	</div>';

				$is_collapse = 1;
				$sql = $this->db->query("SELECT * FROM supd WHERE idparent='{$dataParent['idsupd']}' ORDER BY idsupd");

		  		while ($dataSUPD = $this->db->fetchAssoc($sql)) {
		  			$collapse = ($is_collapse) ? 'collapse' : 'collapse' ;
		  			$id_object = $dataSUPD['idsupd'];
		  			$urusan .= '
		  			<div class="panel box box-warning" style=" margin:0px;">
				    	<div class="box-header with-border" role="tab" id="heading'.$id_object.'">
					    	<h3 class="box-title" style="font-size:14px;">
					    	<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$id_object.'" aria-expanded="false" class="collapsed"><span>'.$dataSUPD['urai'].'</span>
		                      </a>
					        </h3>
				    	</div>
				     	<div id="collapse'.$id_object.'" class="panel-collapse '.$collapse.'" aria-expanded="false" style="height: 0px;">
				      	<div class="box-body"><ul class="nav-list">';

		  			$is_collapse = 0;
		  			
		  			$sql_klp = "SELECT * FROM kelompok_supd 
		  					WHERE idparent < 100 AND idkelompok IN (SELECT idkelompok FROM supd_kelompok WHERE idsupd='".$this->scr->filter($dataSUPD['idsupd'])."' ORDER BY ordering ASC)";
		  					// die($sql_klp);
		  			$res_klp = $this->db->query($sql_klp);
		  			$number = 1;
		  			while ($dataKlp = $this->db->fetchAssoc($res_klp)) {
		  				$urusan .= "<li class='nav'>
		  					<a href=\"javascript:loadContent_supd({$dataKlp['idkelompok']},'{$dataSUPD['idsupd']}','supd');\">
		  					<span class='number'>{$number}.</span><span class='text'>{$dataKlp['urai']}</span>
		  					</a></li>";
		  				$number++;
		  			}
		  			
		  			$urusan .= "</ul></div>
			    		</div>
			    	</div>";
		  		}
		  	}

	  		$urusan .= '
	  		</div>
	  		</div>
	  		<div class="panel-group col-sm-9 data-content">
	  		<div class="box box-danger"  id="data_container">
		            <div class="box-header with-border" id="data_header">
		              <h3 class="box-title">DATA SUPD</h3><br>
		              untuk menampilkan data, pilih elemen data di samping
		            </div>
		            <!-- /.box-header -->   
		            <div class="box-body">
						<div id="data_content"><i class="fa fa-arrow-left"></i> Pilih Fokus SUPD</div>
		            </div>
		            <!-- /.box-body -->         
		    </div>
	  		</div>';
			$this->pgContent = $urusan;
		*/
		/*
		} else if ($kategori_data == 'SIPD') {
			$this->pgTitle = "INDIKATOR SIPD";
			$this->pgScript = '<script>
				$(document).ready(function(){autoCompleteSearch_indikator();});
				$(document).data(\'print_title\',\'INDIKATOR SIPD\');
			</script>';

			$urusan .= '
			<!-- /.accordion -->
			<div class="box-group col-sm-3 list_kategori">
			<div class=" data-search"><input placeholder="cari elemen data" id="search_elemen" name="search_elemen" class="form-control"></div>
			<div class="box-group data-navigation" id="accordion" role="tablist" aria-multiselectable="true">';

			$is_collapse = 1;
			$sql = $this->db->query("SELECT * FROM kelompok WHERE idparent=4 ORDER BY ordering ASC"); 
	  		while ($dataUrusan = $this->db->fetchAssoc($sql)) {
	  			$collapse = ($is_collapse) ? 'collapse' : 'collapse' ;
	  			$kode = $dataUrusan['idkelompok'];
	  			$id_object = str_replace('.','_',$kode);
	  			$urusan .= '
	  			<div class="panel box box-warning" style=" margin:0px;">
			    	<div class="box-header with-border" role="tab" id="heading'.$id_object.'">
				    	<h3 class="box-title" style="font-size:14px;">
				    	<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$id_object.'" aria-expanded="false" class="collapsed"><span>'.$dataUrusan['urai'].'</span>
	                      </a>
				        </h3>
			    	</div>
			     	<div id="collapse'.$id_object.'" class="panel-collapse '.$collapse.'" aria-expanded="false" style="height: 0px;">
			      	<div class="box-body"><ul class="nav-list">';

	  			$is_collapse = 0;
	  			
	  			$sql_klp = "SELECT * FROM kelompok 
	  					WHERE idparent = ".$dataUrusan['idkelompok']." ORDER BY ordering ASC";
	  			// die($sql_klp);
	  			$res_klp = $this->db->query($sql_klp);
	  			$number = 1;
	  			while ($dataKlp = $this->db->fetchAssoc($res_klp)) {
	  				$urusan .= "<li class='nav'>
	  					<a href=\"javascript:loadContent_indikator({$dataKlp['idkelompok']},'sipd');\">
	  					<span class='number'>{$number}.</span><span class='text'>{$dataKlp['urai']}</span>
	  					</a></li>";
	  				$number++;
	  			}
	  			
	  			$urusan .= "</ul></div>
		    		</div>
		    	</div>";
	  		}

	  		$urusan .= '
	  		</div>
	  		</div>
	  		<div class="panel-group col-sm-9 data-content">
	  		<div class="box box-danger"  id="data_container">
		            <div class="box-header with-border" id="data_header">
		              <h3 class="box-title">INDIKATOR SIPD</h3><br>
		              untuk menampilkan data, pilih kelompok > jenis data di samping
		            </div>
		            <!-- /.box-header -->   
		            <div class="box-body">
						<div id="data_content"><i class="fa fa-arrow-left"></i> Pilih kelompok > jenis data Disamping</div>
		            </div>
		            <!-- /.box-body -->         
		    </div>
	  		</div>';
			$this->pgContent = $urusan;
		*/
		} else if ($kategori_data == 'EKPOD') {
			$this->pgTitle = "ASPEK, FOKUS DAN INDIKATOR KINERJA";
			$this->pgScript = '<script>
				$(document).ready(function(){autoCompleteSearch_indikator();});
				$(document).data(\'print_title\',\'ASPEK, FOKUS DAN INDIKATOR KINERJA\');
			</script>';

			$urusan .= '
			<!-- /.accordion -->
			<div class="box-group col-sm-3 list_kategori">
			<div class=" data-search"><input placeholder="cari elemen data" id="search_elemen" name="search_elemen" class="form-control"></div>
			<div class="box-group data-navigation" id="accordion" role="tablist" aria-multiselectable="true">';

			$is_collapse = 1;
			$sql = $this->db->query("SELECT * FROM kelompok WHERE idparent=2 ORDER BY ordering ASC"); 
  		while ($dataUrusan = $this->db->fetchAssoc($sql)) {
  			$collapse = ($is_collapse) ? 'collapse' : 'collapse' ;
  			$kode = $dataUrusan['idkelompok'];
  			$id_object = str_replace('.','_',$kode);
  			$urusan .= '
	  			<div class="panel box box-warning" style=" margin:0px;">
			    	<div class="box-header with-border" role="tab" id="heading'.$id_object.'">
				    	<h3 class="box-title" style="font-size:14px;">
					    	<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$id_object.'" aria-expanded="false" class="collapsed">
					    		<span>'.$dataUrusan['urai'].'</span>
				    		</a>
			    		</h3>
			    	</div>
			     	<div id="collapse'.$id_object.'" class="panel-collapse '.$collapse.'" aria-expanded="false" style="height: 0px;"><div class="box-body"><ul class="nav-list">';

  			$is_collapse = 0;
  			
  			$sql_klp = "SELECT * FROM kelompok WHERE idparent = {$dataUrusan['idkelompok']} ORDER BY ordering ASC";
  			$res_klp = $this->db->query($sql_klp);
  			$number = 1;
  			while ($dataKlp = $this->db->fetchAssoc($res_klp)) {
  				$urusan .= "
  					<li class='nav'>
  						<a href=\"javascript:loadContent_indikator({$dataKlp['idkelompok']},'indikator');\">
  						<span class='number'>{$number}.</span><span class='text'>{$dataKlp['urai']}</span>
  						</a>
  					</li>";
  				$number++;
  			}
  			$urusan .= "</ul></div></div></div>";
	  	}

			$urusan .= '</div></div>
	  		<div class="panel-group col-sm-9 data-content">
		  		<div class="box box-danger"  id="data_container">
		  			<div class="box-header with-border" id="data_header">
		  				<h3 class="box-title">ASPEK, FOKUS DAN INDIKATOR KINERJA</h3>
		  				<p>untuk menampilkan data, pilih fokus analisa di samping</p>
	  				</div>
	          <!-- /.box-header -->   
	          <div class="box-body">
							<div id="data_content">
								<i class="fa fa-arrow-left"></i> Pilih Aspek & Fokus Disamping
							</div>
						</div>
	          <!-- /.box-body -->         
			    </div>
	  		</div>';

			$this->pgContent = $urusan;
		/*
		} else if ($kategori_data == '5WILAYAHADAT') {
			$this->pgTitle = "5 WILAYAH ADAT";
			$this->pgScript = '<script>
				$(document).ready(function(){autoCompleteSearch_indikator();});
				$(document).data(\'print_title\',\'DATA 5 WILAYAH ADAT\');
			</script>';

			$urusan .= '
			<!-- /.accordion -->
			<div class="box-group col-sm-3 list_kategori">
			<div class=" data-search text-center"><h4>Wilayah & Distrik</h4></div>
			<div class="box-group data-navigation" id="accordion" role="tablist" aria-multiselectable="true">';

			$is_collapse = 1;
			$sql = $this->db->query("SELECT * FROM wilayah ORDER BY wilayah ASC"); 
	  		while ($dataWilayah = $this->db->fetchAssoc($sql)) {
	  			$collapse = ($is_collapse) ? 'collapse' : 'collapse' ;
	  			$kode = $dataWilayah['idwilayah'];
	  			$id_object = str_replace('.','_',$kode);
	  			$urusan .= '
	  			<div class="panel box box-warning" style=" margin:0px;">
			    	<div class="box-header with-border" role="tab" id="heading'.$id_object.'">
				    	<h3 class="box-title" style="font-size:14px;">
				    	<a data-toggle="collapse" data-parent="#accordion" href="#collapse'.$id_object.'" aria-expanded="false" class="collapsed"><span>'.$dataWilayah['wilayah'].'</span>
	                      </a>
				        </h3>
	                    <button onClick="loadContent_wilayahadat('.$dataWilayah['idwilayah'].',\'wilayahadat\');" class="btn-flat pull-right"><i class="fa fa-table"></i></button>
			    	</div>
			     	<div id="collapse'.$id_object.'" class="panel-collapse '.$collapse.'" aria-expanded="false" style="height: 0px;">
			      	<div class="box-body"><ul class="nav-list">';

	  			$is_collapse = 0;
	  			
	  			$sql_klp = "SELECT * FROM kabupaten_gis 
	  					WHERE idwilayah = ".$dataWilayah['idwilayah']." ORDER BY kabupaten ASC";
	  			// die($sql_klp);
	  			$res_klp = $this->db->query($sql_klp);
	  			$number = 1;
	  			while ($dataKlp = $this->db->fetchAssoc($res_klp)) {
	  				$urusan .= "<li class='nav'>
	  					<a href=\"javascript:loadContent_wilayahadat({$dataKlp['kodepemda']},'wilayahadat');\">
	  					<span class='number'>{$number}.</span><span class='text'>{$dataKlp['kabupaten']}</span>
	  					</a></li>";
	  				$number++;
	  			}
	  			
	  			$urusan .= "</ul></div>
		    		</div>
		    	</div>";
	  		}

	  		$urusan .= '
	  		</div>
	  		</div>
	  		<div class="panel-group col-sm-9 data-content">
	  		<div class="box box-danger"  id="data_container">
		            <div class="box-header with-border" id="data_header">
		              <h3 class="box-title">5 WILAYAH ADAT</h3><br>
		              untuk menampilkan data, pilih wilayah di samping
		            </div>
		            <!-- /.box-header -->   
		            <div class="box-body">
						<div id="data_content"><i class="fa fa-arrow-left"></i> Pilih Wilayah Disamping</div>
		            </div>
		            <!-- /.box-body -->         
		    </div>
	  		</div>';
			$this->pgContent = $urusan;
		*/
		} else if ($kategori_data == 'PENGGUNAAN') {
			$this->pgTitle = "PENGGUNAAN INDIKATOR";
			$this->pgScript = '
				<script>
					//$(document).ready(function(){autoCompleteSearch_indikator();});
					$(document).data(\'print_title\',\'DATA PENGGUNAAN INDIKATOR\');
				</script>';

			$urusan .= '
			<!-- /.accordion -->
			<div class="box-group col-sm-3 list_kategori">
			<div class=" data-search">
			<ul class="nav-list">';

			$is_collapse = 1;
			$sql = $this->db->query("SELECT * FROM penggunaan_indikator WHERE penggunaan <> 'SIPD' ORDER BY penggunaan ASC"); 
	  		while ($dataPenggunaan = $this->db->fetchAssoc($sql)) {
  				$number++;
  				$urusan .= "
  					<li class='nav'>
	  					<h5>
	  						<a href=\"javascript:loadContent_penggunaan({$dataPenggunaan['idpenggunaan']},'penggunaan');\">
	  							<span class='number' style='top:11px;'>{$number}.</span><span class='text text-slim'>{$dataPenggunaan['penggunaan']}</span>
	  						</a>
	  					</h5>
  					</li>";
	  			
	  		}
	  		
	  		$urusan .= "</ul></div>";

	  		$urusan .= '
	  		</div>
	  		<div class="panel-group col-sm-9 data-content">
	  		<div class="box box-danger"  id="data_container">
		            <div class="box-header with-border" id="data_header">
		              <h3 class="box-title">PENGGUNAAN INDIKATOR</h3><br>
		              untuk menampilkan data, pilih kategori penggunaan di samping<br>
		            </div>
		            <!-- /.box-header -->   
		            <div class="box-body">
						<div id="data_content"><i class="fa fa-arrow-left"></i> Pilih Penggunaan Disamping</div>
		            </div>
		            <!-- /.box-body -->         
		    </div>
	  		</div>';
			$this->pgContent = $urusan;
		} 

		$this->pgContent .= '
			<!-- Modal -->
			<div id="chartModal" class="modal fade" role="dialog">
			  <div class="modal-dialog">
			    <!-- Modal content-->
			    <div class="modal-content">
			      <div class="modal-header">
			        <button type="button" class="close" data-dismiss="modal">&times;</button>
			        <h4 class="modal-title">Chart</h4>
			      </div>
			      <div class="modal-body" id="modal_content">
			      {}
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			      </div>
			    </div>

			  </div>
			</div>';

		$this->pgScript .= '
			<script src="{themepath}plugins/Highcharts-4.2.3/js/highcharts.js"></script>
			<script src="{themepath}plugins/Highcharts-4.2.3/js/modules/exporting.js"></script>
			<script src="{themepath}js/data.js"></script>
			<link rel="stylesheet" href="{themepath}css/data.css">
			';

		$menu = new MenuClass;
		$user = new UserClass;
		$slider = new SliderClass;

		$this->template->init(THEME.'/detail.html', [
			'sitetitle' 	=> SITE_TITLE,
			'sitekey' 		=> SITE_KEY,
			'sitedesc' 	=> SITE_DESC,
			'menu'       => $menu->FrontDisplay('T'),
			'menufooter'	=> $menu->FrontDisplay('B'),
			'slider'		=> $slider->FrontDisplay(),

			'pagetitle'	=> $this->pgTitle,
			'pagecontent'	=> $this->pgContent,
			'pagescript'	=> $this->pgScript,

			'account_menu'	=> $user->AccountMenu(),

			'home' => ROOT_URL,
			'error_tag'	=> ERROR_TAG,

			'tweetacc'  => TWEET_ACC,
			'fbacc'     => FB_ACC,
			'contactfb' => FB_ACC,
			'googleacc' => GOOGLE_ACC,

			'contactaddr' => CONTACT_ADDR,
			'contacttelp' => CONTACT_TELP,
			'contactweb' => CONTACT_WEB,
			'contactfax' => CONTACT_FAX,
			'contactemail' => CONTACT_EMAIL,

			'hotline' => HOTLINE,
			'themepath' => THEME_URL,
		]);
		$this->template->printTpl();
	}

	private function _loadContent($iddata,$type='profil'){
		// ajax
		// range tahun
		$tahun_awal = (int) $_GET['tahun_awal'];
		$tahun_akhir = (int) $_GET['tahun_akhir'];
		$th_data = array();
		if (!empty($tahun_awal) && !empty($tahun_akhir)) {
			for ($xt = $tahun_awal;  $xt <= $tahun_akhir; $xt++) {$th_data[] = $xt;}
		}
		$tableData = $this->_loadContentData($iddata,$type,$th_data);

		// $this;dump($_GET,$_POST,get_defined_vars());die();

		return json_encode($tableData); 
	}

	private function _loadContentData($iddata,$type='profil',$tahunData=array())
	{
		//load dataTabale untuk ditampilkan
		$this->typeData  = $type; 
		$this->tahunData = $tahunData;
		$this->detailTable = 'kelompok_detail_matrix';

 		if ($this->typeData == 'profil') {
 			$this->kode_urusan = $_GET['kode_urusan'];
 			$this->kode_suburusan = $_GET['kode_suburusan'];
			$sqlTitle = "SELECT
				u.*,
				CONCAT(u.kode_urusan,'.',u.kode_suburusan) as title_number,
				u.urai as urusan,
				u.urai as title,
				k.*,
				k.idkelompok as started_id
				FROM `kelompok_supd` k
				JOIN `urusan` u ON (
					u.kode_urusan = {$this->db->ci3db->escape($this->kode_urusan)}
					AND u.kode_suburusan = {$this->db->ci3db->escape($this->kode_suburusan)}
				)
				WHERE k.idkelompok={$this->db->ci3db->escape($iddata)}";
		 	$resKelompok = $this->db->query($sqlTitle);
		 	$resTitle = $this->db->query($sqlTitle);
		 	$rowTitle = $this->db->fetchAssoc($resTitle);
		 	$judul_data = "<h4>".$rowTitle['kode_urusan'].".".$rowTitle['kode_suburusan'].".".$rowTitle['urusan']."</h4>".$rowTitle['urai'];

 		} else if ($this->typeData == 'supd') {
 			$this->idsupd = $_GET['idsupd'];
			$sqlKelompok = "SELECT
				s.*,
				s.urai as supd,
				sp.urai as supd_parent,
				sp.urai AS title,
				k.*,
				k.idkelompok as started_id
				FROM `kelompok_supd` k
				JOIN `supd` s ON (s.idsupd = {$this->scr->filter($this->idsupd)})
				JOIN `supd` sp ON (sp.idsupd = s.idparent)
				WHERE k.idkelompok={$this->db->escape_string($iddata)}";
		 	$resKelompok = $this->db->query($sqlKelompok);
		 	$resTitle = $this->db->query($sqlKelompok);
		 	$rowTitle = $this->db->fetchAssoc($resTitle);
		 	$judul_data = "<h4>".$rowTitle['supd_parent']." : ".$rowTitle['supd']."".$rowTitle['urusan']."</h4>".$rowTitle['urai'];

 		} else if ($this->typeData == 'indikator' || $this->typeData == 'sipd') {
			$sqlKelompok = "SELECT
				kp.urai as title,
				kp.urai as fokus,
				k.*,
				k.idkelompok as started_id
				FROM `kelompok` k
				JOIN `kelompok` kp ON (kp.idkelompok = k.idparent)
				WHERE k.idkelompok={$this->db->ci3db->escape($iddata)}";
		 	$resKelompok = $this->db->query($sqlKelompok);
		 	$resTitle 	= $this->db->query($sqlKelompok);
		 	$rowTitle = $this->db->fetchAssoc($resTitle);
		 	$judul_data = "<h4>".$rowTitle['fokus']."</h4>".$rowTitle['urai'];

 		} else if ($this->typeData == 'wilayahadat') {
 			// seleksi wilayah / kabupaten
 			if ($iddata < 10) {
				$sqlTitle = "SELECT
					w.wilayah as title,
					w.wilayah as lokasi,
					0 as started_id
					FROM `wilayah` w
					WHERE w.idwilayah={$this->db->escape_string($iddata)}";
	 			$this->wilayah = $iddata;			

 			} else {
				$sqlTitle = "SELECT
					CONCAT(w.wilayah,', Kab/Kota. ',k.kabupaten) as title,
					CONCAT(w.wilayah,', Kab/Kota. ',k.kabupaten) as lokasi,
					0 as started_id
					FROM `kabupaten_gis` k
					JOIN `wilayah` w ON w.idwilayah = k.idwilayah
					WHERE k.kodepemda={$this->db->escape_string($iddata)}";
	 			$this->kabupaten = $iddata;
 			}

		 	$resTitle = $this->db->query($sqlTitle);
		 	$rowTitle = $this->db->fetchAssoc($resTitle);

	 		$sqlKelompok = "SELECT kb.idkelompok as started_id FROM kelompok_kabupaten kb WHERE kb.idparent = 0";
		 	$resKelompok = $this->db->query($sqlKelompok);

		 	$judul_data = "<h4>WILAYAH ADAT</h4>".$rowTitle['lokasi'];
		 	$this->detailTable = 'kelompok_detail_kabupaten';

 		} else if ($this->typeData == 'penggunaan') {
 			$this->id = $_GET['id'];

 			/*
 				[anovedit][workaround][anomali][!][?]
 				ada yang sangat aneh, saat menampilkan data dengan pengelompokan berdasarkan skpd.
 				ketika skpd seharusnya berada di atas, tiba-tiba bisa berada ditengah2 indikator,
 				pada skpd dibawahnya. kejadiannya random. dari 10x refresh, terjadi 1x.
 				maka dari itu, saya joinkan dengan table instansi, sehigga saya bisa,
 				sort berdasarkan kode-skpd.
 				saya masih belum tau solusinya. jadi ini hanya workaround sementara berdasarkan asumsi saya.
 			*/
			$sqlKelompok = "SELECT
				*,
				pk.idkelompok AS started_id
				FROM penggunaan_indikator pg_i
				JOIN penggunaan_kelompok pk ON (pk.idpenggunaan = pg_i.idpenggunaan)
				JOIN kelompok_matrix km ON (km.idkelompok = pk.idkelompok)
				JOIN instansi i ON (i.idinstansi = km.pk_instansi)
				JOIN users u ON (u.iduser = km.iduser)
				WHERE pg_i.idpenggunaan = {$this->db->escape_string($iddata)}
				AND km.idparent NOT IN (
					SELECT pi2.idkelompok
					FROM penggunaan_kelompok pi2
					WHERE pi2.idpenggunaan = {$this->db->escape_string($iddata)}
				)
				ORDER BY
				i.kode_urusan,i.kode_suburusan,i.kode_organisasi,
				km.ordering ASC";
		 	$resKelompok = $this->db->query($sqlKelompok);

		 	$sqlTitle = "SELECT * FROM penggunaan_indikator pg_i WHERE idpenggunaan={$this->db->escape_string($iddata)}";
		 	$resTitle = $this->db->query($sqlTitle);
		 	$rowTitle = $this->db->fetchAssoc($resTitle);

		 	$this->detailTable = 'kelompok_detail_matrix';
		 	$judul_data = "<h4>INDIKATOR - ".$rowTitle['penggunaan']."</h4>".$rowTitle['urai'];
 		}

		if (empty($this->tahunData)) {
			//opsi pilihan tahun
			$qData = $this->db->query("SELECT MIN(tahun) as min, MAX(tahun) as max FROM {$this->detailTable}");
			$tahunData = array_map('intval', $this->db->fetchAssoc($qData));
			$th_data = array();
			for ($xt = $tahunData['min'];  $xt <= $tahunData['max']; $xt++) {$th_data[] = $xt;}
			$this->tahunData = $th_data;
		}

		// get tahun dari submit atau table
 		$tahun_count = count($this->tahunData);
 		$tahun_header = $th_checkbox =  "";
 		foreach ($this->tahunData as $key => $value) {
 			$tahun_header .= "<th>".$value."</th>";
 			$th_checkbox .= "<input id='tahun_chart[]' name='tahun_chart[]' class='tahun_chart' type='checkbox' checked=true value='{$value}' hidden>";
 		}

	 	// export, import, print button
	 	$data_option = '
		 	<div class="pull-right no-print">
		 		<button class="btn btn-flat btn-warning" onclick="element_Print();"><i class="fa fa-print"> &nbsp; cetak</i></button>
		 		<!--<button class="btn btn-flat btn-success" onclick="excelExport(\''.$iddata.'\',\''.$type.'\','.min($this->tahunData).','.max($this->tahunData).');"><i class="fa fa-file-excel-o"> &nbsp; export</i></button>
		 		{opsi : tahun}-->
		 	</div>';

	 	//header
	 	$header = $judul_data.$data_option;

	 	$th_sumber = ($this->typeData == 'penggunaan') ? '' : '<th rowspan=2>Sumber</th>';
	 	$td_sumber = ($this->typeData == 'penggunaan') ? '' : '<td></td>' ;

	 	// table header, sesuaikan dengan pilihan tahun
 		$content = "
 			<div class='table-responsive'>
	 			<table class='table table-striped table-bordered data-container'>
	 			<tr>
					<th rowspan=2>No</th>
					<th rowspan=2>Urusan / Kelompok / Indikator</th>
					<th colspan={$tahun_count}>Tahun{$th_checkbox}</th>
					<th rowspan=2>Satuan</th>
					{$th_sumber}
					<th rowspan=2>Tanggal Update</th>
					<th rowspan=2 class='no-print'>Chart</th>
				</tr>
				<tr>{$tahun_header}</tr>";

		// table body
		$empty_td = str_repeat("<td></td>", count($this->tahunData));
		$content .= "
			<tr class='kelompok_parent'>
				<td>{$rowTitle['title_number']}</td>
				<td><b>{$rowTitle['title']}</b></td>
				{$empty_td}
				<td></td>
				<td></td>
				{$td_sumber}
				<td class='no-print'></td>
			</tr>";

		$this->detailKelompok = '';
		$this->row_id = 1;
		$this->urutinstansi = array();

		if ($this->typeData == 'penggunaan') {
			$dataPInst = $startedIdIndikator = array();
			while ($rowKelompok = $this->db->fetchAssoc($resKelompok)) {
				$dataPInst[$rowKelompok['idinstansi']][] = $rowKelompok['started_id'];
	 		}			
	 		foreach ($dataPInst as $idinstansi => $indikator) {
	 			foreach ($indikator as $key => $value) {
	 				# code...
		 			$startedIdIndikator[] = $value;
					$this->_lisdetailtelement($value);
	 			}
	 		}

		} else {
	 		while ($rowKelompok = $this->db->fetchAssoc($resKelompok)) {
				$this->_lisdetailtelement($rowKelompok['started_id']);
	 		}			
		}

 		$content .= $this->detailKelompok; //join
 		$content .= "</table></div>
			<b>keterangan : </b><br>
			<span class='text-gray' style='display:inline-block; width:40px;'>(n/a)</span>
			: not available (data tidak tersedia)<br>
			<span class='text-red' style='display:inline-block; width:40px;'>(*)</span>
			: data sementara";
 		
		return array('header' => $header, 'content' => $content);
	}

	private function _lisdetailtelement($iddata,$tab=0)
	{
		/*
		* dipakai pada form edit detail kelompok
		* lisitng element & sub (parent & 1 child)
		* tambahkan detail kelompok
		* - load kelompok - kelompok detail
		* - cek type permintaan > this->typeData
		*/
		// id, idparent, urai, formula, satuan
		// kelompok_detail, idkelompok_detail, idkelompok, tahun, nilai, iduser, postdate

		if ($this->typeData == 'profil' || $this->typeData == 'supd') {
			/* [anovedit][users_table_ignored] */
			$sqlKlp	= "SELECT
				km.idkelompok AS loaded_idkelompok,
				k_supd.*,
				i.*
				FROM kelompok_supd k_supd
				LEFT JOIN konversi_kelompok kk ON (kk.idkelompok_supd = k_supd.idkelompok)
				LEFT JOIN kelompok_matrix km ON (km.idkelompok = kk.idkelompok_matrix)
				LEFT JOIN instansi i ON (i.idinstansi = k_supd.pk_instansi)
				WHERE k_supd.idkelompok={$this->db->escape_string($iddata)}";
			$sql_child	= "SELECT * FROM kelompok_supd WHERE idparent={$this->db->escape_string($iddata)}";

		} else if ($this->typeData == 'sipd') {
			$sqlKlp	= "SELECT
				kk.idkelompok_matrix AS loaded_idkelompok,
				k_sipd.*,
				i.*
				FROM kelompok k_sipd
					LEFT JOIN konversi_kelompok kk ON (kk.idkelompok_sipd = k_sipd.idkelompok)
					LEFT JOIN kelompok_matrix km ON (km.idkelompok = kk.idkelompok_matrix)
					LEFT JOIN instansi i ON (i.idinstansi = k_sipd.pk_instansi)
					WHERE k_sipd.idkelompok={$this->db->escape_string($iddata)}";
			$sql_child	= "SELECT * FROM kelompok WHERE idparent={$this->db->escape_string($iddata)}";

		} else if ($this->typeData == 'indikator') {
			$sqlKlp	= "SELECT * FROM kelompok WHERE idkelompok={$this->db->escape_string($iddata)}";
			$sql_child	= "SELECT * FROM kelompok WHERE idparent={$this->db->escape_string($iddata)}";

		} else if ($this->typeData == 'wilayahadat') {
			$sqlKlp	= "SELECT *, k_kab.idkelompok AS loaded_idkelompok
				FROM kelompok_kabupaten k_kab
				LEFT JOIN instansi i ON (i.idinstansi = k_kab.pk_instansi)
				WHERE k_kab.idkelompok={$this->db->escape_string($iddata)}";
			$sql_child	= "SELECT * FROM kelompok_kabupaten WHERE idparent={$this->db->escape_string($iddata)}";

		} else if ($this->typeData == 'penggunaan') {
			$sqlKlp	= "SELECT km.*, i.*, km.idkelompok AS loaded_idkelompok
				FROM kelompok_matrix km
				LEFT JOIN instansi i ON (i.idinstansi = km.pk_instansi)
				WHERE km.idkelompok={$this->db->escape_string($iddata)}
				ORDER BY i.kode_urusan,i.kode_suburusan,i.kode_organisasi,km.ordering";
			$sql_child	= "SELECT * FROM kelompok_matrix WHERE idparent={$this->db->escape_string($iddata)}";
		}

		$QKelompok 		= $this->db->query($sqlKlp);
		$dataKelompok 	= $this->db->fetchAssoc($QKelompok);

		/* [anovedit] penomoran */
		if (!array_key_exists($dataKelompok['idparent'], $this->table_kelompok_numb)) $this->table_kelompok_numb[$dataKelompok['idparent']] = 0;
		$this->table_kelompok_numb[$dataKelompok['idparent']]++;
		$dataKelompok['numb'] = $this->table_kelompok_numb[$dataKelompok['idparent']];
		/* [anovedit] tingkatan */
		if ($dataKelompok['idparent'] == 0) {
			$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = 0;
		} else {
			$dataKelompok['levl'] = $this->table_kelompok_levl[$dataKelompok['idkelompok']] = $this->table_kelompok_levl[$dataKelompok['idparent']]+1;
		}
		/*
			[anovedit][workaround][special]
			anehnya di data profil, sudah ada penomorannya sendiri (langsung dari database).
			maka dari itu, penomoran mau saya matikan kalau yg ditampilkan adalah data profil.
			(guest) menu: data > urusan pemerintah;
		*/
		$numb_toggle = ($this->typeData === 'profil') ? 'hidden' : null;

		$res_child 	= $this->db->query($sql_child);
		$n_child 	= $this->db->numRows($res_child);
 
		if ($this->typeData == 'penggunaan') {
			// parent by instansi
			$countcolsp = count($this->tahunData)+3;
			if (!isset($this->urutinstansi[$dataKelompok['idinstansi']])) {
				$this->detailKelompok .= "
					<tr class=\"row_separator_instansi\" onClick=\"$('tr.subfrom_{$dataKelompok['idinstansi']}').toggle();\">
						<td></td>
						<td colspan=\"{$countcolsp}\" class=\"text-left\">{$dataKelompok['nama_instansi']}</td>
						<td><i class=\"fa fa-list\"></i></td>
					</tr>";
				$this->urutinstansi[$dataKelompok['idinstansi']] = 1;
			}
		}

		// [anovedit][workaround]
		$urai = htmlentities($dataKelompok['urai']);
		$levl = str_repeat('&nbsp; ', $dataKelompok['levl']);

		if ($n_child > 0) {
			// parent kelompok
			// idkelompok ini masih memiliki child didalamannya

			// punya formula, eksekusi
			$td_rekap = "";
			foreach ($this->tahunData as $key => $value) {
				$nilai_rekap = "";
				if (!empty($dataKelompok['formula'])) {
					// $nilai_rekap = "formula";	
					if ($this->kabupaten > 0) {
						$nilai_rekap = $this->_evalFormulaKabupaten($this->kabupaten,$dataKelompok['formula'],$value);
					} else if ($this->wilayah > 0) {
	  				$sqlKab = "SELECT kodepemda FROM kabupaten_gis WHERE idwilayah={$this->db->ci3db->escape($this->wilayah)}";
	  				$resKab = $this->db->query($sqlKab);
	  				$data_kab = array();
	  				while ($dataKab = $this->db->fetchAssoc($resKab)) {
	  					$data_kab[] = $this->_evalFormulaKabupaten($dataKab['kodepemda'],$dataKelompok['formula'],$value);
	  				}
						$nilai_rekap = $this->_getRekapWilayah($dataKelompok['idkelompok'],implode(',',$data_kab));
					} else {
						$nilai_rekap = $this->_evalFormula($dataKelompok['formula'],$value);
					}

					$nilai_rekap_f = $this->numen->autoSeparator($nilai_rekap);
				}
				$td_rekap .= "<td class='nilai_formula align-right'>{$nilai_rekap_f}</td>";
			} // foreach

			$col_instansi = ($this->typeData == 'penggunaan') ? "" : "<td></td>";

			$this->detailKelompok .= "<tr class=\"subfrom_{$dataKelompok['idinstansi']}\">
				<td>{$this->elemennumber}</td>
				<td nowrap>{$levl} <span {$numb_toggle}>{$dataKelompok['numb']}.</span> {$urai}</td>
				{$td_rekap}
				<td align=center>".$dataKelompok['satuan']."</td>
				{$col_instansi}
				<td></td>
				<td class='no-print'></td>
				</tr>";
			$this->elemennumber++;
  		while ($dataDetail = $this->db->fetchAssoc($res_child)) {
  			$this->_lisdetailtelement($dataDetail['idkelompok'],$tab+1);
  			//$n_child++;
  		}
		} else if (!empty($iddata)) {
			// depest child (kelompok ini adalah yg terdetail)
			$action = '<button type="button" class="btn-flat-info" onClick="openChart(\''.$dataKelompok['loaded_idkelompok'].'\');"><i class="fa fa-bar-chart-o"></i></button>';
			$curent_klp = "";
			$this->detailKelompok .= "
				<tr id='rows_{$this->row_id}' data-row-id='{$this->row_id}' class='rows_data subfrom_{$dataKelompok['idinstansi']}'>
					<td>{$this->elemennumber}</td>
					<td>{$levl} <span {$numb_toggle}>{$dataKelompok['numb']}.</span> {$urai}</td>";

			if ($this->kabupaten > 0) {
				$sqlDetail = "SELECT * FROM kelompok_detail_kabupaten
					WHERE idkelompok={$this->db->ci3db->escape($dataKelompok['loaded_idkelompok'])}
					AND idkabupaten={$this->db->ci3db->escape($this->kabupaten)}";

			} else if ($this->wilayah > 0) {
				$sqlDetail = "SELECT *, GROUP_CONCAT(nilai) AS set_nilai
					FROM kelompok_detail_kabupaten
					WHERE idkelompok={$this->db->ci3db->escape($dataKelompok['loaded_idkelompok'])}
					AND idkabupaten IN (
						SELECT kodepemda FROM kabupaten_gis
						WHERE idwilayah={$this->db->ci3db->escape($this->wilayah)}
					)
					GROUP BY tahun";

			} else {
				$sqlDetail = "SELECT *
					FROM kelompok_detail_matrix
					WHERE idkelompok={$this->db->ci3db->escape($dataKelompok['loaded_idkelompok'])}";
			}

			$qDetail = $this->db->query($sqlDetail);
			$data_arr = $data_chart = $postdate = array();
			while($rDetail = $this->db->fetchAssoc($qDetail)) {
				if ($this->wilayah > 0) { // load data wilayah adat / kabupaten
					$rDetail['nilai'] = $this->_getRekapWilayah($rDetail['idkelompok'],$rDetail['set_nilai']);
				}
				$nilai = ($rDetail['nilai'] == '') ? 0 : $rDetail['nilai'] ;
				$data_arr[$rDetail['tahun']] = $nilai;
				$postdate[] = $rDetail['postdate'];
			}
			$maxpost = max($postdate);

			foreach ($this->tahunData as $key => $val_tahun) {
				// input detail jika ada data terdetail
				$data_chart[$val_tahun] = (empty($data_arr[$val_tahun])) ? 0 : $data_arr[$val_tahun];
				// $lendata = strlen($data_arr[$val_tahun]);
				// listing colom data
				if (!isset($data_arr[$val_tahun])) {
					// memulai fungsi pemanggilan data sementara
					$tmp_data = $this->_getTemporaryData($dataKelompok['loaded_idkelompok'],$val_tahun);
					$data_chart[$val_tahun] = ($tmp_data['available']) ? $tmp_data['nilai'] : 0;
					$tmp_nilai = ($tmp_data['available']) ? "<span class='text-red'>".$this->numen->autoSeparator($tmp_data['nilai'])."*</span>" : "<span class='text-gray'>n/a</span>" ;
					$this->detailKelompok .= "<td class='td_input align-right'>{$tmp_nilai}</td>";
				} else {
					$this->detailKelompok .= "<td class='td_input align-right'>".$this->numen->autoSeparator($data_arr[$val_tahun])."</td>";
				}
			}
			// chartstrge.title,chartstrge.cat,chartstrge.sumber,chartstrge.y_title,chartstrge.series

			$series = '[{"name":"'.$dataKelompok['urai'].'","data":['.implode(",",$data_chart).']}]';

			$col_instansi = ($this->typeData == 'penggunaan') ? "" : "<td>{$dataKelompok['nama_instansi']}</td>";

			$this->detailKelompok .= "
					<td align=center>".$dataKelompok['satuan']."</td>
					{$col_instansi}
					<td nowrap>{$maxpost}</td>
					<td nowrap class='no-print'>
						{$action}
						<span id='strg_{$dataKelompok['loaded_idkelompok']}' data-chart='{\"title\":\"{$dataKelompok['urai']}\",\"cat\":[".implode(",",$this->tahunData)."],\"sumber\":\"{$dataKelompok['nama_instansi']}\",\"y_title\":\"{$dataKelompok['satuan']}\",\"series\":{$series}}'></span>
					</td>
				</tr>";
			$this->elemennumber++;
			$this->row_id++;
		}
  		// get / return var : $this->detailKelompok;
	}

	private function _getTemporaryData($idkelompok,$tahun)
	{
		/*
		| fungsi pemanggilan data yang belum tersedia untuk sementara
		| req : idkelompok, tahun
		| cpt : dapatkan data tahun sebelumnya yg bukan 0
		*/
		if ($this->kabupaten > 0) {
			$sqlDetail = "SELECT *
				FROM kelompok_detail_kabupaten
				WHERE idkelompok = {$this->db->ci3db->escape($idkelompok)}
				AND idkabupaten = {$this->db->ci3db->escape($this->kabupaten)}
				AND tahun < {$this->scr->filter($tahun)}
				ORDER BY tahun DESC LIMIT 1";

		} else if ($this->wilayah > 0) {
			$sqlDetail = "SELECT *, GROUP_CONCAT(nilai) AS set_nilai
				FROM kelompok_detail_kabupaten
				WHERE idkelompok = {$this->db->ci3db->escape($idkelompok)}
				AND idkabupaten IN (
					SELECT kodepemda
					FROM kabupaten_gis
					WHERE idwilayah = {$this->db->ci3db->escape($this->wilayah)}
				)
				AND tahun < {$this->db->ci3db->escape($tahun)}
				GROUP BY tahun ORDER BY tahun DESC LIMIT 1";
		} else {
			$sqlDetail = "SELECT * FROM kelompok_detail_matrix
				WHERE idkelompok = {$this->db->ci3db->escape($idkelompok)}
				AND tahun < {$this->db->ci3db->escape($tahun)}
				ORDER BY tahun DESC LIMIT 1";
		}

		$qDetail = $this->db->query($sqlDetail);
		$rDetail = $this->db->fetchAssoc($qDetail);

		if (isset($rDetail['nilai']) || isset($rDetail['set_nilai'])) {
  		if ($this->wilayah > 0) {
	  		$rDetail['nilai'] = $this->_getRekapWilayah($rDetail['idkelompok'],$rDetail['set_nilai']);
	  	}
			return array('available' => true, 'nilai' => $rDetail['nilai']);
		} else {
			return array('available' => false, 'nilai' => 0);
		}
	}

	private function _evalFormula($formula,$tahun,&$akumulasiFormula = '', &$tab = ''){
		// ubah formula menjadi value : 
		// contoh : {idkelompok}*{idkelompok} => 100*12
		// print $akumulasiFormula."<br>";
		preg_match_all("/\{([0-9]+)\}/", $formula, $arrmatches);
		if($akumulasiFormula == '') $akumulasiFormula = $formula;
		foreach ($arrmatches[1] as $idkelompok) {

			// cek ada formulanya atau tidak
			$sqlKelompok = "SELECT formula FROM kelompok_matrix WHERE idkelompok='{$this->db->escape_string($idkelompok)}'";
			$rKelompok = $this->db->query($sqlKelompok);
			$dataKelompok = $this->db->fetchAssoc($rKelompok);
			$formulaK = $dataKelompok['formula'];
			
			// jika kelompok itu masih punya formula maka rekursif
			if($formulaK <> '') {
				$akumulasiFormula = str_replace('{'.$idkelompok.'}', $formulaK, $akumulasiFormula); 
				$nilai = $this->_evalFormula($formulaK,$tahun,$akumulasiFormula,$tab);
				// LogClass::log('replace : '.'{'.$idkelompok.'} menjadi '.$nilai);
			} else {
				// parsing idkelompoknya
				$sqlDetail = "SELECT nilai
				 	FROM kelompok_detail_matrix
				 	WHERE idkelompok='{$this->db->escape_string($idkelompok)}'
				 	AND tahun = '{$this->db->escape_string($tahun)}'";
			 	$rDetail = $this->db->query($sqlDetail);
			 	$dataDetail = $this->db->fetchAssoc($rDetail);
			 	$res_nilai = trim($dataDetail['nilai']);
			 	$nilai = (empty($res_nilai)) ? 0 : $dataDetail['nilai'];
			 	$akumulasiFormula = str_replace('{'.$idkelompok.'}', $nilai, $akumulasiFormula);
			}
		}

		// evaluate
		$sqlEval = "SELECT ($akumulasiFormula) as hasilperhitungan";
		$rEval = $this->db->query($sqlEval);
		$dataEval = $this->db->fetchAssoc($rEval);	
		$hasilperhitungan = $dataEval['hasilperhitungan'];

		if(!is_numeric($hasilperhitungan)) $hasilperhitungan = $akumulasiFormula;

		$tab = $tab.'      ';

		return $hasilperhitungan;  			
	}

	private function _evalFormulaKabupaten($idkabupaten,$formula,$tahun,&$akumulasiFormula = '', &$tab = '')
	{
		// ubah formula menjadi value : 
		// contoh : {idkelompok}*{idkelompok} => 100*12
		// print $akumulasiFormula."<br>";
		preg_match_all("/\{([0-9]+)\}/", $formula, $arrmatches);
		if($akumulasiFormula == '') $akumulasiFormula = $formula;
		foreach ($arrmatches[1] as $idkelompok) {
			// cek ada formulanya atau tidak
			$sqlKelompok = "SELECT formula FROM kelompok_kabupaten WHERE idkelompok='{$this->db->escape_string($idkelompok)}'";
			$rKelompok = $this->db->query($sqlKelompok);
			$dataKelompok = $this->db->fetchAssoc($rKelompok);
			$formulaK = $dataKelompok['formula'];
			
			// jika kelompok itu masih punya formula maka rekursif
			if($formulaK <> '') {
				$akumulasiFormula = str_replace('{'.$idkelompok.'}', $formulaK, $akumulasiFormula); 
				$nilai = $this->_evalFormulaKabupaten($idkabupaten,$formulaK,$tahun,$akumulasiFormula,$tab);
				// LogClass::log('replace : '.'{'.$idkelompok.'} menjadi '.$nilai);
			} else {
				// parsing idkelompoknya
				$sqlDetail = "SELECT nilai FROM kelompok_detail_kabupaten
					WHERE idkelompok='{$this->db->escape_string($idkelompok)}'
					AND idkabupaten = '{$this->db->escape_string($idkabupaten)}'
					AND	tahun = '{$this->db->escape_string($tahun)}'";
			   $rDetail = $this->db->query($sqlDetail);
			   $dataDetail = $this->db->fetchAssoc($rDetail);
			   $nilai = (empty($dataDetail['nilai'])) ? 0 : $dataDetail['nilai'];
			   $akumulasiFormula = str_replace('{'.$idkelompok.'}', $nilai, $akumulasiFormula);    
			}
		}

		// evaluate
		$sqlEval = "SELECT ($akumulasiFormula) as hasilperhitungan";
		$rEval = $this->db->query($sqlEval);
		$dataEval = $this->db->fetchAssoc($rEval);	
		$hasilperhitungan = $dataEval['hasilperhitungan'];

		if(!is_numeric($hasilperhitungan)) $hasilperhitungan = $akumulasiFormula;
		// metode ini bisa 

		return $hasilperhitungan;  			
	}

	private function _getRekapWilayah($idkelompok,$data)
	{
		/*
		| hitung rekap perwilayah 
		| $data = set data yang akan di hitung
		*/
		$sql_rep = "SELECT metode_kalkulasi AS method FROM kelompok_kabupaten WHERE idkelompok={$this->db->ci3db->escape($idkelompok)}";
		$res_rep = $this->db->query($sql_rep);
		$data_rep = $this->db->fetchAssoc($res_rep);
		$method = $data_rep['method'];
		$arr_data = explode(',',$data);

		$r_val = 0;
		switch ($method) {
			case 'SUM' :
				$r_val = array_sum($arr_data);
			break;			
			case 'AVG' :
				$calc = array_sum($arr_data) / count($arr_data);
				$r_val = $calc;
			break;
		}
		return $r_val;
	}

	private function _listElement_Autocomplete($type)
	{
		// membuat list json kelompok berdasrkan type datanya : urusan, sipd, supd
		// susun query berdasrkan akses ke indikator

		if ($type == 'profil') {
			$sql_kelompok 	= "SELECT
				k.idkelompok,
				uk.*,
				CONCAT(k.urai,' - ',ub.kode_urusan,'.',ub.kode_suburusan,'. ',ub.urai,'') as label
				FROM kelompok_supd k
				LEFT JOIN urusan_kelompok_supd uk ON (uk.idkelompok = k.idkelompok)
				LEFT JOIN urusan ub ON (
					ub.kode_suburusan = uk.kode_suburusan
					AND ub.kode_urusan = uk.kode_urusan
				)
				WHERE k.idparent < 100
				AND k.idkelompok IN (
					SELECT idkelompok FROM urusan_kelompok_supd
					WHERE kode_urusan <> '' AND kode_suburusan <> ''
					ORDER BY ordering ASC
				)";

		} else if ($type == 'supd') {
			$sql_kelompok 	= "SELECT
				k.idkelompok,
				sk.*,
				CONCAT(k.urai,' - ',sb.urai,' : ',sa.urai,'') as label
				FROM kelompok_supd k
				LEFT JOIN supd_kelompok sk ON (sk.idkelompok = k.idkelompok)
				LEFT JOIN supd sa ON (sa.idsupd = sk.idsupd)
				LEFT JOIN supd sb ON (sb.idsupd = sa.idparent)
				WHERE k.idparent < 100
				AND k.idkelompok IN (SELECT idkelompok FROM supd_kelompok ORDER BY ordering ASC)";

		} else if ($type == 'indikator') {
			$sql_kelompok 	= "SELECT
				kc.idkelompok,
				CONCAT(kc.urai,' : ',k.urai,'') as label
				FROM kelompok k
				LEFT JOIN kelompok kc ON (kc.idparent = k.idkelompok)
				WHERE k.idparent = 2";
		}

		$res_kelompok	= $this->db->query($sql_kelompok);
		$json_data = array();
		while ($rec_kelompok = $this->db->fetchAssoc($res_kelompok)) {
			$id = $rec_kelompok['idkelompok'];
			$json_data[$id]['label'] = $rec_kelompok['label'];
			$json_data[$id]['value'] = $rec_kelompok['label'];
			$json_data[$id]['kode_urusan'] 		= $rec_kelompok['kode_urusan'];
			$json_data[$id]['kode_suburusan'] 	= $rec_kelompok['kode_suburusan'];
			$json_data[$id]['idsupd'] 	= $rec_kelompok['idsupd'];
			$json_data[$id]['id'] = $id;
		}
		sort($json_data);
		print json_encode($json_data);
	}

	private function _listsubelement($idkelompok,$tab=0){
		/**
		* lisitng sub element (child & g child)
		*/
		$sqlsub = "SELECT * FROM kelompok WHERE idparent={$this->db->escape_string($idkelompok)}";
		$res_sub = $this->db->query($sqlsub);
		while ($dataSUrusan = $this->db->fetchAssoc($res_sub)) {
			$urusan .= "
				<tr>
					<td style='padding;1px;'>
						<a href='javascript:loadChart({$dataSUrusan['idkelompok']})'>"
						.str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$tab)
						."{$dataSUrusan['idkelompok']} - {$dataSUrusan['urai']} - ({$dataSUrusan['satuan']})
						</a>
					</td>
				</tr>";
			$urusan .= $this->_listsubelement($dataSUrusan['idkelompok'],$tab+1);
		}
		return $urusan;
	}

	private function _listelement($idkelompok)
	{
		/*
		* lisitng element & sub (parent & 1 child)
		* id, idparent, urai, formula, satuan
		*/
		$sql 		= "SELECT * FROM kelompok WHERE idparent={$this->db->escape_string($idkelompok)}";
		$res_sql 	= $this->db->query($sql); //analisa
		$urusan .= "<div class='table-responsive'><table class='table table-condensed table-hover'>";
		while ($dataUrusan = $this->db->fetchAssoc($res_sql)) {
			$urusan .= "<tr><td onClick='loadChart({$dataUrusan['idkelompok']});'><b>{$dataUrusan['idkelompok']} {$dataUrusan['urai']}</b></td></tr>";
			$urusan .= $this->_listsubelement($dataUrusan['idkelompok']);
		}
		$urusan .= "</table></div>";
		return $urusan;
	}

}
