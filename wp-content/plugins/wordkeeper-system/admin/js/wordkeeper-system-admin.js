(function($) {
	'use strict';
	$(function() {
		$('.toggle-value').click(function() {
			var mainParent = $(this).parent('.toggle-btn');
			if($(mainParent).find('input.toggle-value').is(':checked')) {
				$(mainParent).addClass('active');
				$(this).prop('checked', 'checked');
			}
			else {
				$(mainParent).removeClass('active');
			}
		});

		$(document).on('click', '.nav-tab-wrapper a', function() {
			$('.wordkeeper-nav-tab a').removeClass('nav-tab-active');
			$('section').hide();
			
			$(this).addClass('nav-tab-active');
			$('section').eq($(this).index()).show();
			return false;
		});
		
		$('.vrazer-tooltip').each(function(index, $jqObj) {
			var tooltip = $(this).data('tooltip');
			var btnfix = $(this).attr('type');
			if (tooltip) {
				var btnfixClass = '';
				if (btnfix != 'undefined' && btnfix == 'button') {
					btnfixClass = 'btn-fix';
				}
				var html = '<a href="#" class="tooltip ' + btnfixClass + '">?<span><b></b>' + tooltip + '</span></a>';
				var present = $(this).next().length;
				if (present) {
					$(this).next().before(html);
				}
				else {
					$(this).after(html);
				}
			}
		});
	});	
})(jQuery);