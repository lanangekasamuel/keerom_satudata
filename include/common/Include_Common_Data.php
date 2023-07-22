<?php
/**
* 
* Data
* 
* [20180924060018][anovedit][todo:]
* tujuannya adalah, sebagai abstract dari ./configclass,
* karena configclass nantinya akan di ~/.gitignore kan,
* jadi saya buat class ini, sebagai extended untuk modifikasi configclass.
* karena configclass sudah dipanggil dimana-mana, dan saya malas kalau terjadi breaking=changes dan merubahnya semuanya.
* 
* jadi begini rencananya...
* nanti ada methods|variables tambahan di configclass,
* yg akan saya gunakan sebagai collectons|data-provider,
* yaitu menyimpan data secara global dan seca, tanpa constants,$_GLOBALS maupun lainnya.
* 
* @author anovsiradj <anov.siradj@gin.co.id>
* @version 20180924
* 
*/

class Include_Common_Data {
	public function __construct() {}
}
