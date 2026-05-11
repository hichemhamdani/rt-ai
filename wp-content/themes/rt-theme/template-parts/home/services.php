<?php
$services = [
    [
        'title'   => 'Planning',
        'desc'    => 'Every operation is unique. We help you plan a custom or turnkey solution that works for you.',
        'image'   => content_url('/uploads/RT-experts-LN2.jpeg'),
        'alt'     => 'RT experts planning LN2 solution',
        'link'    => home_url('/services/planning/'),
    ],
    [
        'title'   => 'Maintenance',
        'desc'    => 'Our nationwide network of technicians is on standby to provide remote or on-site maintenance, minimizing downtime.',
        'image'   => content_url('/uploads/Maintenance-RT.jpeg'),
        'alt'     => 'RT maintenance engineers',
        'link'    => home_url('/services/maintenance/'),
    ],
    [
        'title'   => 'Installation',
        'desc'    => 'Our team quickly and efficiently installs your system while minimizing operational disruption.',
        'image'   => content_url('/uploads/Installation-RT.jpeg'),
        'alt'     => 'RT installation engineer',
        'link'    => home_url('/services/installation/'),
    ],
    [
        'title'   => 'Upgrades',
        'desc'    => 'As your partner, we help you continually upgrade and reconfigure your system for maximum efficiency.',
        'image'   => content_url('/uploads/Upgrade-system-RT.jpg'),
        'alt'     => 'RT system upgrade',
        'link'    => home_url('/services/upgrades/'),
    ],
];
?>
<section class="rt-section rt-services" id="services">
    <div class="rt-container">
        <div class="rt-services__header rt-section__header" style="text-align:center;">
            <span class="rt-preheading" style="display:block;text-align:center;">Services</span>
            <h2>Experts In All Things Cryogenics</h2>
            <div class="rt-divider" style="margin-inline:auto;"></div>
            <p style="max-width:750px;margin-inline:auto;">
                From planning and installation to ongoing maintenance and upgrades, RT covers every stage of your industrial gas supply lifecycle.
            </p>
        </div>
        <div class="rt-services__grid">
            <?php foreach ( $services as $s ) : ?>
            <article class="rt-service-card <?php echo $s === reset($services) ? 'rt-service-card--featured' : ''; ?>">
                <a href="<?php echo esc_url( $s['link'] ); ?>" class="rt-service-card__link">
                    <div class="rt-service-card__arrow">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M2 12L12 2M12 2H5M12 2v7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                    <img src="<?php echo esc_url( $s['image'] ); ?>"
                         alt="<?php echo esc_attr( $s['alt'] ); ?>"
                         loading="lazy">
                    <div class="rt-service-card__overlay">
                        <h4 class="rt-service-card__title"><?php echo esc_html( $s['title'] ); ?></h4>
                        <p class="rt-service-card__desc"><?php echo esc_html( $s['desc'] ); ?></p>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
