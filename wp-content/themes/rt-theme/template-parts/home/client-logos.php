<?php
$logos = [
    ['src' => content_url('/uploads/ln2-generator-client-logo-air-force.jpeg'),          'alt' => 'US Air Force'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-fbi.jpeg'),                'alt' => 'FBI'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-lockhead-martins.jpeg'),   'alt' => 'Lockheed Martin'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-morgan-dermatology.jpeg'), 'alt' => 'Morgan Dermatology'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-nasa.jpeg'),               'alt' => 'NASA'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-north-arizona-university.jpeg'), 'alt' => 'Northern Arizona University'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-michigan-university.jpeg'), 'alt' => 'University of Michigan'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-toshiba.jpeg'),            'alt' => 'Toshiba'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-pharmative.jpeg'),         'alt' => 'Pharmative'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-socom.jpeg'),              'alt' => 'SOCOM'],
    ['src' => content_url('/uploads/ln2-generator-client-logo-university-of-hawai.jpeg'),'alt' => 'University of Hawaiʻi'],
];
?>
<section class="rt-section rt-section--white rt-clients">
    <div class="rt-container">
        <div class="rt-clients__header">
            <h2 class="rt-clients__title">They Trust Us</h2>
            <p class="rt-clients__subtitle">They trust us with the liquid nitrogen supply</p>
        </div>
    </div>

    <div class="rt-logos-marquee">
        <div class="rt-logos-track">
            <?php foreach ( $logos as $logo ) : ?>
            <div class="rt-logo-slide">
                <img src="<?php echo esc_url( $logo['src'] ); ?>"
                     alt="<?php echo esc_attr( $logo['alt'] ); ?>"
                     loading="lazy">
            </div>
            <?php endforeach; ?>
            <?php foreach ( $logos as $logo ) : ?>
            <div class="rt-logo-slide" aria-hidden="true">
                <img src="<?php echo esc_url( $logo['src'] ); ?>"
                     alt=""
                     loading="lazy">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
