<?php

/**
 * Gallery Block Template.
 *
 * @param   array $block The block settings and attributes.
 * @param   string $content The block inner HTML (empty).
 * @param   bool $is_preview True during AJAX preview.
 * @param   (int|string) $post_id The post ID this block is saved to.
 */

// Create id attribute allowing for custom "anchor" value.
$id = 'gallery-' . $block['id'];
if (!empty($block['anchor'])) {
    $id = $block['anchor'];
}

// Create class attribute allowing for custom "className" and "align" values.
$className = 'gallery-block';
if (!empty($block['className'])) {
    $className .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $className .= ' align' . $block['align'];
}

// Load values and assign defaults.
$galleries = get_field('gallery');
?>

<section class="block <?php echo esc_attr($className); ?>" id="<?php echo esc_attr($id); ?>">
    <div class="container-fluid">
        <ul class="filter"> 
            <?php foreach ($galleries as $gallery) { ?>
                <li><a class="btn active" href="<?php echo sanitize_title($gallery['slide_title']); ?>"><?php echo $gallery['slide_title']; ?></a></li>
            <?php } ?>
        </ul>

        <div class="filter-content">
            <div class="gallery-wrapper">
                <?php foreach ($galleries as $gallery) : ?>
                    <?php $images = $gallery['images']; ?>
                    <?php foreach ((array) $images as $img) : ?>
                        <img class="<?php echo sanitize_title($gallery['slide_title']); ?>" src="<?php echo $img['url']; ?>" alt="">
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        let allImages = document.querySelectorAll('.gallery-wrapper img');
        allImages = Array.from(allImages)

        let allFilterBtns = document.querySelectorAll('.filter li a.active');
        allFilterBtns = Array.from(allFilterBtns);


        allFilterBtns.forEach(btn => {
            btn.addEventListener('click', updateContent)
        })

        function updateContent(e) {
            e.preventDefault();
            e.target.classList.toggle('active');
            filterImages();
        }

        function filterImages() {
            let filterBtns = document.querySelectorAll('.filter li a.active');
            filterBtns = Array.from(filterBtns);

            allImages.forEach(img => {
                img.classList.remove('active')
            })

            filterBtns.forEach(btn => {
                const activeClass = btn.getAttribute('href')
                let activeImages = document.querySelectorAll(`.${activeClass}`);
                
                activeImages = Array.from(activeImages)
                activeImages.forEach(img => {
                    img.classList.add('active');
                })
            });
        }

        window.onload = filterImages;
        
    </script>

    <style type="text/css">
        #<?php echo $id; ?> {
            margin-top: 0;
            position: relative;
            padding: 140px 0;
        }
        #<?php echo $id; ?> ul.filter {
            list-style: none;
            max-width: 100%;
            padding: 0;
            display: flex;
            justify-content: space-around;
            align-items: center;
        }
        #<?php echo $id; ?> ul.filter li {
            margin: 0 0 20px;
            flex: 1 0 25%;
            max-width: 25%;
        }
        #<?php echo $id; ?> ul.filter li a {
            border-style: solid;
            border-width: 3px;
            border-color: rgba(41, 73, 84, 0.5);
            background-color: transparent;
            border-radius: 12px;
            padding: 13px 1.5vw;
            color: rgba(41, 73, 84, 0.5);
            text-transform: uppercase;
            text-decoration: none;
            font-weight: bold;
            width: 100%;
            display: inline-block;
            text-align: center;
            box-shadow: 0px 3px 6px #00000029;
        }
        #<?php echo $id; ?> ul.filter li a:hover {
            color: rgba(41, 73, 84, 1);
            border-color: rgba(41, 73, 84, 1);
        }
        #<?php echo $id; ?> ul.filter li a.active {
            color: white;
            border-color: rgba(41, 73, 84, 1);
            background-color: rgba(41, 73, 84, 1);
        }
        #<?php echo $id; ?> .slider-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #<?php echo $id; ?> .slider-item .slider-column {
            flex: 1;
            max-width: 50%;
        }
        #<?php echo $id; ?> .slider-header hr {
            border: 0;
            border-bottom: solid 2px #AA5B3C; 
            width: 65%;
            background: transparent;
        }
        #<?php echo $id; ?> .slider-header h3 {
            font-size: min(4vw, 72px);
        }
        #<?php echo $id; ?> .the-content {
            font-size: 16px;
            line-height: 1;
        }
        #<?php echo $id; ?> .the-content a {
            margin-top: 60px;
            border: solid 3px #294954;
            border-radius: 12px;
            padding: 13px 1.5vw;
            color: #294954;
            text-transform: uppercase;
            text-decoration: none;
            font-weight: bold;
            font-size: 1vw;
            display: inline-block;
        }
        @media (max-width: 992px) {
            
            #<?php echo $id; ?> {
                padding: 60px 0;
            }
            #<?php echo $id; ?> ul.filter {
                flex-direction: column;
            }
            #<?php echo $id; ?> ul.filter li {
                width: 100%;
            }
        }
        @media (max-width: 768px) {
            
            #<?php echo $id; ?> ul.filter li {
                width: 100%;
                flex-basis: 100%;
                max-width: 100%;
            }
            #<?php echo $id; ?> .slider-header h3 {
                font-size: 46px;
            }
            #<?php echo $id; ?> .slider-item {
                flex-direction: column;
            }
            #<?php echo $id; ?> .slider-item .slider-column {
                max-width: 100%;
            }
            #<?php echo $id; ?> .the-content a {
                font-size: 20px;
            }
            #<?php echo $id; ?> .the-content {
                margin-bottom: 40px;
            }
        }
        .gallery-wrapper {
            display: flex;
            justify-content: space-evenly;
            align-items: center;
            flex-wrap: wrap;
        }
        .gallery-wrapper img {
            height: 40vh;
            width: auto;
            object-fit: contain;
            max-width: 100%;
            margin: 30px;
            display: none;
        }
        .gallery-wrapper img.active {
            display: block;
        }

        .gallery-wrapper img:nth-child(3n + 3) {
            width: 50%;
            object-fit: cover;
        }
        @media (max-width: 768px) {
            .gallery-wrapper img {
                width: 100%;
                height: auto;
                margin: 0 0 30px;
            }
        }
    </style>
</section>