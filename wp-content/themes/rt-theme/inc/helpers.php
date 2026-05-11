<?php
function rt_get_products( $count = 8, $category = '' ) {
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ];
    if ( $category ) {
        $args['tax_query'] = [[
            'taxonomy' => 'product_cat',
            'field'    => 'slug',
            'terms'    => $category,
        ]];
    }
    return new WP_Query( $args );
}

function rt_get_product_image_url( $post_id, $size = 'medium' ) {
    $thumb_id = get_post_thumbnail_id( $post_id );
    if ( ! $thumb_id ) return '';
    $img = wp_get_attachment_image_src( $thumb_id, $size );
    return $img ? $img[0] : '';
}

function rt_star_rating( $count = 5 ) {
    $out = '<span class="rt-stars">';
    for ( $i = 0; $i < $count; $i++ ) {
        $out .= '<svg width="16" height="16" viewBox="0 0 16 16" fill="#FFCE00" xmlns="http://www.w3.org/2000/svg"><path d="M8 1l1.85 3.75L14 5.5l-3 2.92.71 4.13L8 10.4l-3.71 2.15.71-4.13L2 5.5l4.15-.75z"/></svg>';
    }
    $out .= '</span>';
    return $out;
}
