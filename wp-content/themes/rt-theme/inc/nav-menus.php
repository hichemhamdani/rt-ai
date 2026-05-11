<?php
add_action( 'after_setup_theme', function() {
    register_nav_menus([
        'primary'      => __( 'Menu principal', 'rt-theme' ),
        'top-bar'      => __( 'Top bar', 'rt-theme' ),
        'footer-ln2'   => __( 'Footer — LN2 Generators', 'rt-theme' ),
        'footer-links' => __( 'Footer — Useful Links', 'rt-theme' ),
        'footer-legal' => __( 'Footer — Legal', 'rt-theme' ),
    ]);
});
