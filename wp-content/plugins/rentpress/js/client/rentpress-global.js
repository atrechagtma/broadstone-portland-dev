(function($) {
    $(document).ready(function() {

        // * page scrolling http://css-tricks.com/snippets/jquery/smooth-scrolling/
        $(function() {
            $('.rp-radio-unit-number').on('change', function() {
            $('.rp-unit-card').removeClass('rp-active');
            
            $(this).parent().parent().addClass('rp-active');
        });
            $('.rentpress-core-container a[href*="#"]:not([href="#"])').click(function() {
                if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
                    var target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
                    if (target.length) {
                        $('html, body').animate({
                            scrollTop: target.offset().top
                        }, 400);
                        return false;
                    }
                }
            });
        });
        // * Page scrolling
    });

})(jQuery);