$(document).ready(function(){
	// alert('a');
    // $('#tbl_griddata').DataTable();
    // $('nav.navbar-static-top').data('step','100');
    // $('nav.navbar-static-top').data('intro','pilih menu berikut untuk mengakses langsung halaman');
     // data-step="1" data-intro="pilih menu berikut untuk mengakses langsung halaman"
    $('a#btn_intro').on('click',function(){
    	startIntro();
    	// alert($('nav.navbar-static-top').data('intro'));
    });
    // $('a#btn_intro_adm').on('click',function(){
    // 	startIntro();
    // 	// alert($('nav.navbar-static-top').data('intro'));
    // });
    // if (ADMINPANEL == undefined) {
    // 	if (ADMINPANEL) {
    // 		$('li.dropdown.user.user-menu').removeAttr('data-step data-intro');
    // 	}
    // }
});

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for(var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

function checkCookie() {
    var user = getCookie("username");
    if (user != "") {
        alert("Welcome again " + user);
    } else {
        user = prompt("Please enter your name:", "");
        if (user != "" && user != null) {
            setCookie("username", user, 365);
        }
    }
}

// intro

function startIntro_begin(){
	// opsi hideintro
	var ishideintro = getCookie('hideintro');
	ischecked = (ishideintro == 1) ? 'checked=checked' : '' ;
	/* intro saat pertama kali membuka website pusdalisbang*/
	var intro = introJs();
	  intro.setOptions({
	    steps: [
	      {
	        element: '#step1',
	        intro: "<div class=\"text-center\" style=\"white-space:nowrap;\">"+
	        	"<img src=\""+PUSDAHOST+"/files/images/logo_provinsi_shaded.png\" height=\"200\" style=\"height:200px;\">"+
	        	"<img src=\""+PUSDAHOST+"/files/images/logo_pusdalisbang_shaded.png\" height=\"200\" style=\"height:200px;\">"+
	        	"<div>Selamat datang di</div>"+
	        	"<div class=\"intro-content-title\">Website <b></b><br>KEEROM PU DATA</div>"+
	        	"<br><br>"+
	        	"<div class=\"text-right\"><small><label><input type=\"checkbox\" id=\"trg_hideintro\" onchange=\"cek_hideintro();\" "+ischecked+"> Jangan Tampilkan ini lagi</label></small></div>"+
	        	"</div>"
	      },{
	        element: '#btn_intro',
	        intro: "<b>Ingat!</b>, gunakan tombol '<i class=\"fa fa-question-circle\"></i> Tips' ini memperoleh bantuan atau tip-tip lainnya",
	        position: 'left'
	      },{
	        element: '#intro_button',
	        intro: "Klik untuk memulai lagi",
	        position: 'left'
	      }
	    ]
	  });
	  // intro_button

	  intro.start().oncomplete(function() {
      		// $('a#btn_intro').click();
      		// start on page intro
      		var page_intro = introJs();
  			setTimeout(function(){page_intro.start();},900);
    	});
}

function cek_hideintro() {
	if ($('#trg_hideintro').is(':checked')) {
		setCookie('hideintro',1,1);
		// var intro = introJs();
		// intro.stop();
		$('.introjs-button.introjs-skipbutton').click();
	} else {
		setCookie('hideintro',0,1);
	}
	console.log(getCookie('hideintro'));
}

function startIntro_page(pagetitle){
	var intro = introJs();
	var using_intro = true;
	if (pagetitle == 'BADAN' || pagetitle == 'DINAS' || pagetitle == 'BIRO'
		|| pagetitle == 'KANTOR' || pagetitle == 'RUMAH SAKIT' || pagetitle == 'UNIT LAYANAN'
		|| pagetitle == 'SEKRETARIAT' || pagetitle == 'INSTANSI') {
	  	intro_steps = [
		    {
	    		element : 'div.list-walidata',
	    		intro: "<b>Daftar Walidata dalam "+pagetitle+"</b><br>",
	    		position:'top'
		   	},{
		        element: 'div.info-box',
		        intro: "informasi tentang walidata"
		    },{
		        element: 'div.info-box div.info-box-content span.info-box-text:last-child button',
		        intro: "Klik untuk menampilkan indikator dari walidata yang bersangkutan!"
		    },{
		      	element: 'div.pagination-area ul.pagination',
		      	intro : "<b>Navigasi Halaman</b><br>gunakan untuk melihat walidata yang lain"
		    }
	    ];
	} else if (pagetitle == 'GERBANGMAS') {
	  	intro_steps = [
	    	{
	    		element : 'div#gerbangmas_fokus',
	    		intro: "<b>Fokus Program Gerbangmas</b><br>",
	    		position:'right'
	    	},{
	    		element : 'div#gerbangmas_program',
	    		intro: "sekilas tujuan program Gerbangmas<br>",
	    		position:'left'
	    	},{
	    		element : 'div#gerbangmas_wilayah',
	    		intro: "<b>Wilayah Gerbangmas</b><br>berikut adalah data wilayah program gerbangmas dan beberapa indikator penting di wilayah tersebut",
	    	},{
	    		element : 'div#content_peta',
	    		intro: "<b>Peta Interaktif Wilayah Gerbangmas</b><br>berikut adalah data wilayah program gerbangmas dalam bentuk peta interaktif",
	    		position:'top'
	    	},{
	    		element : 'a#btn_map_tips',
	    		intro: "selengkapnya tentang tip-tip dalam peta bisa men-click ini",
	    		position:'bottom left'
	    	}
	    ];
	} else if (pagetitle == 'ANALISA') {
	  	intro_steps = [
		    {
		        element: 'div#chart_option',
		        intro: "<b>Bagian Opsi Analisa</b><br>dibagian ini terdapat pilihan analisa apa yang akan ditampilkan dan juga tahun - tahun analisanya"
		    },{
		        element: 'span.select2',
		        intro: "Pilih Analisa yang akan ditampilkan!"
		    },{
		        element: 'div.box-body.btn-group',
		        intro: "Pilih Tahun-tahun yang akan di muat datanya!"
		    },{
		        element: 'button.btn-refresh-analisa-chart',
		        intro: "tombol mulai / muat ulang Analisa"
		    },{
		        element: 'div#chart_content',
		        intro: "<b>Bagian Chart</b><br>Analisa ditampilkan dalam bentuk chart dan summary dapat dilihat dibagian bawah",
		        position :"top"
		    },{
		        element: 'div.pull-right.box-tools button.btn-refresh-analisa-chart',
		        intro: "tombol mulai / muat ulang Analisa",
		        position :"left"
		    },{
		        element: 'div#chart2',
		        intro: "tampilan chart analisa",
		        position :"bottom"
		    },{
		        element: 'div#chart_content div.box-footer',
		        intro: "<b>Summary Hasil Analisa</b><br>menampilkan asal data, nilai minimum, maximum dan rata-rata setiap indikator",
		        position :"top"
		    },{
		        element: 'div#table_content',
		        intro: "<b>Analisa dalam bentuk tabel</b><br>",
		        position :"top"
		    }
	    ];
	} else if (pagetitle == 'PETA INTERAKTIF WILAYAHADAT' || pagetitle == 'PETA INTERAKTIF PROVINSI'
		|| pagetitle == 'PETA INTERAKTIF KABUPATEN' || pagetitle == 'PETA INTERAKTIF TEMATIK') {
	 	var using_intro = false;
	  	startIntro_map(pagetitle); // direct langsung ke intro peta
	} else if (pagetitle == 'OUTPUT PUSDALISBANG') {
	  	intro_steps = [
	    	{
	    		element : 'div#output_list',
	    		intro: "Daftar otput pusdalisbang, pilih untuk memulai<br>",
	    		position:'left'
	    	},{
	    		element : 'div#output_content',
	    		intro: "konten / isi dari output pusdalisbang<br>",
	    		position:'right'
	    	},{
	    		element : 'div#output_content div.box-header',
	    		intro: "Judul dan keterangan output pusdalisbang<br>",
	    	},{
	    		element : 'div#output_content div.box-body',
	    		intro: "isi Dokumen",
	    		position:'right'
	    	}
	    ];
	} else if (pagetitle == '8 KELOMPOK DATA') {
	  	intro_steps = [
	    	{
	    		element : 'div#accordion',
	    		intro: "Daftar Kelompok Data dan Jenis data dari 8 kelompok data<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning:first-child',
	    		intro: "pilih / klik Kelompok Data untuk menampilkan Jenis Data<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning:first-child',
	    		intro: "pilih / klik Kelompok Data untuk menampilkan Jenis Data<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning div.panel-collapse div ul li.info-box-text:first-child',
	    		intro: "pilih / klik Jenis Data untuk menampilkan Indikator<br>",
	    		position:'right'
	    	},{
	    		element : 'div#sub_element',
	    		intro: "Daftar Indikator pada jenis Data yang dipilih akan terlihat disini<br>",
	    		position:'left'
	    	},/*{
	    		element : 'div#sub_element_content table tbody tr td:first-child',
	    		intro: "Pilih / klik Indikator atau Subindikator untuk menampilkan chart<br>",
	    		position:'left'
	    	},*/{
	    		element : 'div#chart_option',
	    		intro: "Tampilan data indikator dalam bentuk chart",
	    		position:'top'
	    	},{
	    		element : 'div#chart_option div.box-body div div.btn-group',
	    		intro: "Pilihan tahun untuk chart",
	    		position:'right'
	    	},{
	    		element : 'input#elemen',
	    		intro: "Pencarian Idikator yang ingin ditampilkan chartnya",
	    		position:'left'
	    	},{
	    		element : 'button.btn-refresh-chart',
	    		intro: "Tombol untuk menampilkan ulang chart",
	    		position:'left'
	    	},{
	    		element : 'div#chart_kabupaten',
	    		intro: "Tampilan data indikator perkabupaten dalam bentuk chart, jika data kabupaten ada, maka chart akan ditampilkan",
	    		position:'top'
	    	}
	    ];
	} else if (pagetitle == 'ASPEK, FOKUS DAN INDIKATOR KINERJA') {
	  	intro_steps = [
	    	{
	    		element : 'input#search_elemen',
	    		intro: "Gunakan untuk pencarian Indikator Kinerja<br>",
	    		position:'right'
	    	},{
	    		element : 'div.list_kategori',
	    		intro: "Daftar Aspek & Fokus<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning:first-child',
	    		intro: "pilih / klik Aspek menampilkan Fokus Kinerja<br>",
	    		position:'right'
	    	},{
	    		element : 'div#data_container',
	    		intro: "Data masing-masing Fokus ditampilkan disini<br>",
	    		position:'left'
	    	}
	    ];
	} else if (pagetitle == '5 WILAYAH ADAT') {
	  	intro_steps = [
	    	{
	    		element : 'div.list_kategori',
	    		intro: "Daftar Wilayah dan Kabupaten<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning:first-child',
	    		intro: "pilih / klik Wilayah untuk menampilkan Kabupaten yang termasuk dalam wilayah tersebut<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning:first-child div button',
	    		intro: "Klik untuk menampilkan data dalam wilayah tersebut<br>",
	    		position:'right'
	    	},{
	    		element : 'div#data_container',
	    		intro: "Data masing-masing Wilayah/Kabupaten ditampilkan disini<br>",
	    		position:'left'
	    	}
	    ];
	} else if (pagetitle == 'PROFIL (URUSAN PEMERINTAHAN)') {
	  	intro_steps = [
	    	{
	    		element : 'input#search_elemen',
	    		intro: "Gunakan untuk pencarian Indikator<br>",
	    		position:'right'
	    	},{
	    		element : 'div.list_kategori',
	    		intro: "Daftar Urusan dan Kelompok Indikator<br>",
	    		position:'right'
	    	},{
	    		element : 'div.panel.box.box-warning:first-child',
	    		intro: "pilih / klik Urusan Indikator untuk menampilkan Kelompok Indikator<br>",
	    		position:'right'
	    	},{
	    		element : 'div#data_container',
	    		intro: "Data masing-masing Kelompok ditampilkan disini<br>",
	    		position:'left'
	    	}
	    ];
	} else if (pagetitle == 'Pencarian Data') {
	  	intro_steps = [
	    	{
	    		element : 'form#frm_search',
	    		intro: "Gunakan form berikut ini untuk pencarian Indikator,<br>untuk mempersempit pencarian pilih Kelompok dan Sub Kelompok",
	    	},{
	    		element : 'form#frm_search',
	    		intro: "Untuk memulai pencarian ketik kata kunci lalu tekan <kbd>Enter</kbd> atau klik tombol <b>cari</b>",
	    	},{
	    		element : 'div#search_result',
	    		intro: "Hasil Pencarian ditampilkan disini<br>",
	    		position:'top'
	    	},{
	    		element : '.no-object',
	    		intro: "Jika data dari hasil pencarian tidak sesuai dengan yang dinginkan, silakah menghubungi Pusdalisbang Papua<br>",
	    	}
	    ];
	} else if (pagetitle == 'KONTAK') {
	  	intro_steps = [
	    	{
	    		element : 'div#kontak_map',
	    		intro: "Berikut peta lokasi dari kantor Pusdalisbang ditandai dengan Poin Merah <span class=\"text-red\"><i class=\"red fa fa-map-marker\"></i></span>",
	    		position:'right'
	    	},{
	    		element : 'div#kontak_content',
	    		intro: "berikut ini adalah kontak yang bisa dihubungi dari Pusdalisbang",
	    		position:'left'
	    	},{
	    		element : 'div#div_kontak',
	    		intro: "<b>Form Pesan&Kesan</b><br>Gunakan form berikut untuk memberikan umpan balik kepada Pusdalisbang terutama mengenai Website Pusdalisbang Provinsi papua ini<br>",
	    		position:'left'
	    	},{
	    		element : 'div#div_kontak',
	    		intro: "untuk mengirimkan <b>Pesan&Kesan</b>, isi nama, email dan pesan anda, lalu tekan tombol kirim<br>",
	    		position:'left'
	    	}
	    ];
	} else {
	  	intro_steps = [
	    	{
	    		element : '.no-element',
	    		intro: "Maaf, tips untuk halaman ini belum tersedia, terima kasih"
	    	}
	    ];
	}

	// disabling reload on ajax
	var active_link = 0;
	if (using_intro) {
		intro.setOptions({
		    steps: intro_steps
		});
		intro.onchange(function(targetElement) {
			// event khusus, semisal pada 8 klp data
			if (pagetitle == '8 KELOMPOK DATA') {
				// console.log(intro._currentStep);
			    switch (intro._currentStep)
		        {
			        case 2:
			        	$('div.panel.box.box-warning:first-child div h3 a:first-child').click();
			        break;
			        case 3:
			        	obj = $('div.panel.box.box-warning:first-child').find('li.info-box-text:first-child a');
						// console.log(obj.attr('href'));
						if (active_link == 0) {
							window.location=obj.attr('href');
							active_link = 1;
						}
			        	// obj.click();
			        break;
			        default :

			        break;
		        }
			}
		}).start();
	}
}

function startIntro_admin(adminmode){
	var intro = introJs();
	var using_intro = true;
	if (adminmode == 'dashboard_') {
	  	intro_steps = [
	    	{
	    		element : 'div#entry_progress',
	    		intro: "Progress Entry SKPD",
	    		position:'top'
	    	}
	    ];
	} else if (adminmode == 'user_') {
	  	intro_steps = [
	    	{
	    		element : 'div.dataTables_scroll',
	    		intro: "daftar pengguna  yang memperoleh akses administrasi pusdalisbang",
	    		position:'top'
	    	},{
	    		element : 'div#tb_user_filter label',
	    		intro: "gunakan untuk memfilter data pengguna",
	    	},{
	    		element : 'a.btn.btn-flat.btn-success',
	    		intro: "Tombol untuk menambahkan pengguna baru",
	    		position:'right'
	    	},{
	    		element : 'table#tb_user tr td:last-child',
	    		intro: "Tombol Edit Pengguna dan Tombol Hapus Pengguna",
	    		position:'left'
	    	}
	    ];
	} else if (adminmode == 'output_') {
	  	intro_steps = [
	    	{
	    		element : 'table#tbl_griddata',
	    		intro: "daftar dokumen output yang dikeluarkan oleh pusdalisbang",
	    		position:'top'
	    	},{
	    		element : 'a.fa.fa-plus.btn.btn-success',
	    		intro: "Tombol untuk menambahkan Dokumen Output baru",
	    		position:'right'
	    	},{
	    		element : 'table#tbl_griddata tr td:last-child',
	    		intro: "Tombol Edit Output dan Tombol Hapus Output",
	    		position:'left'
	    	}
	    ];
	} else if (adminmode == 'output_add') {
	  	intro_steps = [
	    	{
	    		element : 'div#div_frm_output',
	    		intro: "form penambahan dokumen output",
	    		position:'top'
	    	},{
	    		element : 'form.form-horizontal',
	    		intro: "Ketik Judul, Keterangan Dokumen, Pilih File Untuk ditampilkan dan juga status publikasi",
	    		position:'top'
	    	},{
	    		element : 'button[type=submit].btn-info',
	    		intro: "klik ini untuk menyimpan output dokumen dan kembali ke daftar output",
	    		position:'top'
	    	}
	    ];
	} else if (adminmode == 'output_edit') {
	  	intro_steps = [
	    	{
	    		element : 'div#div_frm_output',
	    		intro: "form edit dokumen output",
	    		position:'top'
	    	},{
	    		element : 'form.form-horizontal',
	    		intro: "Ganti Judul, Keterangan Dokumen, Pilih File pengganti Untuk ditampilkan dan juga ubah status publikasi jika diperlukan",
	    		position:'top'
	    	},{
	    		element : 'button[type=submit].btn-info',
	    		intro: "klik ini untuk menyimpan output dokumen dan kembali ke daftar output",
	    		position:'top'
	    	}
	    ];
	} else {
	  	intro_steps = [
	    	{
	    		element : '.no-element',
	    		intro: "Maaf, tips untuk halaman ini belum tersedia, terima kasih"
	    	}
	    ];
	}

	// disabling reload on ajax
	var active_link = 0;
	if (using_intro) {
		intro.setOptions({
		    steps: intro_steps
		});
		intro.onchange(function(targetElement) {
			// event khusus, semisal pada 8 klp data
			if (adminmode == '8 KELOMPOK DATA') {
				console.log(intro._currentStep);
			    switch (intro._currentStep)
		        {
			        case 2:
			        	$('div.panel.box.box-warning:first-child div h3 a:first-child').click();
			        break;
			        case 3:
			        	obj = $('div.panel.box.box-warning:first-child').find('li.info-box-text:first-child a');
						// console.log(obj.attr('href'));
						if (active_link == 0) {
							window.location=obj.attr('href');
							active_link = 1;
						}
			        	// obj.click();
			        break;
			        default :

			        break;
		        }
			}
		}).start();
	}
}

function startIntro_detail(pagetitle){
	// digunakan dalam modal detail
	var detail_intro = introJs();
	var using_detail_intro = true;
	if (pagetitle == 'BADAN' || pagetitle == 'DINAS' || pagetitle == 'BIRO'
		|| pagetitle == 'KANTOR' || pagetitle == 'RUMAH SAKIT' || pagetitle == 'UNIT LAYANAN'
		|| pagetitle == 'SEKRETARIAT' || pagetitle == 'INSTANSI') {
	  detail_intro.setOptions({
	    steps: [
	    	{
	    		element : 'div#list_element',
	    		intro: "<b>Daftar Indikator "+pagetitle+"</b><br>",
	    		position: 'left'
	    	},{
	    		element : 'div#sub_element',
	    		intro: "<b>Chart Indikator "+pagetitle+"</b><br>",
	    		position: 'right'
	    	}
	    ]
	  });
	} else {
		var using_detail_intro = false;
	}

	if (using_detail_intro)
		detail_intro.start();
}

function startIntro_map(pagetitle){
	// digunakan dalam content map
	var map_intro = introJs();
	var using_map_intro = true;
	if (pagetitle == 'GERBANGMAS') {
	  	intro_steps = [
	    	{
	    		element : 'div#judul_peta',
	    		intro: "Indikator, tahun dan Wilayah yang dimuat dalam <b>peta</b><br>"
	    	},{
	    		element : 'div#judul_peta div a.btn.btn-info.dropdown-toggle',
	    		intro: "Pilih indikator untuk memuat indikator yang diinginkan<br>"
	    	},{
	    		element : 'select#tahun',
	    		intro: "pilih tahun"
	    	},{
	    		element : 'select#jenis_analisa',
	    		intro: "pilih wilayah"
	    	},{
	    		element : 'div.map-legend',
	    		intro: "legenda peta<br>",
	    		position:'right'
	    	},{
	    		element : 'div#options',
	    		intro: "<b>Opsi peta</b><br>print, chart, data table, reset peta",
	    		position: 'left'
	    	},{
	    		element : 'button.chart-button',
	    		intro: "klik untuk menampilkan chart",
	    		position: 'left'
	    	},{
	    		element : 'div#chart',
	    		intro: "tampilan data dalam bentuk chart",
	    		position: 'top'
	    	},{
	    		element : 'button.table-button',
	    		intro: "klik untuk menampilkan tabel data",
	    		position: 'left'
	    	},{
	    		element : 'div#table_data',
	    		intro: "tampilan data dalam bentuk tabel",
	    		position: 'top'
	    	},{
	    		element : 'div.ol-full-screen.ol-unselectable.ol-control',
	    		intro: "klik untuk tampilan peta fullscreen",
	    	}
	    ];
	} else if (pagetitle == 'PETA INTERAKTIF WILAYAHADAT' || pagetitle == 'PETA INTERAKTIF PROVINSI'
		|| pagetitle == 'PETA INTERAKTIF KABUPATEN' || pagetitle == 'PETA INTERAKTIF TEMATIK') {

	    if (pagetitle == 'PETA INTERAKTIF KABUPATEN') {
			intro_steps = [
		    	{
		    		element : 'div#judul_peta',
		    		intro: "Jenis Analisa dan Tahun yang dimuat dalam <b>peta</b><br>"
		    	},{
		    		element : 'select#jenis_analisa',
		    		intro: "Pilih jenis analisa unttuk ditampilkan<br>"
		    	},{
		    		element : 'select#tahun',
		    		intro: "pilih tahun"
		    	},{
		    		element : 'div#map_title',
		    		intro: "judul dan sumber data peta",
		    	}
		    ];
	    } else if (pagetitle == 'PETA INTERAKTIF PROVINSI') {
			intro_steps = [
		    	{
		    		element : 'div#judul_peta',
		    		intro: "GIS dari Simtaru yang dimuat dalam <b>peta</b><br>"
		    	},{
		    		element : 'select#jenis_analisa',
		    		intro: "Pilih file peta yang akan ditampilkan<br>"
		    	}
		    ];
	    } else {
		    intro_steps = [
		    	{
		    		element : 'div#judul_peta',
		    		intro: "Indikator, tahun dan Wilayah yang dimuat dalam <b>peta</b><br>"
		    	},{
		    		element : 'div#judul_peta div a.btn.btn-info.dropdown-toggle',
		    		intro: "Pilih indikator untuk memuat indikator yang diinginkan<br>"
		    	},{
		    		element : 'select#jenis_analisa',
		    		intro: "pilih wilayah"
		    	},{
		    		element : 'select#tahun',
		    		intro: "pilih tahun"
		    	},{
		    		element : 'div#map_title',
		    		intro: "judul dan sumber data peta",
		    	}
		    ];
	    }

	    intro_steps.push({
	    		element : 'div.map-legend',
	    		intro: "legenda peta<br>",
	    		position:'right'
	    	},{
	    		element : 'div#options',
	    		intro: "<b>Opsi peta</b><br>print, chart, data table, reset peta",
	    		position: 'left'
	    	},{
	    		element : 'button.chart-button',
	    		intro: "klik untuk menampilkan chart",
	    		position: 'left'
	    	},{
	    		element : 'div#chart',
	    		intro: "tampilan data dalam bentuk chart",
	    		position: 'top'
	    	},{
	    		element : 'button.table-button',
	    		intro: "klik untuk menampilkan tabel data",
	    		position: 'left'
	    	},{
	    		element : 'div#table_data',
	    		intro: "tampilan data dalam bentuk tabel",
	    		position: 'top'
	    	},{
	    		element : 'div.ol-full-screen.ol-unselectable.ol-control',
	    		intro: "klik untuk tampilan peta fullscreen",
	    	});

	} else {
		var using_map_intro = false;
	}

	if (using_map_intro) {
		map_intro.setOptions({
		    	steps: intro_steps
		  	}).onchange(function(targetElement) {
			// console.log(targetElement.id);
		    switch (targetElement.id)
	        {
		        case "chart":
		        	$('#chart').show();
		        break;
		        case "table_data":
		        	$('#table_data').show();
		        break;
		        default :
		        	$('#chart').hide();
		        	$('#table_data').hide();
		        break;
	        }
		}).start();
	}
}

function startIntro(){
	var intro = introJs();
  	intro.start();
}
