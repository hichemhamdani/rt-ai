<nav class="rt-mainnav" id="rt-mainnav" aria-label="Navigation principale">
    <div class="rt-mainnav__inner rt-container">

        <!-- Logo -->
        <a href="<?php echo esc_url( home_url('/') ); ?>" class="rt-mainnav__logo" aria-label="Rutherford &amp; Titan — Accueil">
            <span class="rt-logo-full">Rutherford &amp; Titan</span>
            <span class="rt-logo-short">RT</span>
        </a>

        <!-- Menu desktop -->
        <ul class="rt-nav" id="rt-nav">

            <!-- Solutions (megamenu) -->
            <li class="rt-nav__item rt-nav__item--mega" id="rt-mega-solutions">
                <button class="rt-nav__link rt-nav__link--btn" aria-expanded="false" aria-controls="rt-mega-panel-solutions">
                    Solutions
                    <svg class="rt-nav__chevron" width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </button>
                <div class="rt-mega" id="rt-mega-panel-solutions" role="region">
                    <div class="rt-mega__inner rt-container">

                        <!-- Col 1 LN2 -->
                        <div class="rt-mega__col rt-mega__col--ln2">
                            <p class="rt-mega__col-title">LN2</p>
                            <ul>
                                <li>
                                    <a href="<?php echo esc_url( get_permalink(2949) ); ?>">
                                        On-Site LN2 Generators
                                        <span class="rt-badge">New models</span>
                                    </a>
                                </li>
                                <li><a href="<?php echo esc_url( get_permalink(3722) ); ?>">The True Cost of LN2 in The USA</a></li>
                                <li><a href="<?php echo esc_url( get_permalink(4265) ); ?>">Our Clients &amp; Applications</a></li>
                                <li><a href="<?php echo esc_url( get_permalink(3696) ); ?>">Tech Comparison: Membrane Vs. PSA</a></li>
                            </ul>
                        </div>

                        <!-- Col 2 Gases -->
                        <div class="rt-mega__col">
                            <p class="rt-mega__col-title">Gases</p>
                            <ul>
                                <li><a href="<?php echo esc_url( get_term_link(130, 'product_cat') ); ?>">Nitrogen Gas Generators</a></li>
                                <li><a href="#">Oxygen Generators</a></li>
                                <li><a href="#">Helium Generators</a></li>
                                <li><a href="#">Hydrogen Generators</a></li>
                            </ul>
                        </div>

                        <!-- Col 3 Storage -->
                        <div class="rt-mega__col">
                            <p class="rt-mega__col-title">Storage, Transfer &amp; Safety</p>
                            <ul>
                                <li><a href="<?php echo esc_url( get_term_link(162, 'product_cat') ); ?>">Bulk Storage Tanks</a></li>
                                <li><a href="<?php echo esc_url( get_term_link(163, 'product_cat') ); ?>">Cryogenic Hoses &amp; Pipes</a></li>
                                <li><a href="<?php echo esc_url( get_term_link(164, 'product_cat') ); ?>">Safety Systems</a></li>
                                <li><a href="<?php echo esc_url( get_term_link(165, 'product_cat') ); ?>">Accessories</a></li>
                            </ul>
                        </div>

                    </div>
                </div>
            </li>

            <!-- Clients & Applications -->
            <li class="rt-nav__item">
                <a href="<?php echo esc_url( get_permalink(4265) ); ?>" class="rt-nav__link">Clients &amp; Applications</a>
            </li>

            <!-- About Us -->
            <li class="rt-nav__item">
                <a href="<?php echo esc_url( get_permalink(2815) ); ?>" class="rt-nav__link">About Us</a>
            </li>

            <!-- Contact -->
            <li class="rt-nav__item">
                <a href="<?php echo esc_url( get_permalink(16) ); ?>" class="rt-nav__link">Contact</a>
            </li>

        </ul>

        <!-- CTA Button -->
        <a href="tel:+12819403597" class="rt-mainnav__cta">
            Call (281) 940-3597
        </a>

        <!-- Burger mobile -->
        <button class="rt-burger" id="rt-burger" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="rt-nav">
            <span></span><span></span><span></span>
        </button>

    </div>
</nav>
