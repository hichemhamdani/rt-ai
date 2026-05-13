<?php
add_action( 'wp_enqueue_scripts', function() {
    $v = wp_get_theme()->get( 'Version' );
    $uri = get_template_directory_uri();

    // Google Fonts
    wp_enqueue_style(
        'rt-fonts',
        'https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;500;600&family=Reddit+Sans:ital,wght@0,300;0,400;0,500;1,300&family=Fira+Sans+Condensed:wght@400;500;600&display=swap',
        [],
        null
    );

    // CSS
    wp_enqueue_style( 'rt-main',       $uri . '/assets/css/main.css',       ['rt-fonts'], $v );
    wp_enqueue_style( 'rt-header',     $uri . '/assets/css/header.css',     ['rt-main'],  $v );
    wp_enqueue_style( 'rt-footer',     $uri . '/assets/css/footer.css',     ['rt-main'],  $v );
    wp_enqueue_style( 'rt-home',       $uri . '/assets/css/home.css',       ['rt-main'],  $v );
    wp_enqueue_style( 'rt-responsive', $uri . '/assets/css/responsive.css', ['rt-main'],  $v );

    // GSAP
    wp_enqueue_script( 'gsap',              'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js',              [], null, true );

    // JS
    wp_enqueue_script( 'rt-header',   $uri . '/assets/js/header.js',   [], $v, true );
    wp_enqueue_script( 'rt-megamenu', $uri . '/assets/js/megamenu.js', [], $v, true );
    wp_enqueue_script( 'rt-carousel', $uri . '/assets/js/carousel.js', [], $v, true );
    wp_enqueue_script( 'rt-faq',        $uri . '/assets/js/faq.js',        [], $v, true );
    wp_enqueue_script( 'rt-animations',    $uri . '/assets/js/animations.js',    [], $v, true );
    wp_enqueue_script( 'rt-craft-slider',  $uri . '/assets/js/craft-slider.js',  ['gsap'], $v, true );

    // Single product (LN2 new design)
    if ( is_product() ) {
        wp_enqueue_style(  'rt-single-product', $uri . '/assets/css/single-product.css', ['rt-main'], $v );
        wp_enqueue_script( 'rt-single-product', $uri . '/assets/js/single-product.js',  [], $v, true );
    }

    // Pass ajaxurl for potential future use
    wp_localize_script( 'rt-header', 'rtTheme', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'homeUrl' => home_url('/'),
    ]);
});
