<?php
$testimonials = [
    [
        'quote'   => 'Such an easy system to use, and we save about $35K a year from using it.',
        'name'    => 'Wayne England',
        'handle'  => '@ivfmichigan',
        'initials'=> 'WE',
        'avatar'  => '',
    ],
    [
        'quote'   => 'RT team is highly knowledgeable about the cryo market and cryogenic applications in general.',
        'name'    => 'Nawfel Oussedik',
        'handle'  => '@cryochambers',
        'initials'=> 'NO',
        'avatar'  => content_url('/uploads/nawfel-oussedik-gtm.jpeg'),
    ],
    [
        'quote'   => 'Robust, works great, and portable. Got the job done when nothing else would have worked at that location.',
        'name'    => 'Pavel Romashkin',
        'handle'  => '@ucar',
        'initials'=> 'PR',
        'avatar'  => '',
    ],
];
?>
<div class="rt-testimonials">
    <div class="rt-container">

        <div class="rt-testimonials__header">
            <span class="rt-preheading">Testimonials</span>
            <h2>What Our Clients Say</h2>
        </div>

        <div class="rt-carousel" id="rt-testimonials-carousel" data-autoplay="false" data-visible="3">
            <div class="rt-carousel__track">
                <?php foreach ( $testimonials as $t ) : ?>
                <div class="rt-carousel__slide rt-testimonial">
                    <div class="rt-testimonial__content">
                        <div class="rt-testimonial__quote-mark">&ldquo;</div>
                        <p class="rt-testimonial__quote"><?php echo esc_html( $t['quote'] ); ?></p>
                    </div>
                    <div class="rt-testimonial__author">
                        <div class="rt-testimonial__author-left">
                            <div class="rt-testimonial__avatar">
                                <?php if ( ! empty( $t['avatar'] ) ) : ?>
                                    <img src="<?php echo esc_url( $t['avatar'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>">
                                <?php else : ?>
                                    <span><?php echo esc_html( $t['initials'] ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="rt-testimonial__author-right">
                            <strong><?php echo esc_html( $t['name'] ); ?></strong>
                            <span><?php echo esc_html( $t['handle'] ); ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="rt-carousel__dots" id="rt-testimonials-dots"></div>
        </div>

    </div>
</div>
