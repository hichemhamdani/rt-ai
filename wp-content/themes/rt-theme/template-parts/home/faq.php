<?php
$faqs = [
    [
        'q' => 'What is the difference between a VJ hose and a stainless hose?',
        'a' => 'A vacuum-jacketed (VJ) hose offers superior thermal insulation, minimizing LN2 boil-off during transfer. Stainless hoses are more affordable but have higher heat gain. For high-volume or frequent transfers, VJ hoses are the preferred choice.',
    ],
    [
        'q' => 'What is the main difference between a portable small O2 monitor and a wall-mounted one?',
        'a' => 'Portable O2 monitors are ideal for mobile use and spot-checking different areas. Wall-mounted units provide continuous fixed-point monitoring with alarm relays — recommended for rooms where LN2 is stored or used regularly.',
    ],
    [
        'q' => 'Should I use copper or stainless?',
        'a' => 'Copper remains ductile at cryogenic temperatures, making it the standard choice for LN2 fittings. Stainless steel can be used, but copper is preferred for standard installations due to its thermal properties and ease of brazing.',
    ],
    [
        'q' => 'Compression fittings or not?',
        'a' => 'Compression fittings are generally not recommended for LN2 service. The thermal cycling causes metal contraction that can lead to leaks. Brazed or cryogenic-rated flare fittings are the industry standard.',
    ],
    [
        'q' => 'How much electrical power do these units use?',
        'a' => 'Requirements vary by model. Our Titan 10L unit runs on standard 120V/15A service. Larger units (130L–960L/day) typically require 208–240V, 3-phase power. Our team provides a complete electrical specification for every installation.',
    ],
    [
        'q' => 'Do on-site generators require extra fittings and accessories?',
        'a' => 'Yes. Every RT generator ships with a standard accessory kit including fill hose, pressure-relief valve, and connection fittings. Additional accessories such as extended hoses, phase separators, and safety brackets are available in our shop.',
    ],
];
?>
<section class="rt-section rt-faq" id="faq">
    <div class="rt-container">

        <div class="rt-faq__header">
            <span class="rt-preheading">FAQ</span>
            <h2 class="rt-faq__title">Frequently Asked Questions</h2>
            <p class="rt-faq__subtitle">From cryogenic piping to on-site industrial gas generation, our engineering team answers the most frequently asked questions.</p>
        </div>

        <div class="rt-accordion" id="rt-accordion">
            <?php foreach ( $faqs as $i => $faq ) : ?>
            <div class="rt-accordion__item <?php echo $i === 0 ? 'is-open' : ''; ?>">
                <button class="rt-accordion__trigger" aria-expanded="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                    <span><?php echo esc_html( $faq['q'] ); ?></span>
                    <span class="rt-accordion__icon" aria-hidden="true">
                        <svg class="rt-accordion__plus"  width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 4v12M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        <svg class="rt-accordion__minus" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M4 10h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                </button>
                <div class="rt-accordion__body">
                    <p><?php echo esc_html( $faq['a'] ); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>
