<?php
/**
 * Plugin Name: BHT Dynamic Tables
 * Description: Shortcodes that render dynamic, ACF-driven tables (pricing,
 *              etc.) on Blue Heart Travel tour pages.
 * Author:      Blue Heart Travel
 * Version:     1.1.0
 *
 * This is a Divi child-theme module — not a plugin. To activate, add this
 * line to your child theme's `functions.php`:
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
 *       └── shortcode-pricing.php   ← [tour_pricing]
 *
 * Adding a new shortcode = drop a new `includes/shortcode-*.php` file and
 * `require_once` it below. No changes anywhere else.
 *
 * @package BHT\DynamicTables
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'BHT_DT_DIR' ) ) {
    define( 'BHT_DT_DIR', __DIR__ );
}
if ( ! defined( 'BHT_DT_VERSION' ) ) {
    define( 'BHT_DT_VERSION', '1.1.0' );
}

/*
 * Load order:
 *   1. assets.php             — registers CSS (used by shortcodes).
 *   2. helpers.php            — defines bht_dt_format_price() etc.
 *   3. shortcode-*.php files  — each shortcode is self-contained.
 */
require_once BHT_DT_DIR . '/includes/assets.php';
require_once BHT_DT_DIR . '/includes/helpers.php';
require_once BHT_DT_DIR . '/includes/shortcode-pricing.php';
