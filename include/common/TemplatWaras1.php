<?php
/**
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20180922
* 
* $ctx = TemplatWaras1::init();
* 
* // dilakukan dari module:
* $ctx->set_root($this);
* $ctx->load($path,$data);
* 
* // dilakukan dari template:
* $ctx->get_root()->*;
* $ctx->open('part');
* $ctx->close();
* 
*/

class TemplatWaras1
{
	protected static $self_instance;

	protected $root_instance;
	protected $cfg = array(
		'opened' => false,
		'part' => null,
		'data' => array(),
	);

	private function __construct() {}
	public static function &init()
	{
		if (!isset(static::$self_instance)) static::$self_instance = new self;
		return static::$self_instance;
	}

	public function open($part)
	{
		if ($this->cfg['opened']) throw new Exception(sprintf('bagian "%s" belum ditutup', $this->cfg['part']), 1);
		$this->cfg['opened'] = true;
		$this->cfg['part'] = $part;
		ob_start();
	}

	public function close()
	{
		if (!$this->cfg['opened']) throw new Exception(sprintf('bagian "%s" belum dibuka', $this->cfg['part']), 1);
		$this->root_instance->{$this->cfg['part']} .= ob_get_clean();
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

	public function root(&$root)
	{
		$this->root_instance =& $root;
	}
	public function set_root(&$root)
	{
		$this->root_instance =& $root;
	}
	public function &get_root()
	{
		return $this->root_instance;
	}

	public function load($path,$data = array())
	{
		$this->data($data);
		TemplatWaras1_load($path,$this->cfg['data']);
	}
}

function TemplatWaras1_load($TemplatWaras1_load_path,$TemplatWaras1_load_data)
{
	ob_start();
	extract($TemplatWaras1_load_data);
	require $TemplatWaras1_load_path;
	ob_end_clean();
}
