<section class="rt-products-section" id="products">
    <div class="rt-container">

        <div class="rt-products-section__header">
            <span class="rt-preheading" style="display:block;text-align:center;">Products</span>
            <h2 style="text-align:center;">Produce, Store, Transfer, and Secure Industrial Gas Supply</h2>
            <p style="text-align:center;max-width:640px;margin-inline:auto;margin-top:1rem;">
                We offer an extensive range of essential products, from on-site liquid nitrogen generators to bulk storage tanks for Oxygen, Argon, and other industrial gases.
            </p>
        </div>

        <?php
        $cat_ids = [ 177, 130, 178, 175 ]; // Oxygen, LN2, LabGas, Hydrogen
        ?>
        <div class="rt-carousel rt-products-carousel" id="rt-products-carousel" data-autoplay="false" data-visible="3">
            <div class="rt-carousel__track">
                <?php foreach ( $cat_ids as $cat_id ) :
                    $term        = get_term( $cat_id, 'product_cat' );
                    if ( ! $term || is_wp_error( $term ) ) continue;
                    $thumb_id  = get_term_meta( $cat_id, 'thumbnail_id', true );
                    $thumb_url = $thumb_id ? wp_get_attachment_image_src( $thumb_id, 'large' )[0] : '';
                    $link      = get_term_link( $term );
                ?>
                <div class="rt-carousel__slide rt-product-card">
                    <a href="<?php echo esc_url( $link ); ?>" class="rt-product-card__link">
                        <div class="rt-product-card__image">
                            <?php if ( $thumb_url ) : ?>
                                <img src="<?php echo esc_url( $thumb_url ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="rt-product-card__no-img"></div>
                            <?php endif; ?>
                        </div>
                        <div class="rt-product-card__body">
                            <h5 class="rt-product-card__title"><?php echo esc_html( $term->name ); ?></h5>
                            <span class="rt-product-card__discover">Discover</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="rt-products__nav">
                <button class="rt-carousel__prev" aria-label="Précédent">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M13 4l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button class="rt-carousel__next" aria-label="Suivant">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M7 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>

    </div>
</section>
