/*
	@author anovsiradj <anov.siradj@gin.co.id>
	@version 20180921
*/

(function() {
	'use strict';

	window.ADMINPANEL = window.ADMINPANEL || false;
	document.body.classList.add(window.ADMINPANEL ? 'admin' : 'guest');

	window.env_is_startpage = window.env_is_startpage || false;
	if(window.env_is_startpage) document.body.classList.add('startpage');

	window.AdminLTEOptions = {
		// jangan modifikasi scroll, bikin berat browser
		navbarMenuSlimscroll: false,
		sidebarSlimScroll: false,
		// toggle
		// sidebarPushMenu: false,
		sidebarExpandOnHover: false,
		/* minimize|maximize|close div
		menurut saya init tidak perlu.
		sementara enable saja dulu, takutnya ada module yg pakai */
		enableBoxWidget: true,
		/* karena ini bukan demo
		dan sidebar di -> hanya opsional,
		jadi sidebar di -> tidak diaktifkan.  */
		enableControlSidebar: false,
		// tidak butuh touchscreen
		enableFastclick: false,
		// chat tidak dipakai
		directChat: {
			enable: false,
		},
	};
})();

/* https://anovsiradj.github.io/cdn/lib/curly-parser.js */
window.CurlyParser = function(string_tpl, obj_data, cb) {
	cb = cb || function(key, obj, result) { return result; };
	return String(string_tpl).replace(/\{(.+?)\}/gm, function(a,b,c,d) {
		return cb(b, obj_data, obj_data[b.trim()]);
	});
};

/* https://jsfiddle.net/anovsiradj/a83rpcf3/ */
window.Text2Color = function(text) {
	function hashCode(str) {
		var hash = 0;
		for (var i = 0; i < str.length; i++) hash = str.charCodeAt(i) + ((hash << 5) - hash);
		return hash;
	}
	function intToARGB(i) {
		return ((i >> 24) & 0xFF).toString(16) +
			((i >> 16) & 0xFF).toString(16) +
			((i >> 8) & 0xFF).toString(16) +
			(i & 0xFF).toString(16);
	}
	return ['#',intToARGB(hashCode(text)).substr(0,6)].join('');
};
