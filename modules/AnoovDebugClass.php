<?php
class AnoovDebugClass extends ModulClass
{
	public function Manage()
	{
		$this->db; // trigger
		$this->cnf; // trigger

		$this->cekAkses();

		dump($this->auth->getDetail());

		dump($this);
		die();
	}
}
