<?php

namespace QuickpayPSP\Compatibility;

use QuickpayPSP\Admin\Support\AssetEnqueuer;
use QuickpayPSP\Support\Dependencies;
use QuickpayPSP\Utilities\ArrayUtils;
use QuickpayPSP\Utilities\MoneyUtils;
use QuickpayPSP\Utilities\RandomUtils;
use QuickpayPSP\WooCommerce\Gateways\GatewayIcons;
use QuickpayPSP\WooCommerce\Support\GatewayUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;
use WC_Payment_Gateway;

/**
 * Legacy helper methods wrapper.
 *
 * The old WC_QuickPay_Helper class is aliased to this class.
 *
 * @deprecated Use the individual utility/service classes instead.
 */
class LegacyHelper {

	/**
	 * @deprecated Use MoneyUtils::price_normalize() instead.
	 */
	public static function price_normalize( $price, $currency ) {
		_deprecated_function( __METHOD__, '8.0.0', MoneyUtils::class . '::price_normalize()' );

		return MoneyUtils::price_normalize( $price, $currency );
	}

	/**
	 * @deprecated Use MoneyUtils::price_multiplied_to_float() instead.
	 */
	public static function price_multiplied_to_float( $price, $currency ) {
		_deprecated_function( __METHOD__, '8.0.0', MoneyUtils::class . '::price_multiplied_to_float()' );

		return MoneyUtils::price_multiplied_to_float( $price, $currency );
	}

	/**
	 * @deprecated Use MoneyUtils::price_custom_to_multiplied() instead.
	 */
	public static function price_custom_to_multiplied( $price, $currency ) {
		_deprecated_function( __METHOD__, '8.0.0', MoneyUtils::class . '::price_custom_to_multiplied()' );

		return MoneyUtils::price_custom_to_multiplied( $price, $currency );
	}

	/**
	 * @deprecated Use MoneyUtils::price_multiply() instead.
	 */
	public static function price_multiply( $price, $currency = null ) {
		_deprecated_function( __METHOD__, '8.0.0', MoneyUtils::class . '::price_multiply()' );

		return MoneyUtils::price_multiply( $price, $currency );
	}

	/**
	 * @deprecated Use MoneyUtils::is_currency_using_decimals() instead.
	 */
	public static function is_currency_using_decimals( $currency ) {
		_deprecated_function( __METHOD__, '8.0.0', MoneyUtils::class . '::is_currency_using_decimals()' );

		return MoneyUtils::is_currency_using_decimals( $currency );
	}

	/**
	 * @deprecated Use AssetEnqueuer::enqueue_javascript_backend() instead.
	 */
	public static function enqueue_javascript_backend() {
		_deprecated_function( __METHOD__, '8.0.0', AssetEnqueuer::class . '::enqueue_javascript_backend()' );

		AssetEnqueuer::enqueue_javascript_backend();
	}

	/**
	 * @deprecated
	 */
	protected static function maybe_enqueue_admin_statics(): bool {
		_deprecated_function( __METHOD__, '8.0.0' );

		return false;
	}

	/**
	 * @deprecated Use AssetEnqueuer::static_version() instead.
	 */
	public static function static_version(): string {
		_deprecated_function( __METHOD__, '8.0.0', AssetEnqueuer::class . '::static_version()' );

		return AssetEnqueuer::static_version();
	}

	/**
	 * @deprecated Use AssetEnqueuer::enqueue_stylesheet() instead.
	 */
	public static function enqueue_stylesheet() {
		_deprecated_function( __METHOD__, '8.0.0', AssetEnqueuer::class . '::enqueue_stylesheet()' );

		AssetEnqueuer::enqueue_stylesheet();
	}

	/**
	 * @deprecated Use AssetEnqueuer::load_i18n() instead.
	 */
	public static function load_i18n() {
		_deprecated_function( __METHOD__, '8.0.0', AssetEnqueuer::class . '::load_i18n()' );

		AssetEnqueuer::load_i18n();
	}

	/**
	 * @deprecated
	 */
	public static function option_is_enabled( $value ) {
		_deprecated_function( __METHOD__, '8.0.0' );

		return ( $value === 'yes' ) ? 1 : 0;
	}

	/**
	 * @deprecated
	 */
	public static function get_callback_url( $post_id = null ) {
		_deprecated_function( __METHOD__, '8.0.0' );

		$args = [ 'wc-api' => 'WC_QuickPay' ];

		if ( $post_id !== null ) {
			$args['order_post_id'] = $post_id;
		}

		$args = apply_filters( 'woocommerce_quickpay_callback_args', $args, $post_id );

		return apply_filters( 'woocommerce_quickpay_callback_url', add_query_arg( $args, home_url( '/' ) ), $args, $post_id );
	}

	/**
	 * @deprecated
	 */
	public static function is_url( $url ) {
		_deprecated_function( __METHOD__, '8.0.0' );

		return ! filter_var( $url, FILTER_VALIDATE_URL ) === false;
	}

	/**
	 * @deprecated Use GatewayIcons::logo_url() instead.
	 */
	public static function get_payment_type_logo( $payment_type ) {
		_deprecated_function( __METHOD__, '8.0.0', GatewayIcons::class . '::logo_url()' );

		return GatewayIcons::logo_url( $payment_type );
	}

	/**
	 * @deprecated
	 */
	public static function has_preorder_plugin() {
		_deprecated_function( __METHOD__, '8.0.0' );
		// NOOP
	}

	/**
	 * @deprecated
	 */
	public static function value( $value, $default = null ) {
		_deprecated_function( __METHOD__, '8.0.0' );

		return empty( $value ) ? $default : $value;
	}

	/**
	 * @deprecated
	 */
	public static function qtranslate_prevent_redirect( $url_lang, $url_orig, $url_info ) {
		_deprecated_function( __METHOD__, '8.0.0' );
		// NOOP
	}

	/**
	 * @deprecated
	 */
	public static function spamshield_bypass_security_check( $bypass ) {
		_deprecated_function( __METHOD__, '8.0.0' );
		// NOOP
	}

	/**
	 * @deprecated Use ArrayUtils::insert_after() instead.
	 */
	public static function array_insert_after( $needle, $haystack, $new_key, $new_value ) {
		_deprecated_function( __METHOD__, '8.0.0', ArrayUtils::class . '::insert_after()' );

		return ArrayUtils::insert_after( $needle, $haystack, $new_key, $new_value );
	}

	/**
	 * @deprecated Use Dependencies::is_browser() instead.
	 */
	public static function is_browser( $browser ): bool {
		_deprecated_function( __METHOD__, '8.0.0', Dependencies::class . '::is_browser()' );

		return Dependencies::is_browser( $browser );
	}

	/**
	 * @deprecated Use WooCommerceContext::is_subscription_status() instead.
	 */
	public static function is_subscription_status( $status ) {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceContext::class . '::is_subscription_status()' );

		return WooCommerceContext::is_subscription_status( $status );
	}

	/**
	 * @deprecated Use WooCommerceContext::is_hpos_enabled() instead.
	 */
	public static function is_HPOS_enabled(): bool {
		_deprecated_function( __METHOD__, '8.0.0', WooCommerceContext::class . '::is_hpos_enabled()' );

		return WooCommerceContext::is_hpos_enabled();
	}

	/**
	 * @deprecated Use RandomUtils::create_random_string() instead.
	 */
	public static function create_random_string( $n ): string {
		_deprecated_function( __METHOD__, '8.0.0', RandomUtils::class . '::create_random_string()' );

		return RandomUtils::create_random_string( $n );
	}

	/**
	 * @deprecated Use GatewayUtils::is_plugin_gateway() instead.
	 */
	public static function is_plugin_gateway( WC_Payment_Gateway $class ) {
		_deprecated_function( __METHOD__, '8.0.0', GatewayUtils::class . '::is_plugin_gateway()' );

		return GatewayUtils::is_plugin_gateway( $class );
	}

	/**
	 * @deprecated Use GatewayUtils::get_plugin_gateways() instead.
	 */
	public static function get_plugin_gateways(): array {
		_deprecated_function( __METHOD__, '8.0.0', GatewayUtils::class . '::get_plugin_gateways()' );

		return GatewayUtils::get_plugin_gateways();
	}
}
