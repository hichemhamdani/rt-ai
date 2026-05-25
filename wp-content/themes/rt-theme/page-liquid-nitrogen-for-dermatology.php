<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>

<main class="ld-page">

    <!-- ══════════════════════════════════════════════
         HERO
         ══════════════════════════════════════════════ -->
    <section class="ld-hero">
        <div class="ld-hero__diamond" aria-hidden="true"></div>

        <div class="ld-hero__content">
            <div class="ld-hero__text">
                <span class="rt-preheading" data-anim="fade-up" style="--anim-delay:0s">Dermatology</span>
                <h1 class="ld-hero__title" data-anim="fade-up" style="--anim-delay:0.1s">
                    Leading Dermatologists<br>Produce Their Own<br>Liquid Nitrogen
                </h1>
                <div class="rt-divider" data-anim="fade-up" style="--anim-delay:0.2s"></div>
                <p class="ld-hero__desc" data-anim="fade-up" style="--anim-delay:0.3s">
                    Dermatology practices rely on liquid nitrogen for cryotherapy every day. With an on-site generator, you produce exactly what you need — no deliveries, no shortages, no wasted nitrogen.
                </p>
                <div class="ld-hero__actions" data-anim="fade-up" style="--anim-delay:0.4s">
                    <a href="<?php echo esc_url( home_url('/liquid-nitrogen-generators/liquid-nitrogen-price-usa/free-supply-audit/') ); ?>" class="rt-btn">Get a Free Supply Audit</a>
                    <a href="<?php echo esc_url( get_permalink(16) ); ?>" class="rt-btn rt-btn--outline ld-hero__btn-outline">Talk to an Expert</a>
                </div>
            </div>
        </div>

        <div class="ld-hero__image-wrap" data-anim="fade-right" style="--anim-delay:0.2s">
            <img src="<?php echo esc_url( content_url('/uploads/Dermatoligist-using-liquid-nitrogen.jpeg') ); ?>"
                 alt="Dermatologist using liquid nitrogen for cryotherapy treatment"
                 loading="eager">
            <div class="ld-hero__badge">
                <img src="<?php echo esc_url( content_url('/uploads/united-states-of-america-1.svg') ); ?>" alt="USA" width="36" height="36">
                <div>
                    <strong>Made in USA</strong>
                    <span>Trusted by NASA, FBI &amp; Air Force</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         PAIN POINTS
         ══════════════════════════════════════════════ -->
    <section class="ld-pain rt-section">
        <div class="rt-container">
            <div class="rt-section__header" data-anim="fade-up">
                <span class="rt-preheading">The Problem</span>
                <h2>Why Bulk LN2 Delivery Hurts Your Practice</h2>
                <div class="rt-divider" style="margin-inline:auto;"></div>
            </div>
            <div class="ld-pain__grid">
                <div class="ld-pain-card" data-anim="fade-up" style="--anim-delay:0.1s">
                    <div class="ld-pain-card__icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="ld-pain-card__title">Deliveries Get Delayed</h3>
                    <p class="ld-pain-card__text">Supply chain issues, driver shortages, and scheduling conflicts leave your practice without nitrogen — forcing you to cancel procedures and lose revenue.</p>
                </div>
                <div class="ld-pain-card" data-anim="fade-up" style="--anim-delay:0.2s">
                    <div class="ld-pain-card__icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="ld-pain-card__title">Paying $1–$3 Per Liter</h3>
                    <p class="ld-pain-card__text">Bulk delivery pricing for dermatology clinics averages $1 to $3 per liter — plus hidden fees, minimum orders, and annual price hikes. Our generators bring that cost down to $0.13.</p>
                </div>
                <div class="ld-pain-card" data-anim="fade-up" style="--anim-delay:0.3s">
                    <div class="ld-pain-card__icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                    </div>
                    <h3 class="ld-pain-card__title">Nitrogen Evaporates Unused</h3>
                    <p class="ld-pain-card__text">Bulk dewars continuously evaporate. You're paying for nitrogen you never use. An on-site generator produces only what your practice needs, eliminating waste entirely.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         SOLUTION — 50/50
         ══════════════════════════════════════════════ -->
    <section class="ld-solution rt-section">
        <div class="ld-solution__diamond" aria-hidden="true"></div>
        <div class="rt-container">
            <div class="ld-solution__grid">
                <div class="ld-solution__image" data-anim="fade-left">
                    <img src="<?php echo esc_url( content_url('/uploads/Laboratories-clinics-generating-LN2.jpeg') ); ?>"
                         alt="On-site liquid nitrogen generator in a dermatology clinic"
                         loading="lazy">
                </div>
                <div class="ld-solution__content" data-anim="fade-right">
                    <span class="rt-preheading">The Solution</span>
                    <h2>Produce Your Own LN2 On-Site</h2>
                    <div class="rt-divider"></div>
                    <p>Our Titan generators pull nitrogen directly from the ambient air and liquefy it on demand. Your clinic gets a continuous, self-managed supply — no deliveries, no contracts, no surprises.</p>
                    <ul class="ld-solution__list">
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--rt-bright-blue)"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Produce up to 10 liters per day — ideal for 1–3 treatment rooms
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--rt-bright-blue)"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            55-liter storage tank included — always a backup reserve
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--rt-bright-blue)"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Compact footprint — fits in a utility closet or treatment room
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--rt-bright-blue)"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Quiet operation — no disruption to patients or staff
                        </li>
                        <li>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--rt-bright-blue)"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            ROI typically achieved within 12–18 months
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         PRODUCT SPOTLIGHT
         ══════════════════════════════════════════════ -->
    <section class="ld-product rt-section">
        <div class="rt-container">
            <div class="rt-section__header" data-anim="fade-up">
                <span class="rt-preheading">Recommended for Dermatology</span>
                <h2>The Titan 10 — Built for Medical Practices</h2>
                <div class="rt-divider" style="margin-inline:auto;"></div>
            </div>
            <div class="ld-product__grid">
                <div class="ld-product__image" data-anim="fade-left">
                    <?php
                    $product = wc_get_product(6113);
                    $gallery_ids = $product ? $product->get_gallery_image_ids() : [];
                    if ( ! empty($gallery_ids) ) {
                        echo wp_get_attachment_image( $gallery_ids[0], 'large', false, ['class' => 'ld-product__img'] );
                    } else {
                        echo $product ? $product->get_image('large', ['class' => 'ld-product__img']) : '';
                    }
                    ?>
                </div>
                <div class="ld-product__specs" data-anim="fade-right">
                    <h3 class="ld-product__name">Titan | 10L/Day Liquid Nitrogen Generator</h3>
                    <div class="rt-divider"></div>
                    <div class="ld-spec-row"><span class="ld-spec-row__label">Daily Output</span><span class="ld-spec-row__value">10 liters / day</span></div>
                    <div class="ld-spec-row"><span class="ld-spec-row__label">Storage Tank</span><span class="ld-spec-row__value">55 liters included</span></div>
                    <div class="ld-spec-row"><span class="ld-spec-row__label">Cost per liter</span><span class="ld-spec-row__value">~ $0.13</span></div>
                    <div class="ld-spec-row"><span class="ld-spec-row__label">Operation</span><span class="ld-spec-row__value">Fully automatic</span></div>
                    <div class="ld-spec-row"><span class="ld-spec-row__label">Footprint</span><span class="ld-spec-row__value">Compact — fits in utility closet</span></div>
                    <div class="ld-spec-row"><span class="ld-spec-row__label">Origin</span><span class="ld-spec-row__value">Made in USA</span></div>
                    <div class="ld-product__pricing">
                        <span class="ld-product__from">From</span>
                        <?php if ($product) : ?>
                        <span class="ld-product__price"><?php echo $product->get_price_html(); ?></span>
                        <?php endif; ?>
                    </div>
                    <a href="<?php echo esc_url( get_permalink(6113) ); ?>" class="rt-btn">View Product</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         SOCIAL PROOF
         ══════════════════════════════════════════════ -->
    <section class="ld-proof rt-section" style="background:var(--rt-dark-blue);">
        <div class="rt-container">
            <div class="ld-proof__grid">
                <div class="ld-proof__stat" data-anim="fade-up" style="--anim-delay:0s">
                    <div class="ld-proof__number">$35K</div>
                    <div class="ld-proof__label">Average annual savings reported by dermatology clients</div>
                </div>
                <div class="ld-proof__stat" data-anim="fade-up" style="--anim-delay:0.15s">
                    <div class="ld-proof__number">$0.13</div>
                    <div class="ld-proof__label">Cost per liter vs $1–3 with bulk delivery</div>
                </div>
                <div class="ld-proof__stat" data-anim="fade-up" style="--anim-delay:0.3s">
                    <div class="ld-proof__number">100%</div>
                    <div class="ld-proof__label">Uptime — never cancel a procedure due to LN2 shortage</div>
                </div>
            </div>
            <div class="ld-proof__chart" data-anim="fade-up" style="--anim-delay:0.2s">
                <h3 class="ld-proof__chart-title">Cost per Liter — On-Site vs Bulk Delivery</h3>
                <div class="ld-chart">
                    <div class="ld-chart__bars">
                        <div class="ld-chart__col">
                            <div class="ld-chart__value">$0.13</div>
                            <div class="ld-chart__bar-wrap">
                                <div class="ld-chart__bar ld-chart__bar--onsite" style="--bar-h:6%"></div>
                            </div>
                            <div class="ld-chart__label">On-Site<br>Production</div>
                        </div>
                        <div class="ld-chart__col">
                            <div class="ld-chart__value">$1.00</div>
                            <div class="ld-chart__bar-wrap">
                                <div class="ld-chart__bar ld-chart__bar--bulk" style="--bar-h:44%"></div>
                            </div>
                            <div class="ld-chart__label">Bulk<br>Low End</div>
                        </div>
                        <div class="ld-chart__col">
                            <div class="ld-chart__value">$2.00</div>
                            <div class="ld-chart__bar-wrap">
                                <div class="ld-chart__bar ld-chart__bar--bulk" style="--bar-h:72%"></div>
                            </div>
                            <div class="ld-chart__label">Bulk<br>Average</div>
                        </div>
                        <div class="ld-chart__col">
                            <div class="ld-chart__value">$3.00</div>
                            <div class="ld-chart__bar-wrap">
                                <div class="ld-chart__bar ld-chart__bar--bulk ld-chart__bar--peak" style="--bar-h:100%"></div>
                            </div>
                            <div class="ld-chart__label">Bulk<br>High End</div>
                        </div>
                    </div>
                    <div class="ld-chart__legend">
                        <span class="ld-chart__legend-item ld-chart__legend-item--onsite">On-Site Production</span>
                        <span class="ld-chart__legend-item ld-chart__legend-item--bulk">Bulk Delivery</span>
                    </div>
                </div>
            </div>
            <div class="ld-proof__logos" data-anim="fade-up" style="--anim-delay:0.3s">
                <img src="<?php echo esc_url( content_url('/uploads/ln2-generator-client-logo-morgan-dermatology.jpeg') ); ?>" alt="Morgan Dermatology" loading="lazy">
                <img src="<?php echo esc_url( content_url('/uploads/ln2-generator-client-logo-nasa.jpeg') ); ?>" alt="NASA" loading="lazy">
                <img src="<?php echo esc_url( content_url('/uploads/ln2-generator-client-logo-fbi.jpeg') ); ?>" alt="FBI" loading="lazy">
                <img src="<?php echo esc_url( content_url('/uploads/ln2-generator-client-logo-air-force.jpeg') ); ?>" alt="US Air Force" loading="lazy">
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         FAQ
         ══════════════════════════════════════════════ -->
    <section class="ld-faq rt-section" style="background:var(--rt-bg-light);">
        <div class="rt-container">
            <div class="rt-section__header" data-anim="fade-up">
                <span class="rt-preheading">FAQ</span>
                <h2>Questions from Dermatologists</h2>
                <div class="rt-divider" style="margin-inline:auto;"></div>
            </div>
            <div class="ld-faq__list">
                <?php
                $faqs = [
                    [
                        'q' => 'How much liquid nitrogen does a typical dermatology practice need per day?',
                        'a' => 'Most single-location dermatology practices performing cryotherapy for wart removal, actinic keratoses, and skin tag treatments use between 2 and 8 liters per day. The Titan 10 (10 L/day) with its 55-liter storage tank is specifically suited to cover this demand with a comfortable reserve.',
                    ],
                    [
                        'q' => 'Will the generator fit in my practice without disrupting patients?',
                        'a' => 'Yes. The Titan 10 has a compact footprint and is designed for indoor medical environments. It operates quietly and can be installed in a utility closet, back room, or designated supply area — completely out of sight and hearing range of the treatment rooms.',
                    ],
                    [
                        'q' => 'What purity level of liquid nitrogen does it produce?',
                        'a' => 'Our generators produce liquid nitrogen at 99.5% purity, which meets and exceeds the requirements for cryotherapy, dermatological procedures, and clinical applications.',
                    ],
                    [
                        'q' => 'What happens if the generator needs maintenance — will I run out of LN2?',
                        'a' => 'The included 55-liter storage tank acts as a buffer. In the unlikely event of a service need, the reserve tank keeps your practice running while our U.S.-based support team resolves the issue — typically within 24 hours via remote diagnostics.',
                    ],
                    [
                        'q' => 'Does it require special electrical or plumbing infrastructure?',
                        'a' => 'No special plumbing is needed. The generator connects to a standard 208–230V single-phase electrical outlet. Installation is typically completed in half a day and does not require construction work.',
                    ],
                    [
                        'q' => 'How quickly will I see a return on investment?',
                        'a' => 'Based on average dermatology LN2 consumption and current delivery pricing, most practices reach ROI within 12 to 18 months. At $0.13 per liter vs. $1–3 per liter for delivery, the savings compound quickly — often exceeding $20,000–$35,000 annually.',
                    ],
                ];
                foreach ( $faqs as $i => $faq ) : ?>
                <div class="ld-faq__item" data-anim="fade-up" style="--anim-delay:<?php echo $i * 0.08; ?>s">
                    <button class="ld-faq__question" aria-expanded="false">
                        <?php echo esc_html($faq['q']); ?>
                        <span class="ld-faq__icon" aria-hidden="true">+</span>
                    </button>
                    <div class="ld-faq__answer">
                        <p><?php echo esc_html($faq['a']); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════
         FINAL CTA
         ══════════════════════════════════════════════ -->
    <section class="ld-cta rt-section">
        <div class="rt-container">
            <div class="ld-cta__inner" data-anim="fade-up">
                <span class="rt-preheading" style="color:var(--rt-bright-blue);">Get Started</span>
                <h2>See How Much Your Practice Can Save</h2>
                <div class="rt-divider" style="margin-inline:auto; margin-bottom:1.5rem;"></div>
                <p class="ld-cta__desc">Book a free 20-minute supply audit. We'll analyze your current LN2 usage and show you the exact cost savings your practice can expect with an on-site generator.</p>
                <div class="ld-cta__actions">
                    <a href="<?php echo esc_url( home_url('/liquid-nitrogen-generators/liquid-nitrogen-price-usa/free-supply-audit/') ); ?>" class="rt-btn">Get a Free Supply Audit</a>
                    <a href="<?php echo esc_url( get_permalink(16) ); ?>" class="rt-btn rt-btn--outline">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

</main>

<?php get_footer(); ?>
