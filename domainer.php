<?php
/*
Plugin Name: Domainer
Plugin URI: https://github.com/dougwollison/domainer
Description: Domain mapping management for WordPress Multisite.
Version: 1.0.1
Author: Doug Wollison
Author URI: http://dougw.me
Tags: domain mapping, domain management, multisite
License: GPL2
Text Domain: domainer
Domain Path: /languages
*/

// =========================
// ! Constants
// =========================

/**
 * Reference to the plugin file.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'DOMAINER_PLUGIN_FILE', __FILE__ );

/**
 * Reference to the plugin directory.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'DOMAINER_PLUGIN_DIR', dirname( DOMAINER_PLUGIN_FILE ) );

/**
 * Identifies the current database version.
 *
 * @since 1.0.0
 *
 * @var string
 */
define( 'DOMAINER_DB_VERSION', '1.0.0' );

/**
 * Identifies if rewriting has occured.
 *
 * @since 1.0.0
 *
 * @var bool
 */
if ( ! defined( 'DOMAINER_REWRITTEN' ) ) {
	define( 'DOMAINER_REWRITTEN', false );
}

/**
 * Identifies if www was used.
 *
 * @since 1.0.0
 *
 * @var bool
 */
if ( ! defined( 'DOMAINER_USING_WWW' ) ) {
	define( 'DOMAINER_USING_WWW', false );
}

// =========================
// ! Includes
// =========================

require( DOMAINER_PLUGIN_DIR . '/includes/autoloader.php' );
require( DOMAINER_PLUGIN_DIR . '/includes/functions-domainer.php' );
require( DOMAINER_PLUGIN_DIR . '/includes/functions-template.php' );

// =========================
// ! Setup
// =========================

Domainer\System::setup();
