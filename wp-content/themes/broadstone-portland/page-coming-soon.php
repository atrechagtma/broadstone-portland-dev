<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>
	<?php $location = get_field('location', 'options'); ?>
    <body <?php body_class(); ?>>
        <div id="page" class="site">
            <div class="full-height">
                <div class="row gx-0">
                    <div class="col the-content">
                        <div>
                            <img src="<?php the_field('coming_soon_logo', 'options'); ?>" alt="Broadstone Portland">
                            <h4><?php the_field('coming_soon_title', 'options'); ?></h4>
                            <h3>Coming soon</h3>
                        </div>
                    </div>
                    <div class="col">
                        <div class="has-bg" style="background-image: url('<?php the_field('coming_soon_background', 'options'); ?>');">
                            <?php echo do_shortcode(get_field('coming_soon_shortcode', 'options')); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center location-address">
                <?php echo $location['address']; ?>
            </div>
            <section class="block properties-block">
                <div class="acf-map">
                    <div class="marker" data-lat="<?php echo esc_attr($location['lat']); ?>" data-lng="<?php echo esc_attr($location['lng']); ?>" data-address="<?php echo esc_attr($location['address']); ?>">
                        <h3><?php the_title(); ?></h3>
                        <p><em><?php echo esc_html($location['address']); ?></em></p>
                    </div>
                </div>
            </section>
        </div>
        <?php wp_footer(); ?>
        <style>
            .full-height .col {
                min-height: 100vh;
            }
            .the-content {
                color: #A95B3B;
                text-align: center;
                padding: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .the-content img {
                width: 58%;
                margin-bottom: 40px;
            }
            .the-content h4 {
                font-size: 50px;
            }
            .the-content h3 {
                font-size: 70px;
                position: relative;
                display: inline-block;
            }

            .the-content h3::before,
            .the-content h3::after {
                content: '';
                width: 10%;
                height: 2px;
                background-color: #B39F4B;
                display: block;
                position: absolute;
                top: 50%;
            }
            .the-content h3::before {
                left: -15%;
            }
            .the-content h3::after {
                right: -15%;
            }
            .has-bg {
                min-height: 100vh;
                display: flex;
                align-items: flex-end;
            }
            form {
                background-color: #0D180872;
                padding: 40px 80px;
                margin-bottom: 80px;
                width: 100%;
                color: white;
            }
            form input[type=text],
            form input[type=email] {
                width: 100%;
                background-color: transparent;
                border-style: solid;
                border-width: 2px;
                border-color: white;
                border-radius: 0;
                color: white !important;
                padding-left: 10px;
                padding-right: 10px;
                font-family: "Montserrat", sans-serif;
            }
            ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
                color: white;
                opacity: 1; /* Firefox */
            }

            :-ms-input-placeholder { /* Internet Explorer 10-11 */
                color: white;
            }

            ::-ms-input-placeholder { /* Microsoft Edge */
                color: white;
            }
            form input[type=text]:focus-visible,
            form input[type=email]:focus-visible {
                border-style: solid;
                border-width: 2px;
                border-color: white;
                border-radius: 0;
                outline: none;
            }
            form input[type=submit] {
                background-color: #B79F39;
                border-radius: 0;
                color: white;
                text-transform: uppercase;
                border: 0;
                padding: 15px 25px;
                font-family: "Montserrat", sans-serif;
                float: right;
            } 
            .properties-block {
                text-align: center;
                margin-top: 0;
            }
            .acf-map {
                width: 100%;
                height: 40vw;
                border: #ccc solid 1px;
                margin: 0;
                min-height: 400px;
            }

            .acf-map img {
                max-width: inherit !important;
            }
            .marker * {
                opacity: 0;
            }
            .location-address {
                background-color: #A95B3B;
                color: white;
                padding: 10px;
                text-transform: uppercase;
            }

            @media (max-width: 768px) {
                .full-height .col {
                    min-height: 0px;
                }
                .the-content h3 {
                    font-size: 42px;
                }
                .the-content h4 {
                    font-size: 36px;
                }
            }
        </style>
        <script defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDAySXaZBcJ55fy_bVzQtdV_h42jPj3iN8&callback"></script>
        <script>
            window.onload = () => {
                let footerMap = document.querySelector('.acf-map');
                let location = document.querySelector('.marker');

                if (footerMap) {
                    window.map = new google.maps.Map(footerMap, {
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        center: { lat: parseFloat(location?.dataset?.lat), lng: parseFloat(location?.dataset?.lng)},
                        zoom: 14,
                    });

                    var marker = new google.maps.Marker({
                        position: new google.maps.LatLng(parseFloat(location.dataset.lat), parseFloat(location.dataset.lng)),
                        map: map
                    });
                    var listener = google.maps.event.addListener(map, "idle", function () {
                        google.maps.event.removeListener(listener);
                    });

                }
            }
        </script>
    </body>
</html>