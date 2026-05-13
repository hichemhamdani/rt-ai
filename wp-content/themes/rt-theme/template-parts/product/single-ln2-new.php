<?php
defined( 'ABSPATH' ) || exit;

global $product;
$pid      = get_the_ID();
$capacity = get_post_meta( $pid, 'wr_capacity', true );   // e.g. "10"
$storage  = get_post_meta( $pid, 'wr_storage',  true );   // e.g. "55"
$features = get_post_meta( $pid, 'wr_features', true );
$cooling  = get_post_meta( $pid, 'wr_cooling',  true );
$electrical = get_post_meta( $pid, 'wr_electrical', true );

$cap_terms     = wp_get_post_terms( $pid, 'pa_production-capacity' );
$capacity_label = ( ! is_wp_error( $cap_terms ) && ! empty( $cap_terms ) )
    ? 'Titan ' . $cap_terms[0]->name
    : get_the_title();

$gallery_ids = array_filter( $product->get_gallery_image_ids() );
?>

<!-- ══════════════════════════════════════════════
     HERO BLOCK — text left / image centre / specs right
     ══════════════════════════════════════════════ -->
<div class="sp-hero-block">

    <div class="sp-hero-block__diamond" aria-hidden="true"></div>

    <!-- ── Left column: title + desc + CTA ── -->
    <div class="sp-hero-block__left">
        <p class="sp-hero-block__preheading" data-anim="fade-up" style="--anim-delay:0s"><?php echo esc_html( $capacity_label ); ?></p>
        <h1 class="sp-hero-block__heading">
            <?php echo esc_html( $capacity ); ?> Liquid Nitrogen Generator
        </h1>
        <div class="rt-divider sp-hero-block__divider" data-anim="fade-up" style="--anim-delay:0.15s"></div>
        <div class="sp-hero-block__desc" data-anim="fade-up" style="--anim-delay:0.25s"><?php the_excerpt(); ?></div>

        <div class="sp-hero-block__pricing" data-anim="fade-up" style="--anim-delay:0.35s">
            <span class="sp-hero-block__from">From</span>
            <div class="sp-hero-block__price"><?php echo $product->get_price_html(); ?></div>
        </div>

        <a href="<?php echo esc_url( get_permalink( 16 ) ); ?>" class="rt-btn sp-hero-block__cta" data-anim="fade-up" style="--anim-delay:0.45s">Contact us</a>
    </div>

    <!-- ── Centre column: product gallery ── -->
    <div class="sp-hero-block__center" data-anim="fade-up" style="--anim-delay:0.2s">
        <?php if ( ! empty( $gallery_ids ) ) : ?>
        <div class="sp-gallery" id="sp-gallery">
            <div class="sp-gallery__main">
                <?php foreach ( $gallery_ids as $i => $img_id ) :
                    $img_url  = wp_get_attachment_image_url( $img_id, 'large' );
                    $full_url = wp_get_attachment_image_url( $img_id, 'full' );
                ?>
                <a href="<?php echo esc_url( $full_url ); ?>"
                   class="sp-gallery__slide <?php echo $i === 0 ? 'is-active' : ''; ?>"
                   data-index="<?php echo esc_attr( $i ); ?>">
                    <img src="<?php echo esc_url( $img_url ); ?>"
                         alt="<?php echo esc_attr( get_the_title() ); ?>"
                         loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>">
                </a>
                <?php endforeach; ?>
            </div>
            <?php if ( count( $gallery_ids ) > 1 ) : ?>
            <div class="sp-gallery__dots">
                <?php foreach ( $gallery_ids as $i => $img_id ) : ?>
                <button class="sp-gallery__dot <?php echo $i === 0 ? 'is-active' : ''; ?>"
                        data-index="<?php echo esc_attr( $i ); ?>" aria-label="Image <?php echo $i + 1; ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else : ?>
        <?php echo $product->get_image( 'large', [ 'class' => 'sp-hero-block__fallback-img' ] ); ?>
        <?php endif; ?>
    </div>

    <!-- ── Right column: specs + badge + price ── -->
    <div class="sp-hero-block__right">
        <div class="sp-spec-card" data-anim="fade-right" style="--anim-delay:0.1s">
            <div class="sp-spec-card__icon">
                <img src="<?php echo esc_url( content_url( '/uploads/output.svg' ) ); ?>" alt="" width="28" height="28">
            </div>
            <div class="sp-spec-card__text">
                <div class="sp-spec-card__title">Capacity</div>
                <div class="sp-spec-card__desc">Produce up to <?php echo esc_html( $capacity ); ?> liters per day</div>
            </div>
        </div>
        <div class="sp-spec-card" data-anim="fade-right" style="--anim-delay:0.25s">
            <div class="sp-spec-card__icon">
                <img src="<?php echo esc_url( content_url( '/uploads/capacity.svg' ) ); ?>" alt="" width="28" height="28">
            </div>
            <div class="sp-spec-card__text">
                <div class="sp-spec-card__title">Storage</div>
                <div class="sp-spec-card__desc">Large <?php echo esc_html( $storage ); ?> liters tank Included</div>
            </div>
        </div>
        <div class="sp-spec-card" data-anim="fade-right" style="--anim-delay:0.4s">
            <div class="sp-spec-card__icon">
                <img src="<?php echo esc_url( content_url( '/uploads/united-states-of-america-1.svg' ) ); ?>" alt="USA" width="28" height="28">
            </div>
            <div class="sp-spec-card__text">
                <div class="sp-spec-card__title">Made in USA</div>
                <div class="sp-spec-card__desc">Used by NASA, FBI, SOCOM, and Air Force</div>
            </div>
        </div>

    </div>

</div>

<!-- ── Trust / logos ── -->
<section class="sp-trust">
    <h2 class="sp-trust__title">Trusted choice of leading U.S. laboratories</h2>
    <?php get_template_part( 'template-parts/home/client-logos' ); ?>
</section>

<!-- ══════════════════════════════════════════════
     SECTION 3 — ARGUMENTS (2 cards)
     ══════════════════════════════════════════════ -->
<section class="sp-arguments">
    <div class="sp-arguments__header">
        <h2 class="sp-arguments__title">Produce the LN2 You Need at The Best Cost</h2>
        <p class="sp-arguments__subtitle">Traditional nitrogen delivery services charge premium rates and often impose delays, shortages, hidden fees, and delivery costs.</p>
    </div>
    <div class="sp-arguments__cards">

        <div class="sp-arg-card">
            <div class="sp-arg-card__img-wrap">
                <img src="<?php echo esc_url( content_url( '/uploads/freepik__person-proudly-presenting-a-new-liquid-nitrogen-ge__88993.png' ) ); ?>"
                     alt="Less cost" loading="lazy" class="sp-arg-card__img">
            </div>
            <div class="sp-arg-card__body">
                <div class="sp-arg-card__tag">Less cost</div>
                <h3 class="sp-arg-card__heading">Pay 13 Cents Per Liter</h3>
                <div class="rt-divider sp-arg-card__divider"></div>
                <p class="sp-arg-card__text">With lower operational expenses and no delivery fees, you saves thousands annually.</p>
                <a href="<?php echo esc_url( home_url( '/liquid-nitrogen-generators/liquid-nitrogen-price-usa/' ) ); ?>"
                   class="rt-btn rt-btn--outline sp-arg-card__btn">Learn more</a>
            </div>
        </div>

        <div class="sp-arg-card">
            <div class="sp-arg-card__img-wrap">
                <img src="<?php echo esc_url( content_url( '/uploads/freepik__person-using-a-new-liquid-nitrogen-generator-in-a-__88996.png' ) ); ?>"
                     alt="Always available" loading="lazy" class="sp-arg-card__img">
            </div>
            <div class="sp-arg-card__body">
                <div class="sp-arg-card__tag">More freedom</div>
                <h3 class="sp-arg-card__heading">Always Available Supply</h3>
                <div class="rt-divider sp-arg-card__divider"></div>
                <p class="sp-arg-card__text">Our system comes with a 55L storage capacity so you always have a backup supply.</p>
                <a href="<?php echo esc_url( home_url( '/liquid-nitrogen-generators/liquid-nitrogen-price-usa/free-supply-audit/' ) ); ?>"
                   class="rt-btn rt-btn--outline sp-arg-card__btn">Free LN2 supply audit</a>
            </div>
        </div>

    </div>
</section>

<!-- ══════════════════════════════════════════════
     SECTION 4 — AMERICAN ENGINEERING
     ══════════════════════════════════════════════ -->
<section class="sp-engineering">
    <div class="sp-engineering__inner">
        <h2 class="sp-engineering__title">American Engineering</h2>
        <p class="sp-engineering__subtitle">Smart Technology. Continuous Performance. Guaranteed Reliability.</p>

        <div class="sp-engineering__cols">
            <div class="sp-engineering__desc-col">
                <?php the_content(); ?>
            </div>
            <div class="sp-engineering__features-col">
                <div class="sp-feature">
                    <div class="sp-feature__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/></svg></div>
                    <div class="sp-feature__text">
                        <strong>FlowSync&#x2122; Technology</strong>
                        <p>An intelligent control system that continuously monitors internal LN&#x2082; storage levels and automatically adjusts production. LN&#x2082; generation only occurs when storage drops below a set threshold, ensuring optimal efficiency, reduced energy consumption, and extended component lifespan.</p>
                    </div>
                </div>
                <div class="sp-feature">
                    <div class="sp-feature__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg></div>
                    <div class="sp-feature__text">
                        <strong>1-Year Warranty</strong>
                        <p>Comprehensive warranty coverage includes all parts, labor, and technical support for 12 months from the date of installation. Our U.S.-based service team provides direct assistance, remote diagnostics, and rapid replacement of any defective components.</p>
                    </div>
                </div>
                <div class="sp-feature">
                    <div class="sp-feature__icon"><svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4 2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg></div>
                    <div class="sp-feature__text">
                        <strong>SmartConnect&#x2122; Monitoring</strong>
                        <p>Built-in remote access capability allows authorized engineers to monitor and diagnose system performance in real time via Wi-Fi or Ethernet. This enables predictive maintenance, quick troubleshooting, and minimized downtime — all without requiring on-site service calls.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     SECTION 5 — PRODUCT SPECIFICATIONS
     ══════════════════════════════════════════════ -->
<section class="sp-specs">
    <video class="sp-specs__video" autoplay muted loop playsinline>
        <source src="<?php echo esc_url( content_url( '/uploads/blue-background.mp4' ) ); ?>" type="video/mp4">
    </video>
    <div class="sp-specs__overlay"></div>
    <div class="sp-specs__inner">
        <h2 class="sp-specs__title">Product Specifications</h2>
        <p class="sp-specs__subtitle">For technical questions, please <a href="<?php echo esc_url( get_permalink( 16 ) ); ?>">contact our team</a>.</p>

        <?php if ( $features ) : ?>
        <h3 class="sp-specs__group-title">Features</h3>
        <div class="sp-specs__content sp-specs__content--cols">
            <?php echo wp_kses_post( $features ); ?>
        </div>
        <hr class="sp-specs__divider">
        <?php endif; ?>

        <?php if ( $cooling ) : ?>
        <h3 class="sp-specs__group-title">Cooling</h3>
        <div class="sp-specs__content">
            <?php echo wp_kses_post( $cooling ); ?>
        </div>
        <hr class="sp-specs__divider">
        <?php endif; ?>

        <?php if ( $electrical ) : ?>
        <h3 class="sp-specs__group-title">Electrical</h3>
        <div class="sp-specs__content">
            <?php echo wp_kses_post( $electrical ); ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════════
     SECTION 6 — MARKETS
     ══════════════════════════════════════════════ -->
<section class="sp-markets rt-section">
    <div class="rt-container">
        <div class="rt-section__header">
            <span class="rt-preheading">Industries</span>
            <h2>Who Uses Our Generators</h2>
            <div class="rt-divider"></div>
        </div>
        <div class="sp-markets__grid">
            <?php
            $markets = [
                [ 'label' => 'University',        'slug' => 'university' ],
                [ 'label' => 'Research',          'slug' => 'research' ],
                [ 'label' => 'Laboratory',        'slug' => 'laboratory' ],
                [ 'label' => 'Health Industry',   'slug' => 'health' ],
                [ 'label' => 'Government Agency', 'slug' => 'government' ],
                [ 'label' => 'Food & Beverage',   'slug' => 'food' ],
                [ 'label' => 'Fertility',         'slug' => 'fertility' ],
                [ 'label' => 'Electronics',       'slug' => 'electronics' ],
                [ 'label' => 'Dermatology',       'slug' => 'dermatology' ],
                [ 'label' => 'Defense & Aerospace','slug' => 'defense' ],
            ];
            foreach ( $markets as $m ) : ?>
            <div class="sp-market-card">
                <div class="sp-market-card__body">
                    <h4 class="sp-market-card__title"><?php echo esc_html( $m['label'] ); ?></h4>
                    <a href="<?php echo esc_url( get_permalink( 16 ) ); ?>" class="rt-btn rt-btn--sm">Contact us</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
