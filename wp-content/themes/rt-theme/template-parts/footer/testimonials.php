<?php
$testimonials = [
    [
        'quote'   => 'Such an easy system to use, and we save about $35K a year from using it.',
        'name'    => 'Wayne England',
        'role'    => 'Operations Manager',
        'company' => 'IVF Michigan',
        'initials'=> 'IVF',
        'logo'    => '',
        'rating'  => 5,
    ],
    [
        'quote'   => 'RT team is highly knowledgeable about the cryo market and cryogenic applications in general.',
        'name'    => 'Nawfel Oussedik',
        'role'    => 'CEO',
        'company' => 'CryoChambers.com',
        'initials'=> 'CC',
        'logo'    => content_url('/uploads/nawfel-oussedik-gtm.jpeg'),
        'rating'  => 5,
    ],
    [
        'quote'   => 'Robust, works great, and portable. Got the job done when nothing else would have worked at that location.',
        'name'    => 'Pavel Romashkin',
        'role'    => 'Staff Directory',
        'company' => 'University of Atmospheric Research',
        'initials'=> 'UCAR',
        'logo'    => '',
        'rating'  => 5,
    ],
];
?>
<div class="rt-testimonials">
    <div class="rt-container">
    <div class="rt-carousel" id="rt-testimonials-carousel" data-autoplay="false" data-visible="3">
        <div class="rt-carousel__track">
            <?php foreach ( $testimonials as $t ) : ?>
            <div class="rt-carousel__slide rt-testimonial">

                <div class="rt-testimonial__header">
                    <div class="rt-testimonial__logo">
                        <?php if ( ! empty( $t['logo'] ) ) : ?>
                            <img src="<?php echo esc_url( $t['logo'] ); ?>"
                                 alt="<?php echo esc_attr( $t['company'] ); ?>">
                        <?php else : ?>
                            <span class="rt-testimonial__initials"><?php echo esc_html( $t['initials'] ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="rt-testimonial__meta">
                        <p class="rt-testimonial__name">
                            <?php echo esc_html( $t['name'] ); ?> &mdash; <?php echo esc_html( $t['role'] ); ?> @ <?php echo esc_html( $t['company'] ); ?>
                        </p>
                        <div class="rt-testimonial__stars">
                            <?php for ( $i = 0; $i < $t['rating']; $i++ ) : ?>
                            <svg width="16" height="16" viewBox="0 0 18 18" fill="#FFCE00" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M9 1.5L11.09 6.26L16.5 7.09L12.75 10.74L13.68 16.5L9 13.77L4.32 16.5L5.25 10.74L1.5 7.09L6.91 6.26L9 1.5Z"/>
                            </svg>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <div class="rt-testimonial__body">
                    <p class="rt-testimonial__quote"><?php echo esc_html( $t['quote'] ); ?></p>
                </div>

            </div>
            <?php endforeach; ?>
        </div>
        <div class="rt-carousel__dots" id="rt-testimonials-dots"></div>
    </div>
    </div>
</div>
