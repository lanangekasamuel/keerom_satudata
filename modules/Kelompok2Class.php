<?php // 20180923000701

// $this;dump($_GET,$_POST,get_defined_vars(),get_defined_constants(true)['user']);die();

class Kelompok2Class extends KelompokClass
{
	public function Manage()
	{
		$TemplatWaras1 = TemplatWaras1::init();
		$TemplatWaras1->root($this);

		$this->title .= '<i class="fa fa-th-list"></i> Kelompok Indikator';

		$md_kelp = $this->scr->filter(strtolower($_GET['cntmode']));
		$this->pilahKelompok($md_kelp);
		$TemplatWaras1->data('modekelompok', $this->mode_kelompok);

		$use_seleksi_instansi = false;
		$use_seleksi_kelompok = false;
		$use_seleksi_kabupaten = false;
		$this->cekAkses();
		if ($this->userAkses == 'admin') {
			$this->title .= htmlentities(' <Admin> ');
			if (preg_match('/^sipd|supd$/', $this->mode_kelompok)) {
				$use_seleksi_kelompok = true;
			} elseif (preg_match('/^matrik|kabupaten$/', $this->mode_kelompok)) {
				$use_seleksi_instansi = true;
			}
		} // admin

		$selected_instansi = null;
		if (isset($_GET['instansi']) && $_GET['instansi'] > 0) $selected_instansi = $_GET['instansi'];
		$TemplatWaras1->data('selectedskpd',$selected_instansi);

		$TemplatWaras1->data('showoptionskpd',$use_seleksi_instansi);
		if ($use_seleksi_instansi) {
			if (preg_match('/^matrik|kabupaten$/', $this->mode_kelompok)) {
				
			}
		} // use_seleksi_instansi

		$TemplatWaras1->load(ROOT_PATH.'/themes/'.THEME.'/_/kelompok.tpl');
	}


}
