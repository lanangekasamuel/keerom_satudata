<?php
class GIS_Class extends ModulClass
{
	public function Manage()
	{
		$TemplatWaras1 = TemplatWaras1::init();
		$TemplatWaras1->set_root($this);

		$TemplatWaras1->data([
			'sup' => $this->data('sup'),
			'subs' => $this->data('subs'),
		]);

		$TemplatWaras1->load(ROOT_PATH.'/themes/'.THEME.'/_/gis/manage.tpl');
	}

	public function FrontList()
	{
		$TemplatWaras2 = TemplatWaras2::init();
		$TemplatWaras2->set_root($this->template);
		$TemplatWaras2->block([
			'sitetitle' => SITE_TITLE,
			'sitekey' => SITE_KEY,
			'sitedesc' => SITE_DESC,
		]);

		$TemplatWaras2->load(THEME_PATH . '/_/gis/frontlist.tpl', [
			'sup' => $this->data('sup'),
			'subs' => $this->data('subs'),
		]);
		$menu = new MenuClass;
		$user = new UserClass;
		$TemplatWaras2->get_root()->init(THEME.'/detail.html', [
			'menu'         => $menu->FrontDisplay('T'),
			'menufooter'   => $menu->FrontDisplay('B'),
			'account_menu' => $user->AccountMenu(),
			'home'     => ROOT_URL,
			'contactaddr'  => CONTACT_ADDR,
			'contacttelp'  => CONTACT_TELP,
			'contactweb'   => CONTACT_WEB,
			'contactfb'    => FB_ACC,
			'contactfax'   => CONTACT_FAX,
			'contactemail' => CONTACT_EMAIL,
			'hotline'      => HOTLINE,
			'themepath'    => THEME_URL,
		]);
		$TemplatWaras2->get_root()->printTpl();
	}

	public function getJSON()
	{
		switch ($_GET['ajaxmode']) {
			case 'save':
				$this->_save();
				break;
			default: echo '{}'; break;
		}
	}

	public function _save()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			foreach ($_POST as $kodepemda => $polygon) {
				if (preg_match('/^[\d]+$/', $kodepemda)) {
					$q = $this->db->ci3db
					->from('kabupaten_gis')
					->where('kodepemda', $kodepemda)
					->set('geom',"ST_POLYGONFROMTEXT({$this->db->ci3db->escape($polygon)})",false)
					->limit(1)->update();

					if (!$q) http_response_code(500);
				}
			}
		}
	}

	public function data($type)
	{
		if ($type === 'sup') {
			$sup = $this->db->ci3db
			->query("SELECT ST_AsText(geom) as poly,kabupaten as name,lat,lng from kabupaten_gis Where kodepemda = '' limit 1")
			->row();
			$sup->lat = (float) $sup->lat;
			$sup->lng = (float) $sup->lng;
			return $sup;
		} elseif ($type === 'subs') {
			// semua sub
			$subs = [];
			$q = $this->db->ci3db->query("SELECT ST_AsText(geom) as poly,kabupaten as name,kodepemda from kabupaten_gis Where kodepemda != '' order by kodepemda");
			foreach ($q->result() as $b) {
				$b->lat = (float) $b->lat;
				$b->lng = (float) $b->lng;
				$subs[] = $b;
			}
			return $subs;
		}

		// satu sub
		$sub = $this->db->ci3db
		->query("SELECT ST_AsText(geom) as poly,kabupaten as name,kodepemda from kabupaten_gis Where kodepemda = {$this->db->ci3db->escape($type)} limit 1")
		->row();
		$sub->lat = (float) $sub->lat;
		$sub->lng = (float) $sub->lng;
		return $sub;
	}
}
