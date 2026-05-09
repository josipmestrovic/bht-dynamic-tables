<?php
/**
 * Shared helpers for the dynamic-tables shortcodes.
 *
 * Keep this file dependency-free — anything in here should be safe to call
 * from any shortcode without side effects.
 *
 * @package Ecom\DynamicTables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Format a numeric price as a localized currency string.
 *
 * Output style mirrors the original `$X,XXX.XX` format used in the pricing
 * table so existing pages don't visually shift. Both the symbol and the
 * formatted output are filterable for future i18n / multi-currency work.
 *
 * @param int|float|string $amount Raw value from ACF (numbers may arrive as strings).
 * @return string Already-escaped, ready to echo.
 */
function ecom_dt_format_price( $amount ) {
    $symbol = apply_filters( 'ecom_dt_currency_symbol', '$' );
    $value  = (float) $amount;

    /**
     * Filter the rendered price string.
     *
     * @param string           $rendered Final formatted string (e.g. "$1,250.00").
     * @param int|float|string $amount   Raw input.
     * @param string           $symbol   Currency symbol used.
     */
    return apply_filters(
        'ecom_dt_format_price',
        $symbol . number_format_i18n( $value, 2 ),
        $amount,
        $symbol
    );
}

/**
 * Render a WYSIWYG field value through `the_content` with wptexturize
 * disabled.
 *
 * `wptexturize()` rewrites straight ASCII quotes/apostrophes/dashes into
 * typographic equivalents (U+2018, U+2019, U+201C, U+201D, U+2013, U+2014,
 * U+2026). When the rendering font on the page lacks glyphs for those
 * codepoints the browser shows NO GLYPH tofu. Skipping texturization
 * keeps whatever the editor typed (ASCII), which every font supports.
 *
 * The toggle is scoped to this single render call so the rest of the
 * page (post content, other modules) keeps its smart quotes.
 *
 * @param string $value Raw WYSIWYG value from ACF.
 * @return string Rendered HTML.
 */
function ecom_dt_render_wysiwyg( $value ) {
    if ( ! is_string( $value ) || '' === $value ) {
        return '';
    }

    add_filter( 'run_wptexturize', '__return_false' );
    $rendered = apply_filters( 'the_content', $value );
    remove_filter( 'run_wptexturize', '__return_false' );

    return $rendered;
}
