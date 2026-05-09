<?php
/**
 * Plugin Name: Ecom Dynamic Tables
 * Description: Shortcodes that render dynamic, ACF-driven tables
 *              (pricing tables with collapsible rows + an optional
 *              extensions accordion) on any WordPress site.
 * Author:      Josip Meštrović
 * Version:     2.0.0
 *
 * This is a Divi (or any WordPress theme) child-theme module — not a
 * plugin. To activate, add this line to your child theme's
 * `functions.php`:
 *
 *     require_once get_stylesheet_directory() . '/dynamic-tables/dynamic-tables.php';
 *
 * File structure:
 *   dynamic-tables/
 *   ├── dynamic-tables.php          ← this bootstrap (loads modules below)
 *   ├── dynamic-tables.css          ← shared shortcode styles
 *   ├── dynamic-tables.js           ← accordion controller (vanilla, no deps)
 *   ├── README.md                   ← module documentation
 *   └── includes/
 *       ├── assets.php              ← register CSS + JS
 *       ├── helpers.php             ← currency formatter + WYSIWYG renderer (filterable)
 *       └── shortcode-pricing.php   ← [ecom_dynamic_table]
 *
 * Adding a new shortcode = drop a new `includes/shortcode-*.php` file and
 * `require_once` it below. No changes anywhere else.
 *
 * @package Ecom\DynamicTables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'ECOM_DT_DIR' ) ) {
    define( 'ECOM_DT_DIR', __DIR__ );
}
if ( ! defined( 'ECOM_DT_VERSION' ) ) {
    define( 'ECOM_DT_VERSION', '2.0.0' );
}

/*
 * Load order:
 *   1. assets.php             — registers CSS (used by shortcodes).
 *   2. helpers.php            — defines ecom_dt_format_price() etc.
 *   3. shortcode-*.php files  — each shortcode is self-contained.
 */
require_once ECOM_DT_DIR . '/includes/assets.php';
require_once ECOM_DT_DIR . '/includes/helpers.php';
require_once ECOM_DT_DIR . '/includes/shortcode-pricing.php';
