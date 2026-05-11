<?php
$testimonials = [
    [
        'quote'    => 'Such an easy system to use, and we save about $35K a year from using it.',
        'name'     => 'Wayne England',
        'company'  => 'IVF Michigan',
        'rating'   => 5,
    ],
    [
        'quote'    => 'RT team is highly knowledgeable about the cryo market. They guided us through every step of our LN2 supply transition.',
        'name'     => 'Nawfel Oussedik',
        'company'  => 'CryoChambers.com',
        'rating'   => 5,
    ],
    [
        'quote'    => 'Robust, works great, and portable. We use it across multiple research sites without any issues.',
        'name'     => 'Pavel Romashkin',
        'company'  => 'University of Atmospheric Research',
        'rating'   => 5,
    ],
];
?>
<div class="rt-testimonials">
    <div class="rt-carousel" id="rt-testimonials-carousel" data-autoplay="false">
        <div class="rt-carousel__track">
            <?php foreach ( $testimonials as $t ) : ?>
            <div class="rt-carousel__slide rt-testimonial">
                <div class="rt-testimonial__stars">
                    <?php for ( $i = 0; $i < $t['rating']; $i++ ) : ?>
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="#FFCE00" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M9 1.5L11.09 6.26L16.5 7.09L12.75 10.74L13.68 16.5L9 13.77L4.32 16.5L5.25 10.74L1.5 7.09L6.91 6.26L9 1.5Z"/>
                    </svg>
                    <?php endfor; ?>
                </div>
                <blockquote class="rt-testimonial__quote">
                    &ldquo;<?php echo esc_html( $t['quote'] ); ?>&rdquo;
                </blockquote>
                <cite class="rt-testimonial__author">
                    <strong><?php echo esc_html( $t['name'] ); ?></strong>
                    <span><?php echo esc_html( $t['company'] ); ?></span>
                </cite>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="rt-carousel__dots" id="rt-testimonials-dots"></div>
    </div>
</div>
