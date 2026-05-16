<?php
/**
 * Plugin Name: Quickpay for WooCommerce
 * Plugin URI: http://wordpress.org/plugins/woocommerce-quickpay/
 * Description: Integrates your Quickpay payment gateway into your WooCommerce installation.
 * Version: 8.0.3
 * Author: Perfect Solution
 * Text Domain: woocommerce-quickpay
 * Domain Path: /languages
 * Author URI: http://perfect-solution.dk
 * Wiki: http://quickpay.perfect-solution.dk/
 *
 * WC requires at least: 7.1.0
 *
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use QuickpayPSP\Autoload;
use QuickpayPSP\Plugin;

define( 'WCQP_VERSION', '8.0.3' );
define( 'QUICKPAY_PLUGIN_FILE', __FILE__ );
define( 'QUICKPAY_PLUGIN_PATH', plugin_dir_path( QUICKPAY_PLUGIN_FILE ) );

require_once __DIR__ . '/src/Autoload.php';
Autoload::register( __DIR__ . '/src' );

// Load compatibility layer (global functions + legacy WC_QP() helper)
require_once __DIR__ . '/src/Compatibility/functions-capabilities.php';
require_once __DIR__ . '/src/Compatibility/class-aliases.php';
require_once __DIR__ . '/src/Compatibility/wc-qp.php';

// Public API functions (stable surface for 3rd-party plugins)
require_once __DIR__ . '/src/PublicApi/functions.php';

// Optional Composer autoload if present (never required)
$composerAutoload = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $composerAutoload ) ) {
	require_once $composerAutoload;
}

Plugin::boot();

