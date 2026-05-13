<?php
defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
    the_post();
    global $product;
    if ( ! $product ) $product = wc_get_product( get_the_ID() );

    $terms = wp_get_post_terms( get_the_ID(), 'product_tag', [ 'fields' => 'slugs' ] );

    if ( in_array( 'new-design', $terms, true ) ) {
        get_template_part( 'template-parts/product/single-ln2-new' );
    } else {
        woocommerce_get_template( 'content-single-product.php' );
    }
endwhile;

get_footer();
