<?php
/**
 * [tour_pricing] shortcode — renders a tour's pricing tables.
 *
 * Reads from these ACF fields on the current post:
 *   • tour_pricing_note   (text)            — italic intro line
 *   • pricing_footer      (wysiwyg)         — disclaimer block
 *   • tour_departures     (repeater)        — one block per departure
 *       └ departure_label         (text)
 *       └ departure_note          (text, optional badge)
 *       └ prices                  (sub-repeater)
 *           └ price_description   (text)
 *           └ price_amount        (number)
 *       └ land_only_price         (number, optional)
 *       └ single_room_supplement  (number, optional)
 *
 * Markup is intentionally unprefixed (`.tour-pricing-section`,
 * `.departure-block`, …) to stay backwards-compatible with any inline
 * styling already living in posts. See README.md for the rationale.
 *
 * @package BHT\DynamicTables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render the [tour_pricing] shortcode.
 *
 * Bails (returns empty string) on archive / search / 404 / multi-post listings
 * because the shortcode only makes sense in a single-post ACF context.
 *
 * @return string Rendered HTML.
 */
function bht_dt_render_tour_pricing() {
    if ( ! is_singular() ) {
        return '';
    }

    // Pull assets only when the shortcode is actually rendered.
    wp_enqueue_style( 'dynamic-tables-css' );
    wp_enqueue_script( 'dynamic-tables-js' );

    $pricing_note   = get_field( 'tour_pricing_note' );
    $pricing_footer = get_field( 'pricing_footer' );

    // Unique-ish prefix for ARIA id pairing — survives multiple shortcode
    // instances on the same page (rare, but possible inside templates).
    $uid = wp_unique_id( 'bht-dep-' );

    ob_start();
    ?>
    <div class="tour-pricing-section">

        <div class="tour-pricing-header">
            <h2 class="tour-pricing-title">Rates and Dates</h2>
            <?php if ( $pricing_note ) : ?>
                <div class="tour-pricing-note"><?php echo wp_kses_post( $pricing_note ); ?></div>
            <?php endif; ?>
        </div>

        <?php if ( have_rows( 'tour_departures' ) ) : ?>
            <div class="tour-departures">
                <?php
                $row_index = 0;
                while ( have_rows( 'tour_departures' ) ) :
                    the_row();
                    $row_index++;

                    // Cache sub-fields once per row.
                    $dep_label  = get_sub_field( 'departure_label' );
                    $dep_note   = get_sub_field( 'departure_note' );
                    $land_only  = get_sub_field( 'land_only_price' );
                    $single_sup = get_sub_field( 'single_room_supplement' );

                    // First departure renders open; the rest start collapsed.
                    $is_open    = ( 1 === $row_index );
                    $body_id    = $uid . '-body-' . $row_index;
                    $expanded   = $is_open ? 'true' : 'false';
                    $block_cls  = 'departure-block' . ( $is_open ? ' is-open' : '' );
                    ?>
                    <div class="<?php echo esc_attr( $block_cls ); ?>">

                        <button
                            type="button"
                            class="departure-toggle"
                            aria-expanded="<?php echo esc_attr( $expanded ); ?>"
                            aria-controls="<?php echo esc_attr( $body_id ); ?>"
                        >
                            <span class="departure-label">
                                <?php echo esc_html( $dep_label ); ?>
                            </span>

                            <?php if ( $dep_note ) : ?>
                                <span class="departure-toggle-caption">
                                    <?php echo esc_html( $dep_note ); ?>
                                </span>
                            <?php endif; ?>

                            <span class="departure-toggle-cta">
                                <?php esc_html_e( 'Click here to see prices', 'bht' ); ?>
                            </span>

                            <span class="departure-chevron" aria-hidden="true">
                                <svg width="18" height="25" viewBox="0 0 18 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.52484 23.9872L17.7697 13.6221C17.8748 13.543 17.8668 13.4481 17.7535 13.3374C17.6402 13.2267 17.5431 13.1952 17.4622 13.2426L9.7595 19.789L9.7595 0.118128C9.7595 0.118128 9.67048 0.0707092 9.48439 0.0390682C9.30641 0.015358 9.09603 -0.00050354 8.86139 -0.00050354C8.68332 -0.00050354 8.4973 0.015358 8.31931 0.0390682C8.14123 0.0627766 8.04416 0.0864868 8.04416 0.118128L8.04416 19.8443L0.341431 13.2426C0.292896 13.1952 0.212063 13.2109 0.106876 13.2979C0.00168991 13.3849 -0.0226631 13.4955 0.0258713 13.6221L8.27069 23.9872C8.47303 24.2639 8.66725 24.4062 8.85327 24.4062C9.03938 24.4062 9.30641 24.2718 9.50869 23.9872H9.52484Z" fill="#384049"/>
                                </svg>
                            </span>
                        </button>

                        <div
                            class="departure-body<?php echo $is_open ? '' : ' is-collapsed'; ?>"
                            id="<?php echo esc_attr( $body_id ); ?>"
                        >
                            <?php if ( have_rows( 'prices' ) ) : ?>
                                <table class="departure-prices-table">
                                    <thead>
                                        <tr>
                                            <th>Description</th>
                                            <th>Est. Price ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ( have_rows( 'prices' ) ) : the_row(); ?>
                                            <tr>
                                                <td><?php echo esc_html( get_sub_field( 'price_description' ) ); ?></td>
                                                <td><?php echo esc_html( bht_dt_format_price( get_sub_field( 'price_amount' ) ) ); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                            <?php if ( $land_only || $single_sup ) : ?>
                                <div class="departure-extras">
                                    <?php if ( $land_only ) : ?>
                                        <p class="land-only">
                                            <strong>Land Only:</strong>
                                            <span class="departure-extras-price"><?php echo esc_html( bht_dt_format_price( $land_only ) ); ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ( $single_sup ) : ?>
                                        <p class="single-supplement">
                                            <strong>Single Room Supplement:</strong>
                                            <span class="departure-extras-price"><?php echo esc_html( bht_dt_format_price( $single_sup ) ); ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

        <?php
        /*
         * Optional "Tour Extensions" accordion.
         *
         * Renders a single collapsed-by-default row directly below the
         * departures block (and above the pricing footer) when
         * `tour_extensions_boolean` is truthy. Body stacks two WYSIWYGs
         * back-to-back (info + itinerary). The whole block is hidden if
         * the boolean is false OR if both WYSIWYGs are empty
         * (defensive — avoids an empty toggle).
         */
        $extensions_on  = (bool) get_field( 'tour_extensions_boolean' );
        $extension_info = $extensions_on ? get_field( 'tour_extension_info' )      : '';
        $extension_itin = $extensions_on ? get_field( 'tour_extension_itinerary' ) : '';

        if ( $extensions_on && ( $extension_info || $extension_itin ) ) :
            $ext_body_id = $uid . '-extensions';
            ?>
            <div class="tour-extensions-section">
                <div class="departure-block">
                    <button
                        type="button"
                        class="departure-toggle"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr( $ext_body_id ); ?>"
                    >
                        <span class="departure-label">
                            <?php esc_html_e( 'Tour Extensions (optional)', 'bht' ); ?>
                        </span>

                        <span class="departure-toggle-cta">
                            <?php esc_html_e( 'Click here to see info', 'bht' ); ?>
                        </span>

                        <span class="departure-chevron" aria-hidden="true">
                            <svg width="18" height="25" viewBox="0 0 18 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.52484 23.9872L17.7697 13.6221C17.8748 13.543 17.8668 13.4481 17.7535 13.3374C17.6402 13.2267 17.5431 13.1952 17.4622 13.2426L9.7595 19.789L9.7595 0.118128C9.7595 0.118128 9.67048 0.0707092 9.48439 0.0390682C9.30641 0.015358 9.09603 -0.00050354 8.86139 -0.00050354C8.68332 -0.00050354 8.4973 0.015358 8.31931 0.0390682C8.14123 0.0627766 8.04416 0.0864868 8.04416 0.118128L8.04416 19.8443L0.341431 13.2426C0.292896 13.1952 0.212063 13.2109 0.106876 13.2979C0.00168991 13.3849 -0.0226631 13.4955 0.0258713 13.6221L8.27069 23.9872C8.47303 24.2639 8.66725 24.4062 8.85327 24.4062C9.03938 24.4062 9.30641 24.2718 9.50869 23.9872H9.52484Z" fill="#384049"/>
                            </svg>
                        </span>
                    </button>

                    <div
                        class="departure-body is-collapsed tour-extensions-body"
                        id="<?php echo esc_attr( $ext_body_id ); ?>"
                    >
                        <?php if ( $extension_info ) : ?>
                            <div class="tour-extension-info">
                                <?php echo wp_kses_post( $extension_info ); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ( $extension_itin ) : ?>
                            <div class="tour-extension-itinerary">
                                <?php echo wp_kses_post( $extension_itin ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $pricing_footer ) : ?>
            <div class="tour-pricing-footer">
                <?php echo wp_kses_post( $pricing_footer ); ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'tour_pricing', 'bht_dt_render_tour_pricing' );
