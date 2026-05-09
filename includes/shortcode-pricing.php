<?php
/**
 * [ecom_dynamic_table] shortcode — renders a dynamic, ACF-driven
 * pricing table with collapsible rows and an optional extensions
 * accordion.
 *
 * Reads from these ACF fields on the current post (default schema —
 * field names are kept as-is for backwards compatibility with existing
 * installs; if you're integrating fresh, mirror these names in your
 * field group):
 *
 *   • tour_pricing_note   (text)            — italic intro line
 *   • pricing_footer      (wysiwyg)         — disclaimer block
 *   • tour_departures     (repeater)        — one row per entry
 *       └ departure_label         (text)
 *       └ departure_note          (text, optional badge)
 *       └ prices                  (sub-repeater)
 *           └ price_description   (text)
 *           └ price_amount        (number)
 *       └ land_only_price         (number, optional)
 *       └ single_room_supplement  (number, optional)
 *   • tour_extensions_boolean (true/false)  — toggle for the second accordion
 *   • tour_extension_info     (wysiwyg)
 *   • tour_extension_itinerary (wysiwyg)
 *
 * @package Ecom\DynamicTables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render the [ecom_dynamic_table] shortcode.
 *
 * Bails (returns empty string) on archive / search / 404 / multi-post listings
 * because the shortcode only makes sense in a single-post ACF context.
 *
 * @return string Rendered HTML.
 */
function ecom_dt_render_dynamic_table() {
    if ( ! is_singular() ) {
        return '';
    }

    // Pull assets only when the shortcode is actually rendered.
    wp_enqueue_style( 'ecom-dynamic-tables-css' );
    wp_enqueue_script( 'ecom-dynamic-tables-js' );

    $pricing_note   = get_field( 'tour_pricing_note' );
    $pricing_footer = get_field( 'pricing_footer' );

    // Unique-ish prefix for ARIA id pairing — survives multiple shortcode
    // instances on the same page (rare, but possible inside templates).
    $uid = wp_unique_id( 'ecom-row-' );

    ob_start();
    ?>
    <div class="ecom-pricing-section">

        <div class="ecom-pricing-header">
            <h2 class="ecom-pricing-title">Rates and Dates</h2>
            <?php if ( $pricing_note ) : ?>
                <div class="ecom-pricing-note"><?php echo wp_kses_post( $pricing_note ); ?></div>
            <?php endif; ?>
        </div>

        <?php if ( have_rows( 'tour_departures' ) ) : ?>
            <div class="ecom-rows">
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

                    // First row renders open; the rest start collapsed.
                    $is_open    = ( 1 === $row_index );
                    $body_id    = $uid . '-body-' . $row_index;
                    $expanded   = $is_open ? 'true' : 'false';
                    $block_cls  = 'ecom-row-block' . ( $is_open ? ' is-open' : '' );
                    ?>
                    <div class="<?php echo esc_attr( $block_cls ); ?>">

                        <button
                            type="button"
                            class="ecom-row-toggle"
                            aria-expanded="<?php echo esc_attr( $expanded ); ?>"
                            aria-controls="<?php echo esc_attr( $body_id ); ?>"
                        >
                            <span class="ecom-row-label">
                                <?php echo esc_html( $dep_label ); ?>
                            </span>

                            <?php if ( $dep_note ) : ?>
                                <span class="ecom-row-toggle-caption">
                                    <?php echo esc_html( $dep_note ); ?>
                                </span>
                            <?php endif; ?>

                            <span class="ecom-row-toggle-cta">
                                <?php esc_html_e( 'Click here to see prices', 'ecom-dynamic-tables' ); ?>
                            </span>

                            <span class="ecom-row-chevron" aria-hidden="true">
                                <svg width="18" height="25" viewBox="0 0 18 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M9.52484 23.9872L17.7697 13.6221C17.8748 13.543 17.8668 13.4481 17.7535 13.3374C17.6402 13.2267 17.5431 13.1952 17.4622 13.2426L9.7595 19.789L9.7595 0.118128C9.7595 0.118128 9.67048 0.0707092 9.48439 0.0390682C9.30641 0.015358 9.09603 -0.00050354 8.86139 -0.00050354C8.68332 -0.00050354 8.4973 0.015358 8.31931 0.0390682C8.14123 0.0627766 8.04416 0.0864868 8.04416 0.118128L8.04416 19.8443L0.341431 13.2426C0.292896 13.1952 0.212063 13.2109 0.106876 13.2979C0.00168991 13.3849 -0.0226631 13.4955 0.0258713 13.6221L8.27069 23.9872C8.47303 24.2639 8.66725 24.4062 8.85327 24.4062C9.03938 24.4062 9.30641 24.2718 9.50869 23.9872H9.52484Z" fill="#384049"/>
                                </svg>
                            </span>
                        </button>

                        <div
                            class="ecom-row-body<?php echo $is_open ? '' : ' is-collapsed'; ?>"
                            id="<?php echo esc_attr( $body_id ); ?>"
                        >
                            <?php if ( have_rows( 'prices' ) ) : ?>
                                <table class="ecom-prices-table">
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
                                                <td><?php echo esc_html( ecom_dt_format_price( get_sub_field( 'price_amount' ) ) ); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>

                            <?php if ( $land_only || $single_sup ) : ?>
                                <div class="ecom-extras">
                                    <?php if ( $land_only ) : ?>
                                        <p class="ecom-extras-land-only">
                                            <strong>Land Only:</strong>
                                            <span class="ecom-extras-price"><?php echo esc_html( ecom_dt_format_price( $land_only ) ); ?></span>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ( $single_sup ) : ?>
                                        <p class="ecom-extras-single-supplement">
                                            <strong>Single Room Supplement:</strong>
                                            <span class="ecom-extras-price"><?php echo esc_html( ecom_dt_format_price( $single_sup ) ); ?></span>
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
         * Optional extensions accordion.
         *
         * Renders a single collapsed-by-default row directly below the
         * rows block (and above the footer) when
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
            <div class="ecom-extensions-section">
                <div class="ecom-row-block">
                    <button
                        type="button"
                        class="ecom-row-toggle"
                        aria-expanded="false"
                        aria-controls="<?php echo esc_attr( $ext_body_id ); ?>"
                    >
                        <span class="ecom-row-label">
                            <?php esc_html_e( 'Extensions (optional)', 'ecom-dynamic-tables' ); ?>
                        </span>

                        <span class="ecom-row-toggle-cta">
                            <?php esc_html_e( 'Click here to see info', 'ecom-dynamic-tables' ); ?>
                        </span>

                        <span class="ecom-row-chevron" aria-hidden="true">
                            <svg width="18" height="25" viewBox="0 0 18 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.52484 23.9872L17.7697 13.6221C17.8748 13.543 17.8668 13.4481 17.7535 13.3374C17.6402 13.2267 17.5431 13.1952 17.4622 13.2426L9.7595 19.789L9.7595 0.118128C9.7595 0.118128 9.67048 0.0707092 9.48439 0.0390682C9.30641 0.015358 9.09603 -0.00050354 8.86139 -0.00050354C8.68332 -0.00050354 8.4973 0.015358 8.31931 0.0390682C8.14123 0.0627766 8.04416 0.0864868 8.04416 0.118128L8.04416 19.8443L0.341431 13.2426C0.292896 13.1952 0.212063 13.2109 0.106876 13.2979C0.00168991 13.3849 -0.0226631 13.4955 0.0258713 13.6221L8.27069 23.9872C8.47303 24.2639 8.66725 24.4062 8.85327 24.4062C9.03938 24.4062 9.30641 24.2718 9.50869 23.9872H9.52484Z" fill="#384049"/>
                            </svg>
                        </span>
                    </button>

                    <div
                        class="ecom-row-body is-collapsed ecom-extensions-body"
                        id="<?php echo esc_attr( $ext_body_id ); ?>"
                    >
                        <?php if ( $extension_info ) : ?>
                            <div class="ecom-extension-info">
                                <?php echo wp_kses_post( $extension_info ); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ( $extension_itin ) : ?>
                            <div class="ecom-extension-itinerary">
                                <?php echo wp_kses_post( $extension_itin ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $pricing_footer ) : ?>
            <div class="ecom-pricing-footer">
                <?php echo wp_kses_post( $pricing_footer ); ?>
            </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ecom_dynamic_table', 'ecom_dt_render_dynamic_table' );
