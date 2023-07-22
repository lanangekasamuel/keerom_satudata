<?php

/**
* 
* WilayahClass
* 
* entah provinsi,kabupaten,distrik/kecamatan,desa,terserah.
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20181017
* 
*/

class WilayahClass extends ModulClass
{
	public $prefix0 = 'Distrik';

	/*
		~/ajax/distrik/action/pk
	*/
	public function getJSON($pk)
	{
		header('Content-Type: application/json');

		switch ($_GET['ajaxmode']) {
			case 'frontlist_contents':
				return json_encode($this->frontlist_contents($pk));
				break;
			default: return '{}'; break;
		}
	}

	protected function frontlist_contents($pk)
	{
		$year = (int) date('Y');
		$params = [];
		foreach ($_GET as $k => $v) $params[$k] = $v; // 1st
		foreach ($_POST as $k => $v) $params[$k] = $v; // 2nd

		$that =& $this;
		$thet = new stdClass;
		$thet->pk = $pk;
		$thet->is_tr_skpd = [];
		$thet->i = 0;
		$thet->numb = [];
		$thet->levl = [];
		$thet->min = isset($params['min']) ? (int) $params['min'] : $year;
		$thet->max = isset($params['max']) ? (int) $params['max'] : $year;
		$thot = [];

		$recursive = function($pk,$recursive) use (&$that,&$thet,&$thot) {
			$q = $that->db->ci3db->query("SELECT
				a.*,
				b.nama_instansi
				from kelompok_kabupaten a
				left join instansi b on (b.idinstansi = a.pk_instansi)
				where a.idparent = {$pk}
				and a.publish = 1
				order by a.pk_instansi,a.ordering,a.idkelompok,a.idparent
			");
			if($q) foreach ($q->result_array() as $b) {
				// skpd. untuk <tr> header indikator
				if (!isset($thet->is_tr_skpd[$b['pk_instansi']])) {
					$thet->is_tr_skpd[$b['pk_instansi']] = 1;
					$thet->i++;
					$thot[] = [
						'_i' => $thet->i,
						'_type' => 1,
						'nama_skpd' => htmlentities($b['nama_instansi']),
						'kode_skpd' => $b['pk_instansi'],
					];
				}

				// apakah indikator masih punya sub-indikator lagi?
				$subs = $that->db->ci3db->query("
					SELECT count(*)
					from kelompok_kabupaten a
					where a.idparent = {$b['idkelompok']}
					and a.publish = 1
				");
				$subs = $subs ? (int) current($subs->row_array()) : 0;

				// indikator. parent maupun children
				$thet->i++;
				$b['_i'] = $thet->i;
				$b['_type'] = $subs > 0 ? 2 : 3;
				$b['_numb'] = $thet->numb[$b['idparent']] = ($thet->numb[$b['idparent']] ?: 0)+1;
				$b['_levl'] = $thet->levl[$b['idkelompok']] = ($b['idparent'] == 0) ? 0 : ($thet->levl[$b['idparent']]+1);
				$b['uraian'] = str_repeat('&nbsp; &nbsp; ', $b['_levl']) . "{$b['_numb']}. " . htmlentities($b['urai']);

				if ($b['_type'] == 3) {
					$b['years'] = [];
					$qq = $that->db->ci3db->query("
						SELECT a.tahun,a.nilai from kelompok_detail_kabupaten a
						where a.idkelompok = {$b['idkelompok']}
						and a.idkabupaten = {$that->db->escape($thet->pk)}
						and a.tahun >= {$thet->min}
						and a.tahun <= {$thet->max}
						order by a.tahun
					");
					if($qq)
						foreach ($qq->result() as $bb)
							$b['years'][(int) $bb->tahun] = (int) $bb->nilai;
					// for ($i=$thet->min; $i <= $thet->max; $i++)
						// $b['years'][$i] = $b['years'][$i] ?: 0;
					// ksort($b['years']);
				}

				$thot[] = $b;

				$recursive((int) $b['idkelompok'], $recursive);
			}
		};

		$recursive(0, $recursive);

		return $thot;
	}

	public function FrontList()
	{
		$TemplatWaras2 = TemplatWaras2::init();
		$year = (int) date('Y');

		$q = $this->db->ci3db
		->from('kabupaten_gis')
		->select([
			'kabupaten as namawilayah',
			'kodepemda as kodewilayah',
		])
		->where(['kodepemda !=' => ''])
		->order_by('kodepemda')
		->get();
		$wilayah_list = [];
		foreach ($q->result_array() as $b) $wilayah_list[] = $b;

		$b = $this->db->ci3db->query("SELECT
			min(a.tahun) as `min`,
			max(a.tahun) as `max`
			from kelompok_detail_kabupaten a
			left join kelompok_kabupaten b on (b.idkelompok = a.idkelompok)
			where b.idkelompok is not null
		")->row();
		$min_tahun = $b ? (int) $b->min : 0;
		$min_tahun = $min_tahun === 0 ? $year : $min_tahun;
		$max_tahun = $b ? (int) $b->max : $year;
		$max_tahun = $max_tahun === 0 ? $year : $max_tahun;

		$TemplatWaras2->load(THEME_PATH.'/_/wilayah/frontlist.tpl', [
			'prefix0' => $this->prefix0,
			'wilayah_list' => &$wilayah_list,
			'min_tahun' => $min_tahun,
			'max_tahun' => $max_tahun,
		]);

		$menu = new MenuClass;
		$user = new UserClass;
		$TemplatWaras2->get_root()->init(THEME.'/detail.html', [
			'pagetitle' => "Data Indikator {$this->prefix0}",
			'account_menu' => $user->AccountMenu(),
			'menu'         => $menu->FrontDisplay('T'),
			'menufooter'   => $menu->FrontDisplay('B'),
			'sitetitle' => SITE_TITLE,
			'home' => ROOT_URL,
			'themepath' => THEME_URL,
		]);
		$TemplatWaras2->get_root()->printTpl();
	}
}
