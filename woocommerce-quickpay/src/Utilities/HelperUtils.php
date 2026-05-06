<?php

namespace QuickpayPSP\Utilities;

use QuickpayPSP\Admin\Support\AssetEnqueuer;
use QuickpayPSP\Support\Dependencies;
use QuickpayPSP\WooCommerce\Support\GatewayUtils;
use QuickpayPSP\WooCommerce\Support\WooCommerceContext;
use WC_Payment_Gateway;

/**
 * Backwards-compatibility shim aggregating methods that have been moved to dedicated classes.
 *
 * Every method in this class is deprecated and delegates directly to its replacement.
 * New code should call the target class directly; this class exists only to avoid
 * breaking third-party code that still references HelperUtils.
 *
 * @deprecated All methods in this class are deprecated. Use the specific classes directly.
 */
final class HelperUtils {
	/** @deprecated Use MoneyUtils::price_normalize() */
	public static function price_normalize( $price, string $currency ) {
		return MoneyUtils::price_normalize( $price, $currency );
	}

	/** @deprecated Use MoneyUtils::price_multiplied_to_float() */
	public static function price_multiplied_to_float( $price, string $currency ) {
		return MoneyUtils::price_multiplied_to_float( $price, $currency );
	}

	/** @deprecated Use MoneyUtils::price_custom_to_multiplied() */
	public static function price_custom_to_multiplied( $price, string $currency ) {
		return MoneyUtils::price_custom_to_multiplied( $price, $currency );
	}

	/** @deprecated Use MoneyUtils::price_multiply() */
	public static function price_multiply( $price, ?string $currency = null ) {
		return MoneyUtils::price_multiply( $price, $currency );
	}

	/** @deprecated Use MoneyUtils::is_currency_using_decimals() */
	public static function is_currency_using_decimals( string $currency ): bool {
		return MoneyUtils::is_currency_using_decimals( $currency );
	}

	/** @deprecated Use AssetEnqueuer::enqueue_javascript_backend() */
	public static function enqueue_javascript_backend(): void {
		AssetEnqueuer::enqueue_javascript_backend();
	}

	/** @deprecated Use AssetEnqueuer::enqueue_stylesheet() */
	public static function enqueue_stylesheet(): void {
		AssetEnqueuer::enqueue_stylesheet();
	}

	/** @deprecated Use AssetEnqueuer::load_i18n() */
	public static function load_i18n(): void {
		AssetEnqueuer::load_i18n();
	}

	/** @deprecated Use AssetEnqueuer::static_version() */
	public static function static_version(): string {
		return AssetEnqueuer::static_version();
	}

	/** @deprecated */
	public static function option_is_enabled( $value ): int {
		return ( $value === 'yes' ) ? 1 : 0;
	}

	/** @deprecated Use UrlUtils::get_callback_url() */
	public static function get_callback_url( $post_id = null ): string {
		return UrlUtils::get_callback_url( $post_id );
	}

	/** @deprecated Use UrlUtils::is_url() */
	public static function is_url( $url ): bool {
		return UrlUtils::is_url( $url );
	}

	/** @deprecated Use GatewayIcons::logo_url() */
	public static function get_payment_type_logo( $payment_type ) {
		return GatewayUtils::get_payment_type_logo( $payment_type );
	}

	/** @deprecated Use Dependencies::is_pre_orders_active() */
	public static function has_preorder_plugin(): bool {
		return Dependencies::is_pre_orders_active();
	}

	/** @deprecated */
	public static function value( $value, $default = null ) {
		return empty( $value ) ? $default : $value;
	}

	/** @deprecated Use ArrayUtils::insert_after() */
	public static function array_insert_after( string $needle, array $haystack, string $new_key, $new_value ): array {
		return ArrayUtils::insert_after( $needle, $haystack, $new_key, $new_value );
	}

	/** @deprecated Use Dependencies::is_browser() */
	public static function is_browser( string $browser ): bool {
		return Dependencies::is_browser( $browser );
	}

	/** @deprecated Use WooCommerceContext::is_subscription_status() */
	public static function is_subscription_status( $status ): bool {
		return WooCommerceContext::is_subscription_status( $status );
	}

	/** @deprecated Use WooCommerceContext::is_hpos_enabled() */
	public static function is_HPOS_enabled(): bool {
		return WooCommerceContext::is_hpos_enabled();
	}

	/** @deprecated Use RandomUtils::create_random_string() */
	public static function create_random_string( int $length ): string {
		return RandomUtils::create_random_string( $length );
	}

	/** @deprecated Use GatewayUtils::is_plugin_gateway() */
	public static function is_plugin_gateway( WC_Payment_Gateway $class ): bool {
		return GatewayUtils::is_plugin_gateway( $class );
	}

	/** @deprecated Use GatewayUtils::get_plugin_gateways() */
	public static function get_plugin_gateways(): array {
		return GatewayUtils::get_plugin_gateways();
	}
}
