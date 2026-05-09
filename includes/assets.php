<?php
/**
 * Asset registration for the dynamic-tables module.
 *
 * Styles are registered (not enqueued) on `wp_enqueue_scripts` so each
 * shortcode can decide for itself whether to enqueue them — this prevents
 * site-wide CSS bloat on pages that don't render a table.
 *
 * @package Ecom\DynamicTables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the shared dynamic-tables stylesheet.
 *
 * Uses `filemtime()` as the version string so deploys invalidate browser
 * caches automatically.
 *
 * @return void
 */
function ecom_dynamic_tables_enqueue_assets() {
    $base_url = get_stylesheet_directory_uri() . '/dynamic-tables';
    $base_dir = get_stylesheet_directory()     . '/dynamic-tables';

    wp_register_style(
        'ecom-dynamic-tables-css',
        $base_url . '/dynamic-tables.css',
        array(),
        filemtime( $base_dir . '/dynamic-tables.css' )
    );

    wp_register_script(
        'ecom-dynamic-tables-js',
        $base_url . '/dynamic-tables.js',
        array(),
        filemtime( $base_dir . '/dynamic-tables.js' ),
        true // Load in footer.
    );
}
add_action( 'wp_enqueue_scripts', 'ecom_dynamic_tables_enqueue_assets' );
