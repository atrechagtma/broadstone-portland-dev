let blogPosts = document.querySelectorAll(
	".wp-block-latest-posts__list.wp-block-latest-posts li"
);

blogPosts = Array.from(blogPosts);

blogPosts.forEach((blog) => {
	let children = blog.querySelectorAll(":scope > *");
	children = Array.from(children);
	blog.innerHTML = "";
	blog.appendChild(children[0]);
	console.log(children[0]);
	blog.appendChild(divWrapper(children, "post-content-wrapper"));
});

function divWrapper(elements, className) {
	var d = document.createElement("div");
	d.classList = className;
	elements.forEach((elem, index) => {
		if (index !== 0) {
			if (elem.nodeName === "A") {
				let a = document.createElement("a");
				let title = document.createElement("h4");
				a.href = elem.href;
				a.innerText = "Read More";
				a.classList = "post-read-more";
				title.innerText = elem.innerText;
				d.appendChild(title);
				d.appendChild(a);
			} else {
				d.appendChild(elem);
			}
		}
	});
	return d;
}


function getCookie(name) {
	var cookieArr = document.cookie.split(";");

	for (var i = 0; i < cookieArr.length; i++) {
	  var cookiePair = cookieArr[i].split("=");

	  if (name == cookiePair[0].trim()) {
		return decodeURIComponent(cookiePair[1]);
	  }
	}
	return null;
  }

  const ctaBanner = document.querySelector('.cta-banner');
  const ctaClose = document.querySelector('.cta-banner .close');
  const navbar = document.querySelector('#masthead .navbar');

  if (getCookie('ctaClosed') == 'true') {
	ctaBanner.classList.add('closed');
	navbar.classList.remove('cta-active')
  }

  ctaClose.addEventListener('click', function (e) {
	e.preventDefault;
	ctaBanner.classList.add('closed');
	navbar.classList.remove('cta-active')
	document.cookie = "ctaClosed=true;SameSite=Lax;path=/;max-age=" + 1 * 24 * 60 * 60;
  });


// header fixed
window.onscroll = function () {
	scrollTopNav();
};
let header = document.getElementById("masthead");
let sticky = header.offsetTop;
function scrollTopNav() {
	if (window.pageYOffset > sticky) {
		header.classList.add("sticky");
	} else {
		header.classList.remove("sticky");
	}
}
