$(function() {

	if (ADMINPANEL) {
		// [20180930222031][anovedit][workaround]
		var sidebar = $('.wrapper:first', document.body).find('.main-sidebar:first').find('.sidebar-menu:first')[0];
		var first,current;
		$(sidebar).find('a').each(function() {
			if (!first) first = this;
			if (current) return false;
			if (window.location.href === this.href) current = this;
		});
		$(current || first).parentsUntil('.sidebar-menu','li').addClass('active');
	} // ADMINPANEL

});
