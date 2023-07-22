<?php
Class AnalisakabupatenClass extends ModulClass{

	function buildForm(){
		# menampilkan form
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT a.idanalisis, a.judul, 
					GROUP_CONCAT(ak.idkelompok ORDER BY ak.idkelompok ASC) as kelompok
				FROM analisa_kabupaten a 
				LEFT JOIN analisis_kelompok_kabupaten ak ON (a.idanalisis = ak.idanalisis) 
                WHERE a.idanalisis=".$_GET['id']."
				GROUP BY a.idanalisis";

			$result = $this->db->query($sql);
			$data = $this->db->fetchArray($result);
			$action ='upd';
			$status ='edit';
			$kelompok =  explode(',',$data['kelompok']);
			$kelompok_urai =  explode(',',$data['kelompok_urai']);
			$info =', kosongkan jika tidak ingin merubah';					
		}				
		else{
			$status ='tambah';
			$kelompok = array();
		}

		sort($kelompok);

		#build form
		$this->title = 'Analisa';
		// kelompok; max = 10
		for ($x = 0; $x < 2; $x++) {
			// $id
			$result = $this->db->query("SELECT * FROM kelompok_kabupaten WHERE idkelompok=".$kelompok[$x]);
			$rK = $this->db->fetchAssoc($result);
			$val = ($rK['urai'] <> '') ? $rK['urai'] : '' ;
			$valid = ($rK['idkelompok'] <> '') ? $rK['idkelompok'] : '' ;

			$input_kelompok .= '<div class="kparent input-group"><input name="kelompok[]" id="kelompok[]" value="'.$valid.'" class="kelp" hidden><input name="" id="" class="kelompok form-control bg-info" value="'.$val.'">
				<span class="input-group-addon clear_kelompok"><a><i class="fa fa-remove"></i></a></span>
			</div>';
		}

		$define = array (
						'idanalisis'=> $data['idanalisis'], 
						'status'	=> $status,
						'info'		=> $info,
						'judul'		=> $data['judul'], 
						'kelompok'	=> $input_kelompok,
						'rootdir' 	=> ROOT_URL,
						'action' 	=> $action
						 );

		if (isset($_GET['ajaxOn'])) {
			$define['ajax_var'] = 'id="ajaxOn" name="ajaxOn"'; // untuk handling asal update
		}

		$tplform = new TemplateClass;
		$tplform->init(THEME.'/forms/analisa_kabupaten.html');
		$tplform->defineTag($define);	
		$form = $tplform->parse();	

		if (isset($_GET['ajaxOn'])) {
			$json_data = array(
				'content' => $form,
				'message' => 'load berhasil'
				);
			$jason = json_encode($json_data);
			die ($jason);
		} else {
			return $form; 
		}
	}
	function Insert(){
		# query insert 
		// dapatkan id terbaru
		// print_r($_POST);
		$result 	= $this->db->query("select max(idanalisis) as mxid from `analisa_kabupaten`");
		$dmax 		= $this->db->fetchArray($result);
		$newid	 	= $dmax[mxid] + 1; //id baru

		$datausr = $this->auth->getDetail();
		$sql = "INSERT INTO `analisa_kabupaten`
				SET 
				`judul` = '".$this->scr->filter($_POST['judul'])."', 
				idanalisis = '".$newid."'
				";	

		$insertQuery = $this->db->query($sql);

		// sisipkan ke tabel hak akses
		$kelompok = $_POST['kelompok'];
		foreach ($kelompok as $idkelompok) {
			# code...
			if (!empty($idkelompok)) {
				$sql = "INSERT INTO analisis_kelompok_kabupaten 
					SET 
					idanalisis = '".$newid."',
					idkelompok = '".$idkelompok."' 
					";	
				$insertQuery = $this->db->query($sql);
			}
		}

		// die();

		if (isset($_POST['ajaxOn'])) {
			// dipanggil melalui ajax
			$message = ($insertQuery) 
				? '<h2 class="text-success">menambahkan data sukses</h2>' 
				: '<h2 class="text-warning">menambahkan data gagal</h2>';

			$json_data = array(
				'message' => $message
				);

			$jason = json_encode($json_data);
			die ($jason);
		} else {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/analisakabupaten'>";				
		}
	}
	function Update(){
		# query update
		$datausr = $this->auth->getDetail();

		$kelompok = $_POST[kelompok];
			
		$sql = "UPDATE analisa_kabupaten 
				SET 
					judul = '".$this->scr->filter($_POST['judul'])."'
				WHERE
					idanalisis = '".$this->scr->filter($_POST['id'])."'";			
		$updateQuery = $this->db->query($sql);

		$qry = "DELETE FROM analisis_kelompok_kabupaten 
				WHERE 
				idanalisis=".$this->scr->filter($_POST['id']);
		$this->db->query($qry);


		foreach($kelompok as $idkelompok) {
			// tambahkan analisa
			if (!empty($idkelompok)) {
				$qry = "INSERT INTO `analisis_kelompok_kabupaten` 
				SET 
					idkelompok=".$idkelompok.",
					idanalisis=".$this->scr->filter($_POST['id'])."";
				$this->db->query($qry);
			} 
		}

		if (isset($_POST['ajaxOn'])) {
			// dipanggil melalui ajax
			$message = ($updateQuery) 
				? '<h2 class="text-success">update sukses</h2>' 
				: '<h2 class="text-warning">update gagal</h2>';

			$json_data = array(
				'message' => $message
				);

			$jason = json_encode($json_data);
			die ($jason);
		} else {
			echo "<script>alert('data tersimpan');</script>";
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/analisakabupaten'>";	
			die();			
		} 
	}
	function Delete(){
		# query delete 
		# query delete 
		$sql = "DELETE FROM analisa_kabupaten WHERE idanalisis='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);		
		$sql = "DELETE FROM analisis_kelompok_kabupaten WHERE idanalisis='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/analisakabupaten'>";
	}
	function Manage(){
		# grid & manajemen data
		// Analisa Perbandingan Tabel analisa_kabupaten, analisis_kelompok_kabupaten
		// idanalisis, judul JOIN idanalisis_kelompok,idkelompok,idanalisis 
		$sql = "SELECT a.idanalisis, a.judul, 
					GROUP_CONCAT(ak.idkelompok ORDER BY ak.idkelompok ASC) as kelompok 
				FROM analisa_kabupaten a 
				LEFT JOIN analisis_kelompok_kabupaten ak ON (a.idanalisis = ak.idanalisis) 
				GROUP BY a.idanalisis
                ORDER BY a.judul ASC";

		$res = $this->db->query($sql);

		// $field = array('Judul Analisa'=>'judul', 'Kelompok'=>'kelompok');	
		$this->title = 'Manajemen Analisa Kabupaten';		
		$this->pgScript = '<script src="{themepath}js/analisakabupaten.js"></script>
		<script>
			var rootdir = \''.ROOT_URL.'\';
		</script>';


		$add_link = "<a href='".ROOT_URL."giadmin/".$_GET[content]."/form.htm'
						class='fa fa-plus btn btn-success' data-toggle=\"tooltip\" 
						data-placement=\"top\" title=\"Add\"
						> </a>";

		$this->in_content = $add_link.'<table class="table table-striped table-condensed table-hover">
		<tr><th width="30px" height="25" align="center"><b>No</b></th><th height="25" align="left" ><b> Judul Analisa</b></th><th height="25" align="left" ><b> Elemen Data</b></th><th><b>Action</b></th></tr>';
		$x = 0;
		while ($recAnalisa = $this->db->fetchAssoc($res)) {
			$x++;
			// load kelompok detil
			$kelompok = "";
			foreach (explode(',',$recAnalisa['kelompok']) as $idkelp) {
				$scr_idkelp = $this->scr->filter($idkelp);
				$q = "SELECT * FROM kelompok_kabupaten WHERE idkelompok={$scr_idkelp}";
				$qKelp = $this->db->query($q); 
				$recKelp = $this->db->fetchAssoc($qKelp);
				// validasi formula, validasi keterisian data
				// $isformula = () ? "(formula)" : "" ;
				if (empty($recKelp['formula'])) {
					$sql_detail = "SELECT max(nilai) as nilai FROM kelompok_detail_matrix WHERE idkelompok='{$scr_idkelp}';";
					$res_detail = $this->db->query($sql_detail); 
					$data_detail = $this->db->fetchAssoc($res_detail);
					if (!empty($data_detail['nilai'])) {
						$kelompok .= "<li>".$recKelp['urai']."</li>";
					} else {
						$kelompok .= "<li class='text-red'><i class='fa fa-warning'></i> ".$recKelp['urai']." (belum ada data)</li>";
					}
				} else {
					$kelompok .= "<li>".$recKelp['urai']." <span class='text-blue'>(formula)</span></li>";
				}
				// die($q);
			}

			$edit_link = "<a href='".ROOT_URL."giadmin/".$_GET[content]."/".$recAnalisa['idanalisis']."/form.htm' class='fa fa-edit btn btn-primary' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Edit\"></a>";
			$delete_link = "<a  onClick=\"codel('".ROOT_URL."giadmin/".$_GET[content]."/".$recAnalisa['idanalisis']."/del.htm');\" id='del' class='fa fa-times-circle btn btn-danger' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\"></a>	";

			$this->in_content .= "<tr><td>{$x}</td><td>{$recAnalisa['judul']}</td><td>{$kelompok}</td><td>{$edit_link} {$delete_link}</td></tr>";
		}

		$this->in_content .= "</table>";

		$this->content = $this->in_content;
	}
	function FrontDisplay(){
		# tampilan depan
		$jenis_analisa = "<select id='jenis_analisa' class='form-control' onChange='loadAnalisaChart(this.value);'>
		<option value='0'>-- Pilih Analisa --</option>
		";
		$qAnalisa = $this->db->query("SELECT * FROM analisa_kabupaten");
		while ($rAnalisa = $this->db->fetchAssoc($qAnalisa)) {
			$jenis_analisa .= "<option value=\"{$rAnalisa['idanalisis']}\">{$rAnalisa['judul']}</option>";
		}
		$jenis_analisa .= "</select>";

		$chart = "
					<!-- LINE CHART -->
			        <div class='col-md-12'>
			          <div class='box box-info' id='chart_content'>
			            <div class='box-header with-border'>
			              <h3 class='box-title'>Chart Analisa</h3>
			              <div class='box-tools pull-right'>
			                  <button type='button' class='btn btn-box-tool' data-widget='collapse'><i class='fa fa-minus'></i>
			                  </button>
			                  <button type='button' class='btn btn-box-tool' data-widget='remove'><i class='fa fa-times'></i></button>
			              </div>
			            </div>
			            <div class='box-body'>
			              ".$jenis_analisa."
			            </div>
			            <div class='box-body'>
			              <div class='chart'>
			                	<div id='chart2'></div>
			              </div>
			            </div>
			          </div>
			          <!-- /.box -->
			        </div>
			        <!-- /.col (LEFT) -->
				 ";

		$series = "
					<!-- SERIES -->
			        <div class='col-md-6'>
			            <div class=''>
			                  <div class='col-md-12 box box-solid mb-10 sb1 chart_legend'>
			                        <div class='box-body'>
			                            <div class='series1'>
			                            	<div class='series-content'>
			                            		<div class='series-info'>
			                            			<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
			                            			<h3>Parent</h3>
			                            			<div class='series-author'> <i class='fa fa-bookmark-o'></i> <span></span> </div> <br>
			                            			<span>Last update : dasdasdas</span>
			                            		</div>
			                            		<div class='series-value'>
			                            			<div class='series-vb smax'>
														<i class='fa fa-arrow-circle-up'></i>
			                            				<h4>TERTINGGI</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>1.008</div>
			                            			</div>
			                            		</div>
			                            	</div>
			                            	<div class='series-panel'>
			                            	</div>
			                            </div> <!-- /.series1  -->
			                        </div> <!-- /.box body  -->
			                  </div> <!-- /.col  -->

			                  <div class='col-md-12 box box-solid mb-10 sb2 chart_legend'>
			                        <div class='box-body'>
			                            <div class='series2'>
			                            	<div class='series-content'>
			                            		<div class='series-info'>
			                            			<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
			                            			<h3>Parent</h3>
			                            			<div class='series-author'> <i class='fa fa-bookmark-o'></i> <span></span>   </div> <br>
			                            			<span>Last update : dasdasdas</span>
			                            		</div>
			                            		<div class='series-value'>
			                            			<div class='series-vb smax'>
														<i class='fa fa-arrow-circle-up'></i>
			                            				<h4>TERTINGGI</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>1.008</div>
			                            			</div>
			                            		</div>
			                            	</div>
			                            	<div class='series-panel'>
			                            	</div>
			                            </div> <!-- /.series2  -->
			                        </div> <!-- /.box body  -->
			                  </div> <!-- /.col  -->

			                  <div class='col-md-12 box box-solid mb-10 sb3 chart_legend'>
			                        <div class='box-body'>
			                            <div class='series3'>
			                            	<div class='series-content'>
			                            		<div class='series-info'>
			                            			<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
			                            			<h3>Parent</h3>
			                            			<div class='series-author'> <i class='fa fa-bookmark-o'></i> <span></span>  </div> <br>
			                            			<span>Last update : dasdasdas</span>
			                            		</div>
			                            		<div class='series-value'>
			                            			<div class='series-vb smax'>
														<i class='fa fa-arrow-circle-up'></i>
			                            				<h4>TERTINGGI</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>1.008</div>
			                            			</div>
			                            		</div>
			                            	</div>
			                            	<div class='series-panel'>
			                            	</div>
			                            </div> <!-- /.series3  -->
			                        </div> <!-- /.box body  -->
			                  </div> <!-- /.col  -->
			                  
			                 
			            </div> 
			        </div>    
			        <!-- /.col (RIGHT) -->
					";
	

		$this->pgScript = "
						<script>	
							$(document).ready(function(){
								loadAnalisaChart('x');
								$('#jenis_analisa').select2();
							});
						</script>
						";

		return $chart.$series;
	}
	// private function _listsubelement($id,$tab){
	// 	/**
	// 	 * lisitng sub element (child & g child)
	// 	 */
	// 	$sqlsub = $this->db->query("SELECT * FROM kelompok WHERE id_parent=".$id); //analisa

 //  		while ($dataSUrusan = $this->db->fetchAssoc($sqlsub)) {
 //  			$urusan .= "<tr><td style='padding;1px;'><a href='javascript:loadChart({$dataSUrusan['id']})'>".str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',$tab)."{$dataSUrusan['id']} - {$dataSUrusan['urai']} - ({$dataSUrusan['satuan']})</a></td></tr>";
 //  			$urusan .= $this->_listsubelement($dataSUrusan['id'],$tab+1);
 //  		}
 //  		return $urusan;
	// }
	// private function _listelement($id){
	// 	/*
	// 	 * lisitng element & sub (parent & 1 child)
	// 	 */
	// 	$urusan .= "<table class='table table-condensed table-hover'>";
	// 	// id, id_parent, urai, formula, satuan
	// 	$sql = $this->db->query("SELECT * FROM kelompok 
	// 			WHERE id_parent=".$id); //analisa
 //  		while ($dataUrusan = $this->db->fetchAssoc($sql)) {
 //  			$urusan .= "<tr><td onClick='loadChart({$dataUrusan['id']});'><b>{$dataUrusan['id']} {$dataUrusan['urai']}</b></td></tr>";
 //  			$urusan .= $this->_listsubelement($dataUrusan['id'],1);
 //  		}
 //  		$urusan .= "</table>";
 //  		return $urusan;
	// }
	private function _listKelompokAnalisa(){
		// membuat list json kelompok berdasrkan ura dicari
		// table kelompok : idkelompok, idparent, urai, formula, satuan 
		// persiapkan untuk custom analisa by user
		$keyword 		= $_GET['keyword']; //<!-- input keyword 

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT * FROM kelompok_kabupaten 
								WHERE urai LIKE '%".$this->db->escape_string($keyword)."%'
								AND (formula<>'' OR idkelompok IN (SELECT DISTINCT(idkelompok) FROM kelompok_detail_matrix))	";
									/*AND iduser<>'' 
									pilih hanya yg memiliki detail atau memiliki formula
									*/
			$res_kelompok	= $this->db->query($sql_kelompok);
			$json_data = array();
			while ($rec_kelompok = $this->db->fetchAssoc($res_kelompok)) {
				$id = $rec_kelompok['idkelompok'];
				// $json_data[$id]['name'] = $rec_kelompok['urai'];
				$json_data[$id]['label'] = $rec_kelompok['urai'];
				$json_data[$id]['value'] = $rec_kelompok['urai'];
				// $json_data[$id]['value'] = $id;
				$json_data[$id]['id'] = $id;
			}
			sort($json_data);
		} else {
			$json_data['message'] = 'akses ditolak!';
		} 
		print json_encode($json_data,JSON_NUMERIC_CHECK);

	}
	// function recrusiveData($parent_id,$id,$tahun){
	// 	/*
	// 	 * counting data detail dari sebuah sub elemen yg memiliki grand child
	// 	 * id = id kelompok
	// 	 * tahun = tahun yg akan di cari di detail child dari id
	// 	 */
	// 	global $recrusData;
	// 	// print "<br/>".$parent_id.'-'.$tahun.'-'.$id;
	// 	$result = 0;
	// 	$qChild = $this->db->query("SELECT * FROM kelompok WHERE id_parent=".$id." ORDER BY idkelompok ASC");
	// 	if ($this->db->numRows($qChild) > 0) {
	// 		// masih memiliki child
	// 		while ($rTabel = $this->db->fetchArray($qChild)){
	// 			// memiliki formula
	// 			if (!empty($rTabel['satuan'])) {
	// 				// eksekusi fungsi
	// 				$this->recrusiveData($parent_id,$rTabel['idkelompok'],$tahun);
	// 			// satuan parent kosong || memiliki satuan yang sama dengan parent
	// 			} else if (empty($rTabel['satuan'])) {
	// 				$this->recrusiveData($parent_id,$rTabel['idkelompok'],$tahun);
	// 			// atau memilik satuan yg sama dnega child lainnya
	// 			} else {					
	// 				$this->recrusiveData($parent_id,$rTabel['idkelompok'],$tahun);
	// 			}

	// 			// update satuan parent
	// 			$recrusData[$parent_id]['satuan'] = (empty($recrusData[$parent_id]['satuan'])) 
	// 				? $rTabel['satuan'] : $recrusData[$parent_id]['satuan'] ;
	// 		}
	// 	} else {
	// 		// sudah tidak memiliki child lagi
	// 		$nilai = 0;
	// 	      $qVal = $this->db->query("SELECT * FROM kelompok_detail 
	// 	      				WHERE tahun=".$tahun." and idkelompok=".$id." ");
	// 	      if ($rVal = $this->db->fetchArray($qVal)) {
	// 	        $nilai = $rVal['nilai'];
	// 	      } 
	// 	      //$data[] = $nilai;   
	// 		$recrusData[$parent_id][$tahun] += $nilai;
	// 		// print '-'.$nilai;
	// 	}
	// 	return $recrusData;
	// }


	// private function _chart($id){
	// 	/*
	// 	 * CHART JSON OUTPUT
	// 	 */
	// 	$tahun = $_GET['tahun_chart'];
	// 	$data = $this->_chartSource($id,$tahun);
 //  		return json_encode($data,JSON_NUMERIC_CHECK); //print angka sebagai numeric
	// }
	private function _chartAnalisa($id){
		/*
		 * CHART JSON OUTPUT
		 */
		// load analisa detail
		if($id == 'x'){
			$qAnalisa = $this->db->query("SELECT * FROM analisa_kabupaten order by rand() limit 0,1"); 
		}else{	
			$qAnalisa = $this->db->query("SELECT * FROM analisa_kabupaten WHERE idanalisis=".$this->db->escape_string($id));
		}

		// die ($id);
		$rAnalisa = $this->db->fetchAssoc($qAnalisa);
		$judul = $rAnalisa['judul'];
		$tahun_chart = $_GET['tahun_analisa'];

		// LogClass::logClear();
		// LogClass::log($judul."\n");

		$cnt_tahun = count($tahun_chart);
		$table = "<table class='table table-bordered table-condensed table-striped detail_data'><tr>
			<th rowspan=2>No</th><th rowspan=2>Indikator</th><th colspan={$cnt_tahun}>Tahun</th><th rowspan=2>Satuan</th>
			</tr>
			<tr><th>".implode("</th><th>",$tahun_chart)."</th></tr>";

		// load analisa items
		$id = $rAnalisa['idanalisis'];
		$sqlAnalisaK = "SELECT * FROM analisis_kelompok_kabupaten WHERE idanalisis=".$this->db->escape_string($id);
		$qAnalisaK = $this->db->query($sqlAnalisaK);

		// $akumulasiFormula = "";
		// $this->_evalFormula("{4424}+4427}+{4430}+{4433}+{4436}+{4439}",2012);
		// die($akumulasiFormula);

		$x = 1;
		while ($rAnalisaK = $this->db->fetchAssoc($qAnalisaK)) {
			$sa = $this->_detailKelompok($rAnalisaK['idkelompok'],$tahun_chart);
			$avg =  array_sum($sa['data']) / count($sa['data']);
			$series[$x] = array(
			'name' => preg_replace("/[\d{1}\)\._]/si","",$sa['judul']),
			'data' => $sa['data'],
			'satuan' => $sa['satuan'],
			'maxdata' => $sa['maxdata'],
			'mindata' => $sa['mindata'],
			'maxtahun' => $sa['maxtahun'],
			'mintahun' => $sa['mintahun'],
			'avgdata' => $sa['avgdata'],
			'source' => $sa['source'],
			'lastupdate' => $sa['lastupdate'],
			);
			$table .= "<tr><td>{$x}</td>
			<td>{$sa['judul']}</td>
			<td>".implode('</td><td>',$sa['data'])."</td>
			<td>{$sa['satuan']}</td>
			</tr>";
			$x++;
			// print_r($sa);	
		}

		$table .= "</table>";

		$kat = $sa['kategori'];
		$sumber = $sa['source'];

		$data = array(
			'title' => $judul, 
			'sumber' => $sumber, 
			'kategori' => $kat,
			'series1' => $series[1],
			'series2' => $series[2],
			'series3' => $series[3],
			'table'	  => $table
		);

  		return json_encode($data,JSON_NUMERIC_CHECK); //print angka sebagai numeric
	}

	private function _detailKelompok($idkelompok,$arrtahun = array() ){

		// ambil data kelompok
		$sqlKelompok = "SELECT * 
						FROM kelompok_kabupaten k 
							left join users u on k.iduser = u.iduser
							left join instansi i on i.idinstansi = u.idinstansi
						WHERE idkelompok=".$this->db->escape_string($idkelompok);
		$rKelompok = $this->db->query($sqlKelompok);
		$dataKelompok = $this->db->fetchAssoc($rKelompok);

		// data kelompok
		$id 	= $dataKelompok['idkelompok'];
		$judul 	= $dataKelompok['urai'];
		$satuan = $dataKelompok['satuan'];
		$lastupdate = $dataKelompok['lastupdate'];
		// default chart type
		$chart_type = "line";

		// LogClass::log("# $judul # \n");

		$tahun_chart = $arrtahun;
		// jika tahun tdk di inputkan, tampilkan semua data
		if (empty($tahun_chart)) {
			$sqlTahun = "SELECT 
								GROUP_CONCAT(DISTINCT(tahun) ORDER BY tahun ASC) as tahun 
						 FROM 
							kelompok_detail_matrix";
			$rTahun = $this->db->query($sqlTahun);
			$dataTahun = $this->db->fetchAssoc($rTahun);
			$tahun_chart =  explode(',', $dataTahun['tahun']);
		}
		// jika hanya satu tahun, bentuknya bar chart
		if (count($tahun_chart) == 1) {$chart_type = "column";}

		// cari nilai di tiap tahunnya
		$data = $nilai_tahun = array();
  		foreach($tahun_chart as $tahun) {
  			// LogClass::log("# $tahun # \n");
  			if($dataKelompok['formula']){
  				$val = $this->_evalFormula($dataKelompok['formula'],$tahun);
  				$data[] = $nilai_tahun[$tahun] = number_format($val,2,'.','');
  			}else{
			    $dataDetail['nilai'] = 0;
			    $sqlDetail = "SELECT * 
			    			  FROM 
			    			  	kelompok_detail_matrix 
			    			  WHERE 
			    			  	idkelompok='".$this->db->escape_string($idkelompok)."' AND 
			    			  	tahun = '".$this->db->escape_string($tahun)."'";
			    $rDetail = $this->db->query($sqlDetail);
			    $dataDetail = $this->db->fetchAssoc($rDetail); 	
			    $data[] = $nilai_tahun[$tahun] = number_format($dataDetail['nilai'],2,'.','');
			}    
		    
		    $thn[] = $tahun;
		}

		// max data 
		// tidak bisa meload data dengan ini, kelompok tidak meiliki detail
		// $sqlMax = "SELECT * 
	 //    			  FROM 
	 //    			  	kelompok_detail 
	 //    			  WHERE 
	 //    			  	idkelompok='".$this->db->escape_string($idkelompok)."' 
	 //    			  ORDER BY nilai DESC limit 0,1";
	 //    $rMax = $this->db->query($sqlMax);
	 //    $dMax = $this->db->fetchAssoc($rMax);		

	 //    // min data 
		// $sqlMin = "SELECT * 
	 //    			  FROM 
	 //    			  	kelompok_detail 
	 //    			  WHERE 
	 //    			  	idkelompok='".$this->db->escape_string($idkelompok)."' 
	 //    			  ORDER BY nilai limit 0,1";
	 //    $rMin = $this->db->query($sqlMin);
	 //    $dMin = $this->db->fetchAssoc($rMin);

	 //    // avg data 
		// $sqlAvg = "SELECT avg(nilai) as average
	 //    			  FROM 
	 //    			  	kelompok_detail 
	 //    			  WHERE 
	 //    			  	idkelompok='".$this->db->escape_string($idkelompok)."' 
	 //    			  ORDER BY nilai limit 0,1";
	 //    $rAvg = $this->db->query($sqlAvg);
	 //    $dAvg = $this->db->fetchAssoc($rAvg);		  

		// $maxdata = ($dMax['nilai'] == '')?'-':$dMax['nilai'];
		// $mindata = ($dMin['nilai'] == '')?'-':$dMin['nilai'];
		// $avgdata = number_format($dAvg['average'],2,'.','');
		// $maxtahun = ($dMax['tahun'] == '')?'-':$dMax['tahun'];
		// $mintahun = ($dMin['tahun'] == '')?'-':$dMin['tahun'];


		// minimum maximum nilai data
		$maxdata = max($nilai_tahun);
		$maxk = array_keys($nilai_tahun, $maxdata); //dapatkan index
		$maxtahun = $maxk[0];
		$maxdata = ($maxdata == '') ? '-' : $maxdata;	//0-ing  	

		$mindata = min($nilai_tahun);
		$mink = array_keys($nilai_tahun, $mindata); //dapatkan index
		$mintahun = $mink[0];
		$mindata = ($mindata == '') ? '-' : $mindata;	//0-ing  	

		$average = array_sum($nilai_tahun) / count($nilai_tahun);
		$avgdata = number_format($average,2,'.','');

		// sumber data instansi
		$source = ($dataKelompok['iduser'] == '') ? 'Pusdalisbang (*)' : $dataKelompok['nama_instansi'] ;

		if($satuan == '') $satuan = 'satuan';

		$kelompok = array(
			'idkelompok' => $id,
			'judul' => $judul,
			'satuan' => $satuan,
			'kategori' => $thn,
			'type' => $chart_type,
			'data' => $data,
			'maxdata' => $maxdata,
			'mindata' => $mindata,
			'avgdata' => $avgdata,
			'maxtahun' => $maxtahun,
			'mintahun' => $mintahun,
			'source' => $source,
			'lastupdate' => $lastupdate
		);

		return $kelompok;

	}

	private function _evalFormula($formula,$tahun,&$akumulasiFormula = '', &$tab = ''){
		// ubah formula menjadi value : 
		// contoh : {idkelompok}*{idkelompok} => 100*12
		// print $akumulasiFormula."<br>";
		preg_match_all("/\{([0-9]+)\}/", $formula, $arrmatches);
		if($akumulasiFormula == '') $akumulasiFormula = $formula;
		foreach ($arrmatches[1] as $idkelompok) {

			// cek ada formulanya atau tidak
			$sqlKelompok = "SELECT formula 
				    			  FROM 
				    			  	kelompok_kabupaten 
				    			  WHERE 
				    			  	idkelompok='".$this->db->escape_string($idkelompok)."'";
			$rKelompok = $this->db->query($sqlKelompok);
			$dataKelompok = $this->db->fetchAssoc($rKelompok);
			$formulaK = $dataKelompok['formula'];
			
			// jika kelompok itu masih punya formula maka rekursif
			if($formulaK <> ''){
				$akumulasiFormula = str_replace('{'.$idkelompok.'}', $formulaK, $akumulasiFormula); 
				$nilai = $this->_evalFormula($formulaK,$tahun,$akumulasiFormula,$tab);
				// LogClass::log('replace : '.'{'.$idkelompok.'} menjadi '.$nilai);
			}else{ 	
			  // parsing idkelompoknya 
			   $sqlDetail = "SELECT nilai 
			    			  FROM 
			    			  	kelompok_detail_matrix 
			    			  WHERE 
			    			  	idkelompok='".$this->db->escape_string($idkelompok)."' AND 
			    			  	tahun = '".$this->db->escape_string($tahun)."'";
			   $rDetail = $this->db->query($sqlDetail);
			   $dataDetail = $this->db->fetchAssoc($rDetail);
			   $nilai = $dataDetail['nilai'];	
			   $akumulasiFormula = str_replace('{'.$idkelompok.'}', $nilai, $akumulasiFormula); 	   
			}

			

			 		
		}

		// evaluate
		$sqlEval = "SELECT ($akumulasiFormula) as hasilperhitungan";
		$rEval = $this->db->query($sqlEval);
		$dataEval = $this->db->fetchAssoc($rEval);	
		$hasilperhitungan = $dataEval['hasilperhitungan'];

		if(!is_numeric($hasilperhitungan)) $hasilperhitungan = $akumulasiFormula;

		// LogClass::log($tab."rumuse : ".$formula);
		// LogClass::log($tab."parsing : ".$akumulasiFormula);
		// LogClass::log($tab."sql : ".$sqlEval);
		// LogClass::log($tab."nilai : ".$hasilperhitungan);
		// LogClass::log($tab."\n");

		$tab = $tab.'      ';
		
		return $hasilperhitungan;  			
		
	}

	function getJSON($id){
		$jmode = $_GET['ajaxmode'];
		switch ($jmode) {
			// case 'elemen':
			// return $this->_listelement($id);
			// break;			
			case 'listkelompok_analisa':
				return $this->_listKelompokAnalisa($id);
			break;
			// case 'chart':
			// 	return $this->_chart($id);
			case 'chart_analisa':
				return $this->_chartAnalisa($id);
			break;
			default:
				# code...
			break;
		}
	}
	function FrontList(){
		# 
		// index tahun , opsional, bisa dipilih
		// daftar opsi urusan/grand parent
		
		// batasi pilihan tahun sesui keberadaan data
		$qData = $this->db->query("SELECT MIN(tahun) as min, MAX(tahun) as max FROM kelompok_detail_matrix");
		$tahunData = $this->db->fetchAssoc($qData);

		$opsi = $opsi_analisa = "";
		for ($t = $tahunData['min']; $t <= $tahunData['max']; $t++) {
			// $opsi .= '<label id="btn_'.$t.'" type="button" class="btn btn-default btn-year"><input type="checkbox" class="tahun_chart" id="tahun_chart[]" name="tahun_chart[]" checked="false" value="'.$t.'"> &nbsp;'.$t.'</label>'; //$t;
			$opsi_analisa .= '<label id="btn_'.$t.'" type="button" class="btn btn-default btn-md btn-year btn_tahun_analisa"><input type="checkbox" class="tahun_analisa" id="tahun_analisa[]" name="tahun_analisa[]" checked="false" value="'.$t.'"> &nbsp;'.$t.'</label>'; //$t;
			$tahun_chart[] = $t;
		}

		// Analisa Perbandingan Tabel
		// idanalisis, judul JOIN idanalisis_kelompok,idkelompok,idanalisis 
		$jenis_analisa = "<select id='jenis_analisa' class='form-control' onChange='loadAnalisaChart(this.value);'>
		<option value='0'>-- Pilih Analisa --</option>
		";
		$qAnalisa = $this->db->query("SELECT * FROM analisa_kabupaten");
		while ($rAnalisa = $this->db->fetchAssoc($qAnalisa)) {
			$jenis_analisa .= "<option value=\"{$rAnalisa['idanalisis']}\">{$rAnalisa['judul']}</option>";
			// if (!empty($rAnalisa['']))
		}
		$jenis_analisa .= "</select>";

		$this->pgScript = '
	<script src="{themepath}plugins/Highcharts-4.2.3/js/highcharts.js"></script>
	<script src="{themepath}plugins/Highcharts-4.2.3/js/modules/exporting.js"></script>
	<script src="{themepath}js/analisakabupaten.js"></script>
	<script>
	$(document).ready(function(){
		// checkYear();
		$(\'#jenis_analisa\').select2();
		// loadChart('.$test_id.');
		loadAnalisaChart(\'x\');
		// load3AxisChart(\''.$judul.'\','.$kat.','.$series1.','.$series2.','.$series3.');
		  $(\'input\').iCheck({
		    checkboxClass: \'icheckbox_square\',
		    radioClass: \'iradio_square\',
		    increaseArea: \'-10%\' // optional
		  });

	});
	</script>';

	// $index_analisa = $this->FrontDisplay();
	$series_legend_1 = "
		         <div class='col col-xs-12 col-sm-4 sb1 chart_legend'>
			<div class='no-border'>
			                        <div class='box-body no-border'>
			                            <div class='series1'>
			                            	<div class='series-content'>
			                            		<div class='series-info'>
			                            			<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
			                            			<h3>Parent</h3>
			                            			<div class='series-author'> <i class='fa fa-bookmark-o'></i> <span></span> </div> <br>
			                            			<span>Last update : dasdasdas</span>
			                            		</div>
			                            		<div class='series-value'>
			                            			<div class='series-vb smax'>
														<i class='fa fa-arrow-circle-up'></i>
			                            				<h4>TERTINGGI</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>1.008</div>
			                            			</div>
			                            		</div>
			                            	</div>
			                            	<div class='series-panel'>
			                            	</div>
			                            </div> <!-- /.series1  -->
			                        </div> <!-- /.box body  -->
			                  </div> <!-- /.col  -->
	            		</div>
	";

	$series_legend_2 = "<div class='col col-xs-12 col-sm-4 sb2 chart_legend'>
			<div class='no-border'>
			                        <div class='box-body'>
			                            <div class='series2'>
			                            	<div class='series-content'>
			                            		<div class='series-info'>
			                            			<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
			                            			<h3>Parent</h3>
			                            			<div class='series-author'> <i class='fa fa-bookmark-o'></i> <span></span>   </div> <br>
			                            			<span>Last update : dasdasdas</span>
			                            		</div>
			                            		<div class='series-value'>
			                            			<div class='series-vb smax'>
														<i class='fa fa-arrow-circle-up'></i>
			                            				<h4>TERTINGGI</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>1.008</div>
			                            			</div>
			                            		</div>
			                            	</div>
			                            	<div class='series-panel'>
			                            	</div>
			                            </div> <!-- /.series2  -->
			                        </div> <!-- /.box body  -->
			                  </div> <!-- /.col  -->
			            </div>";

	$series_legend_3 = "
					<!-- SERIES -->
			               <div class='col col-xs-12 col-sm-4 sb3 chart_legend'>
			<div class='no-border'>
			                        <div class='box-body'>
			                            <div class='series3'>
			                            	<div class='series-content'>
			                            		<div class='series-info'>
			                            			<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
			                            			<h3>Parent</h3>
			                            			<div class='series-author'> <i class='fa fa-bookmark-o'></i> <span></span>  </div> <br>
			                            			<span>Last update : dasdasdas</span>
			                            		</div>
			                            		<div class='series-value'>
			                            			<div class='series-vb smax'>
														<i class='fa fa-arrow-circle-up'></i>
			                            				<h4>TERTINGGI</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>1.008</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>1.008</div>
			                            			</div>
			                            		</div>
			                            	</div>
			                            	<div class='series-panel'>
			                            	</div>
			                            </div> <!-- /.series3  -->
			                        </div> <!-- /.box body  -->
			                  </div> <!-- /.col  -->
			                  </div>
					";

	$table = '		
<!-- chart analisa ---->

	        <!--{opsi analisa tahun x-n (tahun data pertama diisi dan data terakhir terisi), lokasi}-->
			<div class="col-sm-12 col-md-12">
	          <div class="box box-info" id="chart_option">
	            <!--<div class="box-header with-border">
	              <h3 class="box-title">Analisa Chart</h3>
	            </div>
	             /.box-header --> 
	             <div class="box-body">
	             	Analisa digunakan untuk membandingkan beberapa indikator atau elemen, <br>
					Pilih analisa yang akan ditampilkan :
	            </div>  
	            <div class="box-body">
					'.$jenis_analisa.' 
	            </div>
	             <div class="box-body btn-group">
					'.$opsi_analisa.' 
	            </div> 
	            <button id="" type="button" class="btn-refresh-analisa-chart btn btn-info btn-md" data-toggle="tooltip" title="" data-original-title="Refresh">
	                  <i class="fa fa-refresh"></i> muat ulang
	            </button>
	            <!--<button id="" type="button" onclick="loadAnalisaChart(1);" class="btn-test-chart btn btn-warning btn-sm" data-toggle="tooltip" title="" data-original-title="Refresh">
	                  <i class="fa fa-refresh"></i> test chart
	            </button>-->
	            <!-- /.box-body -->         
	          </div>
	        </div>

			<!-- /.chart-display -->
			<div class="col-sm-12 col-md-12">
	          <div class="box box-info" id="chart_content">
	            <div class="box-header ui-sortable-handle" style="cursor: move;">
	              <i class="fa fa-fw fa-line-chart"></i>

	              <h3 class="box-title">Chart Analisa</h3>
	              <!-- tools box -->
	              <div class="pull-right box-tools">
	                <button id="" type="button" class="btn-refresh-analisa-chart btn btn-info btn-md" data-toggle="tooltip" title="" data-original-title="Refresh">
	                  <i class="fa fa-refresh"></i></button>
	              </div>
	              <!-- /. tools -->
	            </div>
	            <!-- /.box-header -->   
	            <div class="box-body">
					<div id="chart2" class="bg-gray" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
	            </div>
	            <!-- /.box-body -->   
	            <div class="box-footer">
	            	<div class="row">
	            		'.$series_legend_1.'
	            		'.$series_legend_2.'
	            		'.$series_legend_3.'
	            	</div>
	            </div>     
	            <!-- /.box-footer -->   
	          </div>
	        </div>

	        <!-- /.tabel data analisa dalam angka  -->
			<div class="col-sm-12 col-md-12">
	          <div class="box box-info" id="table_content">
	            <div class="box-header ui-sortable-handle">
	              <div><h3 class="box-title"> <i class="fa fa-fw fa-list"></i> Data Analisa</h3></div>
	              <h4 class="judul_analisa">Tabel Data Analisa : <span></span></h4>
	            </div>
	            <!-- /.box-header -->   
	            <div class="box-body">
	            </div>
	            <!-- /.box-body -->    
	          </div>
	        </div>
			';
		
		$this->menu = new MenuClass;
		$this->link = new LinkClass;
		$this->slider = new SliderClass;
		$this->user = new UserClass;
		
		$define = array (
						 'sitetitle' 	=> SITE_TITLE,	
						 'sitekey' 		=> SITE_KEY,
						 'sitedesc' 	=> SITE_DESC,
						 'menu'			=> $this->menu->FrontDisplay(),
						 'slider'		=> $this->slider->FrontDisplay(),
						 'pagetitle'	=> "ANALISA",
						 'pagecontent'	=> $table,
						 'pagescript'	=> $this->pgScript,

						 'account_menu'	=> $this->user->AccountMenu(),

						 'home'			=> ROOT_URL,
						 'error_tag'	=> ERROR_TAG,
						 'tweetacc' 	=> TWEET_ACC,
						 'fbacc' 		=> FB_ACC,
						 'googleacc' 	=> GOOGLE_ACC,
						 'contactaddr' 	=> CONTACT_ADDR,
						 'contacttelp' 	=> CONTACT_TELP,
						 'contactweb' 	=> CONTACT_WEB,
						 'contactfb' 	=> FB_ACC,
						 'contactfax' 	=> CONTACT_FAX,
						 'contactemail' => CONTACT_EMAIL,
						 'hotline' 		=> HOTLINE,					 						 
				 		 'themepath'  	=> THEME_URL,
                );
		$this->template->init(THEME.'/detail.html');
		$this->template->defineTag($define);
		$this->template->printTpl(); 
	}
	function GetDetail($id){
		# detail artikel
	}

}
?>
