<?php
Class AnalisaClass extends ModulClass
{

	function buildForm(){
		# menampilkan form
		# menampilkan form
		$action = 'ins';
		# get data	
		if($_GET['id'] <> ''){
			$sql = "SELECT a.idanalisis, a.judul, 
					GROUP_CONCAT(ak.idkelompok ORDER BY ak.idkelompok ASC) as kelompok
				FROM analisa_perbandingan a 
				LEFT JOIN analisis_perbandingan_kelompok ak ON (a.idanalisis = ak.idanalisis) 
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
		for ($x = 0; $x < 3; $x++) {
			// $id
			$result = $this->db->query("SELECT * FROM kelompok_matrix WHERE idkelompok=".$kelompok[$x]);
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
		$tplform->init(THEME.'/forms/analisa.html');
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
		$result 	= $this->db->query("select max(idanalisis) as mxid from `analisa_perbandingan`");
		$dmax 		= $this->db->fetchArray($result);
		$newid	 	= $dmax[mxid] + 1; //id baru

		$datausr = $this->auth->getDetail();
		$sql = "INSERT INTO `analisa_perbandingan`
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
				$sql = "INSERT INTO analisis_perbandingan_kelompok 
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
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/analisa'>";				
		}
	}
	function Update(){
		# query update
		$datausr = $this->auth->getDetail();

		$kelompok = $_POST[kelompok];
			
		$sql = "UPDATE analisa_perbandingan 
				SET 
					judul = '".$this->scr->filter($_POST['judul'])."'
				WHERE
					idanalisis = '".$this->scr->filter($_POST['id'])."'";			
		$updateQuery = $this->db->query($sql);

		$qry = "DELETE FROM analisis_perbandingan_kelompok 
				WHERE 
				idanalisis=".$this->scr->filter($_POST['id']);
		$this->db->query($qry);


		foreach($kelompok as $idkelompok) {
			// tambahkan analisa
			if (!empty($idkelompok)) {
				$qry = "INSERT INTO `analisis_perbandingan_kelompok` 
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
			echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/analisa'>";	
			die();			
		} 
	}
	function Delete(){
		# query delete 
		# query delete 
		$sql = "DELETE FROM analisa_perbandingan WHERE idanalisis='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);		
		$sql = "DELETE FROM analisis_perbandingan_kelompok WHERE idanalisis='".$this->scr->filter($_GET['id'])."'";
		$this->db->query($sql);
		echo "<script>alert('data terhapus');</script>";
		echo "<meta http-equiv='refresh' content='0;url=".ROOT_URL."giadmin/analisa'>";
	}
	function Manage(){
		# grid & manajemen data
		// Analisa Perbandingan Tabel analisa_perbandingan, analisis_perbandingan_kelompok
		// idanalisis, judul JOIN idanalisis_kelompok,idkelompok,idanalisis 
		$sql = "SELECT a.idanalisis, a.judul, 
					GROUP_CONCAT(ak.idkelompok ORDER BY ak.idkelompok ASC) as kelompok 
				FROM analisa_perbandingan a 
				LEFT JOIN analisis_perbandingan_kelompok ak ON (a.idanalisis = ak.idanalisis) 
				GROUP BY a.idanalisis
                ORDER BY a.judul ASC";

		$res = $this->db->query($sql);

		// $field = array('Judul Analisa'=>'judul', 'Kelompok'=>'kelompok');	
		$this->title = 'Manajemen Analisa';		
		$this->pgScript = '<script src="{themepath}js/analisa.js?20181012-1"></script>
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
				$q = "SELECT * FROM kelompok_matrix WHERE idkelompok={$scr_idkelp}";
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
						$kelompok .= "<li class='text-red'><i class='fa fa-warning'></i>".$recKelp['urai']." (belum ada data)</li>";
					}
				} else {
					$kelompok .= "<li>".$recKelp['urai']." <span class='text-blue'>(formula)</span></li>";
				}
				// die($q);
			}

			$edit_link = "<a href='".ROOT_URL."giadmin/".$_GET[content]."/".$recAnalisa['idanalisis']."/form.htm' class='fa fa-edit btn btn-primary' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Edit\"></a>";
			$delete_link = "<a  onClick=\"codel('".ROOT_URL."giadmin/".$_GET[content]."/".$recAnalisa['idanalisis']."/del.htm');\" id='del' class='fa fa-times-circle btn btn-danger' data-toggle=\"tooltip\" data-placement=\"top\" title=\"Delete\"></a>	";

			$this->in_content .= "<tr><td>{$x}</td><td>{$recAnalisa['judul']}</td><td>{$kelompok}</td><td nowrap>{$edit_link} {$delete_link}</td></tr>";
		}

		$this->in_content .= "</table>";

		$this->content = $this->in_content;
	}
	function FrontDisplay(){
		# tampilan depan
		$jenis_analisa = "<select id='jenis_analisa' class='form-control' onChange='loadAnalisaChart(this.value);'>
		<option value='0'>-- Pilihan Analisa --</option>
		";
		$qAnalisa = $this->db->query("SELECT * FROM analisa_perbandingan");
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
			                            				<div class='series-value-num'>0.00</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>0.00</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>0.00</div>
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
			                            				<div class='series-value-num'>0.00</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>0.00</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>0.00</div>
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
			                            				<div class='series-value-num'>0.00</div>
														<span>Tahun : 2014</span>	
			                            			</div>
			                            			<div class='series-vb smin'>
														<i class='fa fa-arrow-circle-down'></i>									                            				
			                            				<h4>TERENDAH</h4>
			                            				<div class='series-value-num'>0.00</div>
														<span>Tahun : 2014</span>
			                            			</div>
			                            			<div class='series-vb savg'>
														<i class='fa fa-th-list'></i>
			                            				<h4>RATA-RATA</h4>
			                            				<div class='series-value-num'>0.00</div>
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
	
	private function _listKelompokAnalisa(){
		// membuat list json kelompok berdasrkan ura dicari
		// table kelompok : idkelompok, idparent, urai, formula, satuan 
		// persiapkan untuk custom analisa by user
		$keyword 		= $_GET['keyword']; //<!-- input keyword 

		// susun query berdasrkan akses ke indikator
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$sql_kelompok 	= "SELECT * FROM kelompok_matrix 
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
	
	private function _chartAnalisa($id){
		/*
		 * CHART JSON OUTPUT
		 */
		// load analisa detail
		if($id == 'x'){
			$qAnalisa = $this->db->query("SELECT * FROM analisa_perbandingan order by rand() limit 0,1"); 
		}else{	
			$qAnalisa = $this->db->query("SELECT * FROM analisa_perbandingan WHERE idanalisis=".$this->db->escape_string($id));
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
			<tr><th class='col-xs-1'>".implode("</th><th class='col-xs-1'>",$tahun_chart)."</th></tr>";

		// load analisa items
		$id = $rAnalisa['idanalisis'];
		$sqlAnalisaK = "SELECT * FROM analisis_perbandingan_kelompok WHERE idanalisis=".$this->db->escape_string($id);
		$qAnalisaK = $this->db->query($sqlAnalisaK);

		// $akumulasiFormula = "";
		// $this->_evalFormula("{4424}+4427}+{4430}+{4433}+{4436}+{4439}",2012);
		// die($akumulasiFormula);

		$x = 1;
		while ($rAnalisaK = $this->db->fetchAssoc($qAnalisaK)) {
			// print $rAnalisaK['idkelompok'];
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

			$table .= "<tr><td class='text-center'>{$x}</td>
				<td>{$sa['judul']}</td>
				<td class='text-right'>".implode('</td><td class="text-right">',$sa['data_formatted'])."</td>
				<td class='text-center'>{$sa['satuan']}</td>
				</tr>";
			$x++;
			// print_r($sa);	
		}

		// print_r($this->storedData);

		$table .= "</table>
			<b>keterangan : </b><br>
			<span class='text-gray' style='display:inline-block; width:40px;'>(n/a)</span>: not available (data tidak tersedia)<br>
			<span class='text-red' style='display:inline-block; width:40px;'>(*)</span>: data sementara
		";

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
						FROM kelompok_matrix k 
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
		$this->tahunChart = $tahun_chart;
		// jika hanya satu tahun, bentuknya bar chart
		if (count($tahun_chart) == 1) {$chart_type = "column";}

		// die ($this->_evalFormula('{2658}+{2663}+{2666}+{2669}+{2672}+{2675}',2012));

		// cari nilai di tiap tahunnya
		$data = $nilai_tahun = array();
  		foreach($tahun_chart as $tahun) {
  			// LogClass::log("# $tahun # \n");
  			if($dataKelompok['formula']){
  				$val = $this->_evalFormula($dataKelompok['formula'],$tahun);
  				$this->storedData[$dataKelompok['idkelompok']][$tahun] = $val;

  				if ($val == 0) {
  					$val = $this->_getSavedTemporaryData($dataKelompok['idkelompok'],$tahun);
					$data_formatted[] = ($val > 0) ? "<span class='text-red'>".$this->numen->autoSeparator($val)."*</span>" : "<span class='text-gray'>n/a</span>" ;
  				} else {
					$data_formatted[] = $this->numen->autoSeparator($val);
  				}
  				$data[] = number_format($val,2,'.','');
			 	if ($val > 0) $nilai_tahun[$tahun] = $val;
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

			   	if (!isset($dataDetail['nilai'])) {
					// memulai fungsi pemanggilan data sementara
					$tmp_data = $this->_getTemporaryData($dataKelompok['idkelompok'],$tahun);
					$dataDetail['nilai'] = ($tmp_data['available']) ? $tmp_data['nilai'] : 0 ;
					$tmp_format = ($tmp_data['available']) ? "<span class='text-red'>".$this->numen->autoSeparator($tmp_data['nilai'])."*</span>" : "<span class='text-gray'>n/a</span>" ;
				    $data[] = number_format($dataDetail['nilai'],2,'.','');
					$data_formatted[] = $tmp_format;
				} else {
				    $data[] = $nilai_tahun[$tahun] = number_format($dataDetail['nilai'],2,'.','');
					$data_formatted[] = $this->numen->autoSeparator($dataDetail['nilai']);
				}
			    if ($dataDetail['nilai'] > 0) $nilai_tahun[$tahun] = $dataDetail['nilai'];
			}    
		    
		    $thn[] = $tahun;
		}

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
		$avgdata = $average;

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
			'data_formatted' => $data_formatted,
			'maxdata' => $this->numen->autoSeparator($maxdata),
			'mindata' => $this->numen->autoSeparator($mindata),
			'avgdata' => $this->numen->autoSeparator($avgdata),
			'maxtahun' => $maxtahun,
			'mintahun' => $mintahun,
			'source' => $source,
			'lastupdate' => $lastupdate
		);

		return $kelompok;

	}

	private function _getSavedTemporaryData($idkelompok,$tahun) {
		/*
		 | fungsi pemanggilan data yang tersimpan di strorage $this->storedData;
		 | req : idkelompok, tahun
		 | cpt : dapatkan data tahun sebelumnya yg bukan 0
		 */
		if (array_sum($this->storedData[$idkelompok]) == 0) { // jumlah seluruh tahun = 0
			return 0;
		} else if (array_sum($this->storedData[$idkelompok]) > 0) {
			$kdata = $this->storedData[$idkelompok];
			krsort($kdata);

			foreach ($kdata as $thn => $nil) {
				if ($thn < $tahun && $nil > 0) {
					return $nil;
				}
			}
		}
	}

	private function _getTemporaryData($idkelompok,$tahun) {
		/*
		 | fungsi pemanggilan data yang belum tersedia untuk sementara
		 | req : idkelompok, tahun
		 | cpt : dapatkan data tahun sebelumnya yg bukan 0
		 */
		$sqlDetail = "SELECT *
		FROM `kelompok_detail_matrix` 
		WHERE idkelompok='".$this->scr->filter($idkelompok)."' 
			AND tahun<".$this->scr->filter($tahun)." 
		ORDER BY tahun DESC LIMIT 1";
		$qDetail = $this->db->query($sqlDetail);
  		$rDetail = $this->db->fetchAssoc($qDetail);

  		if (isset($rDetail['nilai']) || isset($rDetail['set_nilai'])) {
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
			$sqlKelompok = "SELECT formula 
				    			  FROM 
				    			  	kelompok_matrix 
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
			   $nilai = ($dataDetail['nilai'] == '') ? 0 : $dataDetail['nilai'];	
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

	function FrontList()
	{
		// index tahun , opsional, bisa dipilih
		// daftar opsi urusan/grand parent

		// batasi pilihan tahun sesui keberadaan data
		$qData = $this->db->query('SELECT MIN(tahun) as `min`, MAX(tahun) as `max` FROM kelompok_detail_matrix');
		$tahunData = $this->db->fetchAssoc($qData);

		$opsi = $opsi_analisa = "";
		for ($t = $tahunData['min']; $t <= $tahunData['max']; $t++) {
			// $opsi .= '<label id="btn_'.$t.'" type="button" class="btn btn-default btn-year"><input type="checkbox" class="tahun_chart" id="tahun_chart[]" name="tahun_chart[]" checked="false" value="'.$t.'"> &nbsp;'.$t.'</label>'; //$t;
			$opsi_analisa .= '<label id="btn_'.$t.'" type="button" class="btn btn-default btn-md btn-year btn_tahun_analisa"><input type="checkbox" class="tahun_analisa" id="tahun_analisa[]" name="tahun_analisa[]" checked="false" value="'.$t.'"> &nbsp;'.$t.'</label>'; //$t;
			$tahun_chart[] = $t;
		}

		// Analisa Perbandingan Tabel
		// idanalisis, judul JOIN idanalisis_kelompok,idkelompok,idanalisis 
		$jenis_analisa = "
			<select id='jenis_analisa' class='form-control' onChange='loadAnalisaChart(this.value);'>
				<option value='0'>-- Pilihan Analisa --</option>";
		$qAnalisa = $this->db->query("SELECT * FROM analisa_perbandingan");
		while ($rAnalisa = $this->db->fetchAssoc($qAnalisa)) {
			$jenis_analisa .= "<option value=\"{$rAnalisa['idanalisis']}\">{$rAnalisa['judul']}</option>";
		}
		$jenis_analisa .= "</select>";

		$this->pgScript = '
			<script src="{themepath}plugins/Highcharts-4.2.3/js/highcharts.js"></script>
			<script src="{themepath}plugins/Highcharts-4.2.3/js/modules/exporting.js"></script>
			<script src="{themepath}js/analisa.js?20181012-1"></script>
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
									<div class='series-author'>
										<i class='fa fa-bookmark-o'></i>
										<span></span>
									</div>
									<br>
									<span>Last update : dasdasdas</span>
								</div>
								<div class='series-value'>
									<div class='series-vb smax'>
										<i class='fa fa-arrow-circle-up'></i>
										<h4>TERTINGGI</h4>
										<div class='series-value-num'>0.00</div>
										<span>Tahun : 2014</span>
									</div>
									<div class='series-vb smin'>
										<i class='fa fa-arrow-circle-down'></i>
										<h4>TERENDAH</h4>
										<div class='series-value-num'>0.00</div>
										<span>Tahun : 2014</span>
									</div>
									<div class='series-vb savg'>
										<i class='fa fa-th-list'></i>
										<h4>RATA-RATA</h4>
										<div class='series-value-num'>0.00</div>
									</div>
								</div>
							</div>
							<div class='series-panel'></div>
						</div> <!-- /.series1  -->
					</div> <!-- /.box body  -->
				</div> <!-- /.col  -->
			</div>";

		$series_legend_2 = "
			<div class='col col-xs-12 col-sm-4 sb2 chart_legend'>
				<div class='no-border'>
					<div class='box-body'>
						<div class='series2'>
							<div class='series-content'>
								<div class='series-info'>
									<h1>Perbandingan Jumlah sekolah dengan jumlah siswa</h1>
									<h3>Parent</h3>
									<div class='series-author'>
										<i class='fa fa-bookmark-o'></i>
										<span></span>
									</div>
									<br>
									<span>Last update : dasdasdas</span>
								</div>
								<div class='series-value'>
									<div class='series-vb smax'>
										<i class='fa fa-arrow-circle-up'></i>
										<h4>TERTINGGI</h4>
										<div class='series-value-num'>0.00</div>
										<span>Tahun : 2014</span>
									</div>
									<div class='series-vb smin'>
										<i class='fa fa-arrow-circle-down'></i>
										<h4>TERENDAH</h4>
										<div class='series-value-num'>0.00</div>
										<span>Tahun : 2014</span>
									</div>
									<div class='series-vb savg'>
										<i class='fa fa-th-list'></i>
										<h4>RATA-RATA</h4>
										<div class='series-value-num'>0.00</div>
									</div>
								</div>
							</div>
							<div class='series-panel'></div>
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
									<div class='series-author'>
										<i class='fa fa-bookmark-o'></i>
										<span></span>
									</div>
									<br>
									<span>Last update : dasdasdas</span>
								</div>
								<div class='series-value'>
									<div class='series-vb smax'>
										<i class='fa fa-arrow-circle-up'></i>
										<h4>TERTINGGI</h4>
										<div class='series-value-num'>0.00</div>
										<span>Tahun : 2014</span>
									</div>
									<div class='series-vb smin'>
										<i class='fa fa-arrow-circle-down'></i>
										<h4>TERENDAH</h4>
										<div class='series-value-num'>0.00</div>
										<span>Tahun : 2014</span>
									</div>
									<div class='series-vb savg'>
										<i class='fa fa-th-list'></i>
										<h4>RATA-RATA</h4>
										<div class='series-value-num'>0.00</div>
									</div>
								</div>
							</div>
							<div class='series-panel'></div>
						</div> <!-- /.series3  -->
					</div> <!-- /.box body  -->
				</div> <!-- /.col  -->
			</div>";

		$table = '
			<!-- chart analisa ---->
			<!--{opsi analisa tahun x-n (tahun data pertama diisi dan data terakhir terisi), lokasi}-->
			<div class="col-sm-12 col-md-12">
				<div class="box box-info" id="chart_option">
					<!--<div class="box-header with-border"><h3 class="box-title">Analisa Chart</h3></div>/.box-header -->
					<div class="box-body">
						Analisa digunakan untuk membandingkan beberapa indikator atau elemen,
						<br>Pilih analisa yang akan ditampilkan :
					</div>
					<div class="box-body">'.$jenis_analisa.'</div>
					<div class="box-body btn-group">'.$opsi_analisa.'</div>
					<button id="" type="button" class="btn-refresh-analisa-chart btn btn-info btn-md" data-toggle="tooltip" title="" data-original-title="Refresh">
						<i class="fa fa-refresh"></i> muat ulang
					</button>
					<!--<button id="" type="button" onclick="loadAnalisaChart(1);" class="btn-test-chart btn btn-warning btn-sm" data-toggle="tooltip" title="" data-original-title="Refresh"><i class="fa fa-refresh"></i> test chart</button>-->
					<!-- /.box-body -->
				</div>
			</div>

			<!-- /.chart-display -->
			<div class="col-sm-12 col-md-12">
				<div class="box box-info" id="chart_content">
					<div class="box-header ui-sortable-handle" style="cursor: move;">
						<i class="fa fa-fw fa-line-chart"></i>
						<h3 class="box-title">Grafik Analisa</h3>
						<!-- tools box -->
						<div class="pull-right box-tools">
							<button id="" type="button" class="btn-refresh-analisa-chart btn btn-info btn-md" data-toggle="tooltip" title="" data-original-title="Refresh">
								<i class="fa fa-refresh"></i>
							</button>
						</div><!-- /. tools -->
					</div><!-- /.box-header -->
					<div class="box-body"><div id="chart2" class="bg-gray" style="min-width: 310px; height: 400px; margin: 0 auto"></div></div><!-- /.box-body -->
					<div class="box-footer">
						<div class="row">
							'.$series_legend_1.'
							'.$series_legend_2.'
							'.$series_legend_3.'
						</div>
					</div><!-- /.box-footer -->
				</div>
			</div>

			<!-- /.tabel data analisa dalam angka  -->
			<div class="col-sm-12 col-md-12">
				<div class="box box-info" id="table_content">
					<div class="box-header ui-sortable-handle">
						<div>
							<h3 class="box-title">
								<i class="fa fa-fw fa-list"></i> Data Analisa
							</h3>
						</div>
						<h4 class="judul_analisa">Tabel Data Analisa : <span></span></h4>
					</div><!-- /.box-header -->
					<div class="box-body"></div><!-- /.box-body -->
				</div>
			</div>';

		$menu = new MenuClass;
		$link = new LinkClass;
		$user = new UserClass;

		$this->template->init(THEME.'/detail.html', [
			'sitetitle' => SITE_TITLE,
			'sitekey' => SITE_KEY,
			'sitedesc' => SITE_DESC,

			'menu'       => $menu->FrontDisplay('T'),
			'menufooter' => $menu->FrontDisplay('B'),

			'pagetitle' => "ANALISA",
			'pagecontent' => $table,
			'pagescript' => $this->pgScript,

			'account_menu'	=> $user->AccountMenu(),

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
		]);
		$this->template->printTpl(); 
	}

}
