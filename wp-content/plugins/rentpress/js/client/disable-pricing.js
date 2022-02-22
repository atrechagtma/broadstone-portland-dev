var rp_disable_pricing_words=new RegExp('(Starting.At)|(From)|((\$)'+ rp_replacementMessage +')', 'g');
var rp_vaild_price_repexp=/(\$)((\d*\,\d*)|(\d{3}))/gm;

function rp_disable_pricing() {
	if (typeof angular == 'undefined') {
		var ElementWithClass=document.querySelectorAll('*.rp-dp-here');

		ElementWithClass.forEach(function(ele) {

			var innerTEXT = ele.innerText.replace(/(\r\n\t|\n|\r\t)/gm,"");
			
			if (! ele.innerHTML.match('{{') && ! innerTEXT.match(rp_vaild_price_repexp)) {
	            ele.innerHTML=rp_replacementMessage;
	       	}

		});

		if (Number(rp_must_disable_pricing) === 1) {
			ElementWithClass.forEach(function(ele) {
				ele.innerHTML=rp_replacementMessage;
			});

			/*	
				var LinkElementsWithClass=document.querySelectorAll("a.rp-disable-apply");

				LinkElementsWithClass.forEach(function(ele) {
					ele.setAttribute('href', rp_replacementURL);
				});
			*/
		}
	}

}

document.addEventListener("DOMContentLoaded", function(event) { 
	rp_disable_pricing();
});
