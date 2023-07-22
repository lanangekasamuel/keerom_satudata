<?php
/**
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20181003
* 
* $ctx = TemplatWaras1::init();
* 
* // dilakukan dari module:
* $ctx->set_root($this);
* $ctx->data(...);
* $ctx->block(...);
* $ctx->load($path,$data);
* $ctx->get_root()->init(THEME.'/detail.html');
* $ctx->get_root()->parse(); // alt
* $ctx->get_root()->printTpl(); // alt
* 
* // dilakukan dari template:
* $ctx->get_root()->*;
* $ctx->open('part');
* $ctx->close();
* 
*/

class TemplatWaras2
{
	protected static $self_instance;

	protected $root_instance;
	private $cfg = [
		'opened' => false,
		'part' => null,
		'data' => array(),
	];

	private function __construct()
	{
		$this->root_instance = new TemplateClass;
	}
	public static function &init()
	{
		if (!isset(static::$self_instance)) static::$self_instance = new self;
		return static::$self_instance;
	}

	public function set_root(&$root) { $this->root_instance =& $root; }
	public function &get_root() { return $this->root_instance; }

	public function block($data = [], $v = null)
	{
		$k;
		if (is_string($data)) {
			$k = $data;
			$data = [$k => $v];
		}
		foreach ($data as $k => $v) {
			if (is_array($v) || is_object($v)) throw new Exception(sprintf('block "%s" harus string/integer/float', $k), 1);
			$this->root_instance->TAGS[$k] = $v;
		}
		unset($data,$k,$v);
	}

	/* block_open */
	public function open($part)
	{
		if ($this->cfg['opened']) throw new Exception(sprintf('bagian "%s" belum ditutup', $this->cfg['part']), 1);
		$this->cfg['opened'] = true;
		$this->cfg['part'] = $part;
		ob_start();
	}

	/* block_close */
	public function close()
	{
		if (!$this->cfg['opened']) throw new Exception(sprintf('bagian "%s" belum dibuka', $this->cfg['part']), 1);
		$this->root_instance->TAGS[$this->cfg['part']] = ob_get_clean();
		$this->cfg['opened'] = false;
		$this->cfg['part'] = null;
	}

	public function data($data = array(), $v = null)
	{
		if (is_string($data)) {
			if (isset($v)) $data = array($data => $v);
			else {
				if (!array_key_exists($data, $this->cfg['data'])) $this->cfg['data'][$data] = $v;
				return $this->cfg['data'][$data];
			}
		}
		foreach (array_keys($data) as $k) {
			if (is_object($data[$k]) || is_array($data[$k])) {
				$this->cfg['data'][$k] =& $data[$k];
			} else $this->cfg['data'][$k] = $data[$k];
		}
	}

	public function load($path,$data = array())
	{
		$this->data($data);
		TemplatWaras2_load($path,$this->cfg['data']);
	}
}

function TemplatWaras2_load($TemplatWaras2_load_path,$TemplatWaras2_load_data)
{
	ob_start();
	extract($TemplatWaras2_load_data);
	require $TemplatWaras2_load_path;
	ob_end_clean();
}
