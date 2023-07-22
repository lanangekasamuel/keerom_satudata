function loadAnimationTo(obid){
    $('#'+obid).prepend('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
}

function removeAnimationFrom(obid){
    $('#'+obid).find('div.overlay').remove();
}

function disableElement(obid){
	// alert(obid);
	$('#'+obid).attr('disabled','true');
}

function enableElement(obid){
	// alert(obid);
	$('#'+obid).removeAttr('disabled');
}

function setAnimation(){
	return '<div class="text-center"><h2><i class="fa fa-refresh fa-spin"></i></h2></div>';
}

function codel(urldel) {
	if(confirm('Anda yakin akan menghapus data ini?')){									
		location.href = urldel;
		return true;
	}else{
		return false;
	}
}

function add_aai_notif(msg){
	console.log(msg);
}

//Just Number Java Check Input (keypress)
function JustNumbers(e)
{
	var keynum;
	var keychar;
	if(e.which) // Netscape/Firefox/Opera
	{
		keynum = e.which;
	}
	// alert(keynum);
	keychar = String.fromCharCode(keynum);
	numcheck = /(\d|\.)/; //digit dan dot (pengganti koma)
	return numcheck.test(keychar);
}

//check error function
function errorCheck(d){
/*
 * CHECK the error based on printed tag (default=<!--someerrorwasfoundhere-->) in some page (ajax) result
 */
	var ca = d.search(ERROR_TAG);
	//alert(ca);
	if (ca >= 0) {return true;} // check if result contain teh text up
	else {return false;}
}

$(document).ready(function(){
	$('div.fix-balancer').height($('.navbar-fixed-top').outerHeight());
});

